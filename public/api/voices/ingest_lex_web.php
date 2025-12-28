<?php
/**
 * Script de ingesta RAG - Procesa UN archivo por llamada
 * Recarga la página para procesar el siguiente archivo
 * ¡ELIMINAR DESPUÉS DE USAR!
 */

set_time_limit(120); // 2 minutos max por archivo
ini_set('memory_limit', '256M');

require_once __DIR__ . '/../../../src/App/bootstrap.php';
require_once __DIR__ . '/../../../src/Rag/QdrantClient.php';
require_once __DIR__ . '/../../../src/Rag/EmbeddingService.php';

use App\Env;
use Rag\QdrantClient;
use Rag\EmbeddingService;

// Configuración
define('COLLECTION_NAME', 'lex_convenios');
define('VECTOR_SIZE', 4096);
define('CHUNK_SIZE', 500);
define('CHUNK_OVERLAP', 50);
define('BATCH_SIZE', 3); // Pequeño para evitar timeouts

$conveniosPath = __DIR__ . '/../../../docs/context/voices/lex/convenios';
$progressFile = sys_get_temp_dir() . '/lex_ingest_progress.json';

// Leer progreso
$progress = file_exists($progressFile) ? json_decode(file_get_contents($progressFile), true) : [];
$processedFiles = $progress['processed'] ?? [];
$pointId = $progress['pointId'] ?? 1;
$totalChunks = $progress['totalChunks'] ?? 0;

