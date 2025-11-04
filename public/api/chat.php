<?php
require_once __DIR__ . '/../../src/App/bootstrap.php';
require_once __DIR__ . '/../../src/Chat/GeminiClient.php';
require_once __DIR__ . '/../../src/Chat/ChatService.php';
require_once __DIR__ . '/../../src/Auth/AuthService.php';
require_once __DIR__ . '/../../src/Repos/ConversationsRepo.php';
require_once __DIR__ . '/../../src/Repos/MessagesRepo.php';

use App\Response;
use App\Session;
use Auth\AuthService;
use Chat\ChatService;
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
if ($message === '') {
    Response::error('validation_error', 'El campo message es obligatorio', 400);
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

$svc = new ChatService();
// Construir historial: incluir todos los mensajes de la conversación (ya incluye el del usuario)
$history = [];
foreach ($msgs->listByConversation($conversationId) as $m) {
    $history[] = [ 'role' => $m['role'], 'content' => $m['content'] ];
}
$assistantMsg = $svc->replyWithHistory($history);

// Guardar respuesta de asistente
$assistantMsgId = $msgs->create($conversationId, null, 'assistant', $assistantMsg['content'], getenv('GEMINI_MODEL') ?: null, null, null);

// Actualizar updated_at de la conversación
$convos->touch($conversationId);

Response::json([
    'conversation' => [ 'id' => $conversationId ],
    'message' => [
        'id' => $assistantMsgId,
        'role' => $assistantMsg['role'],
        'content' => $assistantMsg['content'],
        'model' => getenv('GEMINI_MODEL') ?: null
    ]
]);
