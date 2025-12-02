<?php
/**
 * FAQ Chatbot Endpoint
 * 
 * Chatbot de dudas rápidas usando QWEN Flash.
 * No persiste en BD, pero recibe historial para continuidad de conversación.
 */
require_once __DIR__ . '/../../src/App/bootstrap.php';
require_once __DIR__ . '/../../src/Chat/QwenClient.php';
require_once __DIR__ . '/../../src/Auth/AuthService.php';

use App\Response;
use App\Session;
use Auth\AuthService;
use Chat\QwenClient;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('method_not_allowed', 'Sólo POST', 405);
}

// Requiere sesión y CSRF
$user = AuthService::requireAuth();
Session::requireCsrf();

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$message = trim((string)($input['message'] ?? ''));
$history = $input['history'] ?? [];

if ($message === '') {
    Response::error('validation_error', 'Se requiere un mensaje', 400);
}

// Validar historial (array de {role, content})
if (!is_array($history)) {
    $history = [];
}

// Limitar historial a últimos 20 mensajes para no exceder contexto
if (count($history) > 20) {
    $history = array_slice($history, -20);
}

// Cargar system prompt FAQ
$faqPromptPath = __DIR__ . '/../../docs/context/faq_prompt.md';
$systemPrompt = file_exists($faqPromptPath) ? file_get_contents($faqPromptPath) : '';

// Crear cliente QWEN con modelo flash
$qwenClient = new QwenClient(
    null,           // API key desde .env
    'qwen-flash',   // Modelo rápido
    $systemPrompt
);

// Construir mensajes: historial + mensaje actual
$messages = [];
foreach ($history as $h) {
    if (isset($h['role']) && isset($h['content'])) {
        $messages[] = [
            'role' => $h['role'] === 'assistant' ? 'assistant' : 'user',
            'content' => (string)$h['content']
        ];
    }
}
// Añadir mensaje actual
$messages[] = ['role' => 'user', 'content' => $message];

try {
    $reply = $qwenClient->generateWithMessages($messages);
    
    Response::json([
        'reply' => $reply,
        'model' => 'qwen-flash'
    ]);
} catch (\Exception $e) {
    Response::error('faq_error', 'Error al procesar pregunta: ' . $e->getMessage(), 500);
}