// Reset si se pide
if (isset($_GET['reset'])) {
    @unlink($progressFile);
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

// HTML
echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Ingesta RAG</title>";
echo "<meta http-equiv='refresh' content='3'>"; // Auto-refresh cada 3s
echo "<style>body{font-family:system-ui;max-width:800px;margin:40px auto;padding:20px;background:#0d1117;color:#c9d1d9}";
echo "pre{background:#161b22;padding:20px;border-radius:8px;overflow-x:auto}";
echo ".ok{color:#3fb950}.err{color:#f85149}.warn{color:#d29922}a{color:#58a6ff}</style></head><body>";

echo "<h1>🔄 Ingesta RAG para Lex</h1>";

try {
    // Verificar API key
    $openrouterKey = Env::get('OPENROUTER_API_KEY');
    if (!$openrouterKey) {
        throw new Exception("OPENROUTER_API_KEY no configurada");
    }

    // Conectar Qdrant
    $qdrantHost = Env::get('QDRANT_HOST', 'localhost');
    $qdrantPort = (int) Env::get('QDRANT_PORT', 6333);
    $qdrant = new QdrantClient($qdrantHost, $qdrantPort);
    $embeddings = new EmbeddingService($openrouterKey);

    if (!$qdrant->health()) {
        throw new Exception("No se puede conectar con Qdrant en {$qdrantHost}:{$qdrantPort}");
    }

    // Crear colección si no existe
    if (!$qdrant->collectionExists(COLLECTION_NAME)) {
        $qdrant->createCollection(COLLECTION_NAME, VECTOR_SIZE, 'Cosine');
        echo "<p class='ok'>✓ Colección creada</p>";
    }

    // Buscar archivos pendientes
    $txtFiles = glob($conveniosPath . '/*.txt');
    $files = array_filter($txtFiles, fn($f) => basename($f) !== 'README.md');
    $files = array_values($files);
    
    $pending = array_filter($files, fn($f) => !in_array(basename($f), $processedFiles));
    $pending = array_values($pending);

    $total = count($files);
    $done = count($processedFiles);

    echo "<h2>Progreso: {$done}/{$total} archivos</h2>";
    echo "<progress value='{$done}' max='{$total}' style='width:100%;height:30px'></progress>";

    if (empty($pending)) {
        // Terminado
        echo "<pre>";
        echo "<span class='ok'>✅ INGESTA COMPLETADA</span>\n\n";
        echo "Archivos procesados: {$done}\n";
        echo "Total chunks indexados: {$totalChunks}\n";
        $count = $qdrant->countPoints(COLLECTION_NAME);
        echo "Puntos en colección: {$count}\n";
        echo "</pre>";
        echo "<p><a href='?reset=1'>🔄 Reiniciar ingesta</a></p>";
        echo "<p><strong>Ahora borra este archivo: public/api/voices/ingest_lex_web.php</strong></p>";
        // Quitar auto-refresh
        echo "<script>document.querySelector('meta[http-equiv]').remove();</script>";
        exit;
    }

    // Procesar siguiente archivo
    $file = $pending[0];
    $filename = basename($file);
    
    echo "<pre>";
    echo "→ Procesando: <strong>{$filename}</strong>\n";

    $text = file_get_contents($file);
    $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', ' ', $text);
    $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
    
    echo "  Tamaño: " . number_format(strlen($text)) . " chars\n";

    if (strlen(trim($text)) < 50) {
        echo "<span class='warn'>  ⚠ Archivo vacío, saltando...</span>\n";
        $processedFiles[] = $filename;
    } else {
        // Chunking
        $chunks = chunkText($text, CHUNK_SIZE, CHUNK_OVERLAP);
        echo "  Chunks: " . count($chunks) . "\n";

        // Procesar en batches
        $batches = array_chunk($chunks, BATCH_SIZE);
        $fileChunks = 0;
        
        foreach ($batches as $batchIndex => $batch) {
            try {
                $batchTexts = array_column($batch, 'text');
                $vectors = $embeddings->embedBatch($batchTexts);
                
                $points = [];
                foreach ($batch as $i => $chunk) {
                    $points[] = [
                        'id' => $pointId++,
                        'vector' => $vectors[$i],
                        'payload' => [
                            'text' => $chunk['text'],
                            'document_id' => pathinfo($filename, PATHINFO_FILENAME),
                            'document_name' => $filename,
                            'chunk_index' => $chunk['index'],
                            'section' => $chunk['section'] ?? ''
                        ]
                    ];
                }
                
                $qdrant->upsertPoints(COLLECTION_NAME, $points);
                $fileChunks += count($batch);
                $totalChunks += count($batch);
                
            } catch (Exception $e) {
                echo "<span class='err'>  ✗ Batch error: " . htmlspecialchars($e->getMessage()) . "</span>\n";
            }
            
            usleep(300000); // 300ms entre batches
        }
        
        echo "<span class='ok'>  ✓ {$fileChunks} chunks indexados</span>\n";
        $processedFiles[] = $filename;
    }

    // Guardar progreso
    file_put_contents($progressFile, json_encode([
        'processed' => $processedFiles,
        'pointId' => $pointId,
        'totalChunks' => $totalChunks
    ]));

    echo "\n<span class='warn'>Recargando automáticamente en 3 segundos...</span>";
    echo "</pre>";

} catch (Exception $e) {
    echo "<pre class='err'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<script>document.querySelector('meta[http-equiv]').remove();</script>";
}

echo "</body></html>";

// === Funciones ===

function chunkText(string $text, int $targetTokens, int $overlap): array
{
    $charsPerToken = 4;
    $targetChars = $targetTokens * $charsPerToken;
    $overlapChars = $overlap * $charsPerToken;
    
    $text = preg_replace('/\s+/', ' ', trim($text));
    
    $chunks = [];
    $start = 0;
    $index = 0;
    $length = strlen($text);
    
    while ($start < $length) {
        $end = min($start + $targetChars, $length);
        
        if ($end < $length) {
            $sub = substr($text, $start, $end - $start);
            $lastPeriod = strrpos($sub, '. ');
            if ($lastPeriod !== false && $lastPeriod > $targetChars * 0.5) {
                $end = $start + $lastPeriod + 1;
            }
        }
        
        $chunkText = trim(substr($text, $start, $end - $start));
        
        if (strlen($chunkText) > 20) {
            $section = '';
            if (preg_match('/^(Artículo\s+\d+|CAPÍTULO\s+[IVXLC]+|Sección\s+\d+)[.:]/i', $chunkText, $m)) {
                $section = $m[1];
            }
            
            $chunks[] = [
                'text' => $chunkText,
                'index' => $index++,
                'section' => $section
            ];
        }
        
        $start = $end - $overlapChars;
        if ($start >= $length - $overlapChars) break;
    }
    
    return $chunks;
}
