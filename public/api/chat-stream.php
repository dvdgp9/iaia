<?php
/**
 * API: Chat con streaming (Server-Sent Events)
 * POST /api/chat-stream.php
 * 
 * Envía respuestas chunk a chunk mientras se generan.
 * El frontend debe usar fetch() con ReadableStream o EventSource.
 */
require_once __DIR__ . '/../../src/App/bootstrap.php';
require_once __DIR__ . '/../../src/Chat/ContextBuilder.php';
require_once __DIR__ . '/../../src/Chat/OpenRouterClient.php';
require_once __DIR__ . '/../../src/Auth/AuthService.php';
require_once __DIR__ . '/../../src/Repos/ConversationsRepo.php';
require_once __DIR__ . '/../../src/Repos/MessagesRepo.php';
require_once __DIR__ . '/../../src/Repos/ChatFilesRepo.php';
require_once __DIR__ . '/../../src/Repos/UsageLogRepo.php';

use App\Response;
use App\Session;
use App\Env;
use Auth\AuthService;
use Chat\OpenRouterClient;
use Chat\ContextBuilder;
use Repos\ConversationsRepo;
use Repos\MessagesRepo;
use Repos\ChatFilesRepo;
use Repos\UsageLogRepo;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('method_not_allowed', 'Sólo POST', 405);
}

// Requiere sesión y CSRF
$user = AuthService::requireAuth();
Session::requireCsrf();

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$message = trim((string)($input['message'] ?? ''));
$conversationId = isset($input['conversation_id']) ? (int)$input['conversation_id'] : 0;
$file = $input['file'] ?? null;
$fileId = isset($input['file_id']) ? (int)$input['file_id'] : null;

// Modelo (no soportamos streaming para generación de imágenes)
$modelName = isset($input['model']) && $input['model'] !== ''
    ? (string)$input['model']
    : 'qwen/qwen-plus';

// Validar
if ($message === '' && !$file && !$fileId) {
    Response::error('validation_error', 'Se requiere un mensaje o archivo', 400);
}

$filesRepo = new ChatFilesRepo();

// Cargar archivo desde DB si se proporcionó file_id
if ($fileId && !$file) {
    $storedFile = $filesRepo->findByIdAndUser($fileId, (int)$user['id']);
    if ($storedFile) {
        $storagePath = ChatFilesRepo::getStoragePath();
        $filePath = $storagePath . '/' . $storedFile['stored_name'];
        if (file_exists($filePath)) {
            $fileData = base64_encode(file_get_contents($filePath));
            $file = [
                'mime_type' => $storedFile['mime_type'],
                'data' => $fileData,
                'name' => $storedFile['original_name']
            ];
        }
    }
}

// Validar archivo
if ($file) {
    if (!isset($file['mime_type']) || !isset($file['data'])) {
        Response::error('validation_error', 'Datos de archivo inválidos', 400);
    }
    $allowedTypes = ['application/pdf', 'image/png', 'image/jpeg', 'image/gif', 'image/webp'];
    if (!in_array($file['mime_type'], $allowedTypes)) {
        Response::error('validation_error', 'Tipo de archivo no soportado', 400);
    }
    // Si es imagen, usar modelo multimodal
    if ((!isset($input['model']) || $input['model'] === '') && str_starts_with((string)$file['mime_type'], 'image/')) {
        $modelName = 'google/gemini-3-flash-preview';
    }
}

$convos = new ConversationsRepo();
$msgs = new MessagesRepo();
$usageLog = new UsageLogRepo();

// Crear conversación si es nueva
$isNewConversation = $conversationId <= 0;
if ($isNewConversation) {
    $conversationId = $convos->create((int)$user['id'], null);
    $usageLog->log((int)$user['id'], 'conversation');
}

// Guardar mensaje de usuario
$userMsgId = $msgs->create($conversationId, (int)$user['id'], 'user', $message, null, null, null, $fileId);
$usageLog->log((int)$user['id'], 'message', 1, ['model' => $modelName]);

if ($fileId) {
    $filesRepo->updateConversationId($fileId, $conversationId);
    $filesRepo->updateMessageId($fileId, $userMsgId);
}

// Auto-titular
$convos->autoTitle($conversationId, $message);

// Construir historial
$allMessages = $msgs->listByConversation($conversationId);
$history = [];
foreach ($allMessages as $m) {
    $history[] = ['role' => $m['role'], 'content' => $m['content']];
}

// Agregar archivo al último mensaje de usuario
if ($file && count($history) > 0) {
    $lastIdx = count($history) - 1;
    if ($history[$lastIdx]['role'] === 'user') {
        $history[$lastIdx]['file'] = $file;
    }
}

// Limitar contexto
$contextTruncated = false;
if (count($history) > 20) {
    $totalChars = array_sum(array_map(fn($m) => mb_strlen($m['content']), $history));
    $maxContextChars = 50000;
    if ($totalChars > $maxContextChars) {
        $contextTruncated = true;
        $truncated = [];
        $chars = 0;
        for ($i = count($history) - 1; $i >= 0; $i--) {
            $len = mb_strlen($history[$i]['content']);
            if ($chars + $len > $maxContextChars && count($truncated) >= 20) break;
            array_unshift($truncated, $history[$i]);
            $chars += $len;
        }
        $history = $truncated;
    }
}

// Configurar headers SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');

// Desactivar buffering
if (function_exists('apache_setenv')) {
    @apache_setenv('no-gzip', '1');
}
@ini_set('zlib.output_compression', '0');
@ini_set('implicit_flush', '1');
while (ob_get_level()) ob_end_flush();
ob_implicit_flush(true);

// Función helper para enviar evento SSE
function sendSSE(string $event, array $data): void {
    echo "event: {$event}\n";
    echo "data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
    if (connection_aborted()) exit;
    @ob_flush();
    @flush();
}

// Enviar evento inicial con metadata
sendSSE('start', [
    'conversation_id' => $conversationId,
    'is_new' => $isNewConversation,
    'context_truncated' => $contextTruncated
]);

// Crear cliente OpenRouter con contexto
$contextDir = dirname(__DIR__, 2) . '/context';
$contextBuilder = new ContextBuilder($contextDir);
$systemPrompt = $contextBuilder->buildSystemPrompt();

$client = new OpenRouterClient(
    Env::get('OPENROUTER_API_KEY'),
    $modelName,
    $systemPrompt
);

$usedModel = $modelName;
$fullContent = '';

try {
    $fullContent = $client->generateWithMessagesStreaming(
        $history,
        function($chunk) {
            sendSSE('chunk', ['content' => $chunk]);
        },
        function($text, $model) use (&$usedModel) {
            $usedModel = $model;
        }
    );
} catch (\Exception $e) {
    sendSSE('error', ['message' => $e->getMessage()]);
    exit;
}

// Guardar mensaje del asistente
$assistantMsgId = $msgs->create($conversationId, null, 'assistant', $fullContent, $usedModel);

// Actualizar conversación
$convos->touch($conversationId);

// Enviar evento de finalización (sin content para evitar duplicación)
sendSSE('done', [
    'message_id' => $assistantMsgId,
    'model' => $usedModel
]);
