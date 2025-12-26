<?php
/**
 * API: Procesar jobs pendientes (llamado por cron o trigger)
 * POST /api/jobs/process.php
 * 
 * Este endpoint procesa UN job pendiente cada vez que se llama.
 * Diseñado para ser llamado por cron cada minuto o por trigger del frontend.
 * 
 * Response: { success: true, processed: bool, job_id: int|null }
 */

require_once __DIR__ . '/../../../src/App/bootstrap.php';

use App\Env;
use Jobs\BackgroundJobsRepo;
use Audio\ContentExtractor;
use Audio\PodcastScriptGenerator;
use Audio\GeminiTtsClient;
use Gestures\GestureExecutionsRepo;
use Repos\UsageLogRepo;

// Permitir llamadas desde cron (sin sesión) o desde frontend (con sesión)
// Para cron, verificar token secreto; para frontend, verificar sesión
$isCliOrCron = php_sapi_name() === 'cli' || !isset($_SERVER['HTTP_HOST']);
$cronToken = $_GET['token'] ?? '';
$expectedToken = Env::get('CRON_SECRET_TOKEN', '');

if (!$isCliOrCron) {
    // Llamada HTTP - verificar token o sesión
    \App\Session::start();
    $user = \App\Session::user();
    
    $hasValidToken = !empty($expectedToken) && hash_equals($expectedToken, $cronToken);
    $hasValidSession = !empty($user);
    
    if (!$hasValidToken && !$hasValidSession) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'No autorizado']);
        exit;
    }
    
    // IMPORTANTE: Liberar sesión para no bloquear otras peticiones del usuario
    // El procesamiento de podcast puede tardar minutos
    session_write_close();
}

// Configurar tiempo máximo de ejecución (5 minutos para podcasts largos)
set_time_limit(300);
// Aumentar límite de memoria para procesamiento de audio (WAV/Base64)
ini_set('memory_limit', '512M');

// Enviar respuesta inmediata al frontend para no bloquear
if (!$isCliOrCron && isset($_SERVER['HTTP_HOST'])) {
    // Desconectar del cliente pero seguir procesando
    ignore_user_abort(true);
    
    header('Content-Type: application/json');
    header('Connection: close');
    
    $response = json_encode(['success' => true, 'processing' => true, 'message' => 'Procesando en background']);
    header('Content-Length: ' . strlen($response));
    
    echo $response;
    
    // Flush y cerrar conexión
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    } else {
        ob_end_flush();
        flush();
    }
}

$repo = new BackgroundJobsRepo();

// Primero, resetear jobs "colgados" (más de 5 minutos en processing)
$stuckReset = $repo->resetStuckJobs(5);

// Obtener siguiente job pendiente
$job = $repo->getNextPending();

if (!$job) {
    // No hay jobs pendientes
    if ($isCliOrCron) {
        echo "No hay jobs pendientes\n";
        exit(0);
    }
    echo json_encode(['success' => true, 'processed' => false, 'message' => 'No hay jobs pendientes']);
    exit;
}

$jobId = (int)$job['id'];
$jobType = $job['job_type'];
$inputData = $job['input_data'];
$userId = (int)$job['user_id'];

// Log inicio
if ($isCliOrCron) {
    echo "Procesando job #{$jobId} (tipo: {$jobType})\n";
}

// Log file para debug
$logFile = dirname(__DIR__, 2) . '/logs/job_process.log';
@mkdir(dirname($logFile), 0775, true);

