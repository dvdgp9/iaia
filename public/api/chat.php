<?php
require_once __DIR__ . '/../../src/App/bootstrap.php';
require_once __DIR__ . '/../../src/Chat/ContextBuilder.php';
require_once __DIR__ . '/../../src/Chat/LlmProvider.php';
require_once __DIR__ . '/../../src/Chat/GeminiClient.php';
require_once __DIR__ . '/../../src/Chat/GeminiProvider.php';
require_once __DIR__ . '/../../src/Chat/QwenClient.php';
require_once __DIR__ . '/../../src/Chat/QwenProvider.php';
require_once __DIR__ . '/../../src/Chat/LlmProviderFactory.php';
require_once __DIR__ . '/../../src/Chat/ChatService.php';
require_once __DIR__ . '/../../src/Auth/AuthService.php';
require_once __DIR__ . '/../../src/Repos/ConversationsRepo.php';
require_once __DIR__ . '/../../src/Repos/MessagesRepo.php';

use App\Response;
use App\Session;
use Auth\AuthService;
use Chat\ChatService;
use Chat\LlmProviderFactory;
use Repos\ConversationsRepo;
use Repos\MessagesRepo;

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

// Opcional: permitir elegir proveedor/modelo desde el cliente (validado por la factoría)
$providerName = isset($input['provider']) ? (string)$input['provider'] : null;
$modelName = isset($input['model']) ? (string)$input['model'] : null;

// Validar que haya mensaje o archivo
if ($message === '' && !$file) {
    Response::error('validation_error', 'Se requiere un mensaje o archivo', 400);
}

// Validar archivo si existe
if ($file) {
    if (!isset($file['mime_type']) || !isset($file['data'])) {
        Response::error('validation_error', 'Datos de archivo inválidos', 400);
    }
    
    // Validar tipo MIME
    $allowedTypes = ['application/pdf', 'image/png', 'image/jpeg', 'image/gif', 'image/webp'];
    if (!in_array($file['mime_type'], $allowedTypes)) {
        Response::error('validation_error', 'Tipo de archivo no soportado', 400);
    }
    
    // QWEN no soporta archivos/imágenes → forzar Gemini para multimodal
    $providerName = 'gemini';
}

$convos = new ConversationsRepo();
$msgs = new MessagesRepo();

if ($conversationId <= 0) {
    $conversationId = $convos->create((int)$user['id'], null);
}

// Guardar mensaje de usuario
$userMsgId = $msgs->create($conversationId, (int)$user['id'], 'user', $message, null, null, null);

// Auto-titular si el título sigue siendo el genérico
$convos->autoTitle($conversationId, $message);

$provider = LlmProviderFactory::create($providerName, $modelName);
$svc = new ChatService($provider);
// Construir historial: incluir todos los mensajes de la conversación (ya incluye el del usuario)
$allMessages = $msgs->listByConversation($conversationId);
$history = [];
foreach ($allMessages as $m) {
    $historyItem = [ 'role' => $m['role'], 'content' => $m['content'] ];
    $history[] = $historyItem;
}

// Si hay archivo, agregarlo al último mensaje de usuario
if ($file && count($history) > 0) {
    $lastIdx = count($history) - 1;
    if ($history[$lastIdx]['role'] === 'user') {
        $history[$lastIdx]['file'] = $file;
    }
}

// Limitar contexto para no exceder límites de Gemini (250k tokens → ~1M chars)
// Dejamos margen: ~150k chars de historial (~37.5k tokens estimados)
$contextTruncated = false;
if (count($history) > 20) {
    $totalChars = array_sum(array_map(fn($m) => mb_strlen($m['content']), $history));
    $maxContextChars = 150000;
    
    if ($totalChars > $maxContextChars) {
        $contextTruncated = true;
        // Mantener mensajes recientes hasta alcanzar límite
        $truncated = [];
        $chars = 0;
        for ($i = count($history) - 1; $i >= 0; $i--) {
            $len = mb_strlen($history[$i]['content']);
            if ($chars + $len > $maxContextChars && count($truncated) >= 20) {
                break;
            }
            array_unshift($truncated, $history[$i]);
            $chars += $len;
        }
        $history = $truncated;
    }
}

$assistantMsg = $svc->replyWithHistory($history);

// Determinar el modelo usado
// Si se forzó Gemini por archivo, usar ese modelo
$effectiveProvider = $providerName ?? getenv('LLM_PROVIDER') ?: 'qwen';
$usedModel = $modelName ?? ($effectiveProvider === 'gemini' ? getenv('GEMINI_MODEL') : getenv('QWEN_MODEL'));

// Guardar respuesta de asistente
$assistantMsgId = $msgs->create($conversationId, null, 'assistant', $assistantMsg['content'], $usedModel ?: null, null, null);

// Actualizar updated_at de la conversación
$convos->touch($conversationId);

Response::json([
    'conversation' => [ 'id' => $conversationId ],
    'message' => [
        'id' => $assistantMsgId,
        'role' => $assistantMsg['role'],
        'content' => $assistantMsg['content'],
        'model' => $usedModel ?: null
    ],
    'context_truncated' => $contextTruncated
]);
