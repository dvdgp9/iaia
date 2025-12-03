<?php
/**
 * API: Chat con una voz especializada
 * POST /api/voices/chat.php
 * Body JSON: { voice_id, message, history?, execution_id? }
 */

require_once __DIR__ . '/../../../src/App/bootstrap.php';
require_once __DIR__ . '/../../../src/Chat/ContextBuilder.php';
require_once __DIR__ . '/../../../src/Chat/LlmProvider.php';
require_once __DIR__ . '/../../../src/Chat/GeminiClient.php';
require_once __DIR__ . '/../../../src/Chat/GeminiProvider.php';
require_once __DIR__ . '/../../../src/Chat/QwenClient.php';
require_once __DIR__ . '/../../../src/Chat/QwenProvider.php';
require_once __DIR__ . '/../../../src/Chat/LlmProviderFactory.php';
require_once __DIR__ . '/../../../src/Voices/VoiceExecutionsRepo.php';
require_once __DIR__ . '/../../../src/Voices/VoiceContextBuilder.php';

use App\Session;
use App\Response;
use App\Env;
use Chat\GeminiClient;
use Voices\VoiceExecutionsRepo;
use Voices\VoiceContextBuilder;

$user = Session::user();
if (!$user) {
    Response::error('unauthorized', 'Sesión no válida', 401);
}

// Validar CSRF
$csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
$csrfSession = $_SESSION['csrf_token'] ?? '';
if (!$csrfHeader || $csrfHeader !== $csrfSession) {
    Response::error('csrf_invalid', 'Token CSRF inválido', 403);
}

// Solo POST
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    Response::error('method_not_allowed', 'Solo POST', 405);
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$voiceId = $body['voice_id'] ?? '';
$message = trim($body['message'] ?? '');
$history = $body['history'] ?? [];
$executionId = $body['execution_id'] ?? null;

if (!$voiceId) {
    Response::error('missing_voice', 'Se requiere voice_id', 400);
}
if (!$message) {
    Response::error('missing_message', 'Se requiere message', 400);
}

// Obtener contexto especializado de la voz
$voiceContext = new VoiceContextBuilder($voiceId);
$systemPrompt = $voiceContext->buildSystemPrompt();

if (!$systemPrompt) {
    Response::error('invalid_voice', 'Voz no encontrada', 404);
}

// Construir mensajes para el LLM
$messages = [];

// Añadir historial si existe
foreach ($history as $msg) {
    if (isset($msg['role']) && isset($msg['content'])) {
        $messages[] = [
            'role' => $msg['role'],
            'content' => $msg['content']
        ];
    }
}

// Añadir mensaje actual
$messages[] = [
    'role' => 'user',
    'content' => $message
];

// Crear cliente Gemini directamente con el system prompt de la voz
try {
    $client = new GeminiClient(
        Env::get('GEMINI_API_KEY'),
        Env::get('GEMINI_MODEL', 'gemini-1.5-flash'),
        $systemPrompt
    );
    
    $reply = $client->generateWithMessages($messages);
} catch (\Exception $e) {
    Response::error('llm_error', 'Error al generar respuesta: ' . $e->getMessage(), 500);
}

// Guardar o actualizar ejecución
$repo = new VoiceExecutionsRepo();

// Generar título si es nueva conversación
$title = $message;
if (strlen($title) > 60) {
    $title = substr($title, 0, 57) . '...';
}

// Preparar historial completo para guardar
$fullHistory = $history;
$fullHistory[] = ['role' => 'user', 'content' => $message];
$fullHistory[] = ['role' => 'assistant', 'content' => $reply];

$inputData = [
    'history' => $fullHistory
];

if ($executionId) {
    // Actualizar ejecución existente
    $repo->update($executionId, (int)$user['id'], [
        'input_data' => $inputData,
        'output_content' => $reply
    ]);
} else {
    // Crear nueva ejecución
    $executionId = $repo->create([
        'user_id' => (int)$user['id'],
        'voice_id' => $voiceId,
        'title' => $title,
        'input_data' => $inputData,
        'output_content' => $reply,
        'model' => Env::get('GEMINI_MODEL', 'gemini-1.5-flash')
    ]);
}

Response::json([
    'success' => true,
    'reply' => $reply,
    'execution_id' => $executionId
]);