function logJob(string $message, string $logFile) {
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

try {
    logJob("Iniciando job #{$jobId} tipo: {$jobType}", $logFile);
    
    // Marcar como processing
    $repo->markProcessing($jobId, 'Iniciando procesamiento...');
    logJob("Job #{$jobId} marcado como processing", $logFile);
    
    $outputData = [];
    
    switch ($jobType) {
        case 'podcast':
            logJob("Job #{$jobId} llamando processPodcastJob", $logFile);
            $outputData = processPodcastJob($jobId, $inputData, $userId, $repo);
            logJob("Job #{$jobId} processPodcastJob completado", $logFile);
            break;
            
        default:
            throw new \Exception("Tipo de job no soportado: {$jobType}");
    }
    
    logJob("Job #{$jobId} marcando como completed con output: " . json_encode(array_keys($outputData)), $logFile);
    
    // Marcar como completed
    $repo->markCompleted($jobId, $outputData);
    
    // Registrar en estadísticas (usage_log) - Hacerlo DESPUÉS de marcar completed
    try {
        logJob("Job #{$jobId} - Registrando en usage_log", $logFile);
        $usageLog = new Repos\UsageLogRepo();
        $usageLog->log($userId, 'gesture', 1, ['gesture_type' => 'podcast-from-article']);
        logJob("Job #{$jobId} - Usage log registrado", $logFile);
    } catch (\Throwable $e) {
        logJob("Job #{$jobId} - ERROR en usage_log (ignorado): " . $e->getMessage(), $logFile);
    }
    
    logJob("Job #{$jobId} COMPLETADO exitosamente", $logFile);
    
    if ($isCliOrCron) {
        echo "Job #{$jobId} completado exitosamente\n";
    } else {
        echo json_encode([
            'success' => true, 
            'processed' => true, 
            'job_id' => $jobId,
            'status' => 'completed'
        ]);
    }
    
} catch (\Exception $e) {
    logJob("Job #{$jobId} ERROR: " . $e->getMessage(), $logFile);
    logJob("Job #{$jobId} Stack trace: " . $e->getTraceAsString(), $logFile);
    
    // Marcar como failed
    $repo->markFailed($jobId, $e->getMessage());
    
    if ($isCliOrCron) {
        echo "Job #{$jobId} falló: " . $e->getMessage() . "\n";
        exit(1);
    } else {
        echo json_encode([
            'success' => true,
            'processed' => true,
            'job_id' => $jobId,
            'status' => 'failed',
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Procesar job de tipo podcast
 */
function processPodcastJob(int $jobId, array $inputData, int $userId, BackgroundJobsRepo $repo): array
{
    $logFile = dirname(__DIR__, 2) . '/logs/job_process.log';
    
    $sourceType = $inputData['source_type'] ?? 'url';
    $sourceUrl = $inputData['url'] ?? '';
    $sourceText = $inputData['text'] ?? '';
    $sourcePdf = $inputData['pdf_base64'] ?? '';
    
    logJob("Job #{$jobId} - Iniciando extracción. Source type: {$sourceType}", $logFile);
    
    // === PASO 1: Extraer contenido ===
    $repo->updateProgress($jobId, 'Extrayendo contenido del artículo...');
    
    $extractor = new ContentExtractor();
    $content = null;
    $title = '';
    $source = '';
    
    switch ($sourceType) {
        case 'url':
            if (empty($sourceUrl)) {
                throw new \Exception('URL no proporcionada');
            }
            logJob("Job #{$jobId} - Extrayendo de URL: {$sourceUrl}", $logFile);
            $result = $extractor->extractFromUrl($sourceUrl);
            if (!$result['success']) {
                throw new \Exception('Error extrayendo URL: ' . $result['error']);
            }
            $content = $result['content'];
            $title = $result['title'];
            $source = $result['source'];
            logJob("Job #{$jobId} - Extraído. Título: {$title}, palabras: " . str_word_count($content), $logFile);
            break;
            
        case 'pdf':
            if (empty($sourcePdf)) {
                throw new \Exception('PDF no proporcionado');
            }
            logJob("Job #{$jobId} - Extrayendo de PDF", $logFile);
            $result = $extractor->extractFromPdf($sourcePdf);
            if (!$result['success']) {
                throw new \Exception('Error extrayendo PDF: ' . $result['error']);
            }
            $content = $result['content'];
            $title = $result['title'];
            $source = 'PDF';
            logJob("Job #{$jobId} - Extraído. Título: {$title}, palabras: " . str_word_count($content), $logFile);
            break;
            
        case 'text':
            if (empty($sourceText)) {
                throw new \Exception('Texto no proporcionado');
            }
            logJob("Job #{$jobId} - Procesando texto", $logFile);
            $result = $extractor->extractFromText($sourceText);
            if (!$result['success']) {
                throw new \Exception('Error procesando texto: ' . $result['error']);
            }
            $content = $result['content'];
            $title = $result['title'];
            $source = 'Texto';
            logJob("Job #{$jobId} - Extraído. Título: {$title}, palabras: " . str_word_count($content), $logFile);
            break;
            
        default:
            throw new \Exception("Tipo de fuente no soportado: {$sourceType}");
    }
    
    // === PASO 2: Generar guion ===
    logJob("Job #{$jobId} - Iniciando generación de guion", $logFile);
    $repo->updateProgress($jobId, 'Generando guion del podcast...');
    
    $scriptGenerator = new PodcastScriptGenerator();
    $scriptResult = $scriptGenerator->generate($content, $title, 15);
    logJob("Job #{$jobId} - Guion generado exitosamente", $logFile);
    
    if (!$scriptResult['success']) {
        throw new \Exception('Error generando guion: ' . $scriptResult['error']);
    }
    
    $script = $scriptResult['script'];
    $summary = $scriptResult['summary'];
    $speaker1 = $scriptResult['speaker1'];
    $speaker2 = $scriptResult['speaker2'];
    $estimatedDuration = $scriptResult['estimated_duration'];
    
    // === PASO 3: Generar audio ===
    logJob("Job #{$jobId} - Iniciando generación de audio", $logFile);
    $repo->updateProgress($jobId, 'Sintetizando audio con IA (esto puede tardar hasta 5 minutos)...');
    
    $geminiKey = Env::get('GEMINI_API_KEY');
    if (empty($geminiKey)) {
        throw new \Exception('Falta GEMINI_API_KEY para generar audio');
    }
    
    $ttsClient = new GeminiTtsClient();
    $ttsPrompt = "TTS the following podcast conversation between {$speaker1} and {$speaker2} in Spanish:\n\n" . $script;
    
    $audioResult = $ttsClient->generateMultiSpeaker(
        $ttsPrompt,
        $speaker1,
        $speaker2,
        'Aoede',
        'Orus'
    );
    logJob("Job #{$jobId} - Audio generado exitosamente", $logFile);
    
    if (!$audioResult['success']) {
        throw new \Exception('Error generando audio: ' . $audioResult['error']);
    }
    
    // Convertir PCM a WAV y guardar
    logJob("Job #{$jobId} - Convirtiendo PCM a WAV", $logFile);
    $pcmData = base64_decode($audioResult['audio_data']);
    // Liberar memoria del array original grande
    unset($audioResult);
    
    $wavData = GeminiTtsClient::pcmToWav($pcmData);
    // Liberar PCM data
    unset($pcmData);
    
    $publicTmp = dirname(__DIR__, 2) . '/tmp';
    if (!is_dir($publicTmp)) {
        @mkdir($publicTmp, 0775, true);
    }
    $fileName = 'podcast_' . uniqid() . '.wav';
    $filePath = $publicTmp . '/' . $fileName;
    file_put_contents($filePath, $wavData);
    $wavUrl = '/tmp/' . $fileName;
    
    // Liberar WAV data (grande)
    unset($wavData);
    
    logJob("Job #{$jobId} - Audio guardado en {$wavUrl}", $logFile);
    
    // === PASO 4: Guardar en historial de gestos ===
    logJob("Job #{$jobId} - Guardando en historial de gestos", $logFile);
    $repo->updateProgress($jobId, 'Guardando resultado...');
    
    $gesturesRepo = new GestureExecutionsRepo();
    $executionId = $gesturesRepo->create([
        'user_id' => $userId,
        'gesture_type' => 'podcast-from-article',
        'title' => $title ?: 'Podcast: ' . substr($summary, 0, 50),
        'input_data' => [
            'source_type' => $sourceType,
            'source' => $source,
            'url' => $sourceUrl,
            'word_count' => str_word_count($content)
        ],
        'output_content' => $script,
        'output_data' => [
            'summary' => $summary,
            'script' => $script,
            'audio_url' => $wavUrl,
            'duration_estimate' => $estimatedDuration,
            'speaker1' => $speaker1,
            'speaker2' => $speaker2
        ],
        'content_type' => 'original',
        'business_line' => null,
        'model' => 'gemini-2.5-flash-preview-tts'
    ]);
    logJob("Job #{$jobId} - Guardado en gestos con ID: {$executionId}", $logFile);
    
    // Devolver datos para output_data del job
    return [
        'execution_id' => $executionId,
        'title' => $title,
        'summary' => $summary,
        'script' => $script,
        'speaker1' => $speaker1,
        'speaker2' => $speaker2,
        'audio_url' => $wavUrl,
        'duration_estimate' => $estimatedDuration,
        'source' => $source
    ];
}
