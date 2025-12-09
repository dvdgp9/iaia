<?php
/**
 * FAQ Chatbot Endpoint
 * 
 * Chatbot de dudas rápidas usando QWEN Turbo.
 * No persiste en BD, pero recibe historial para continuidad de conversación.
 * 
 * IMPORTANTE: Usa ContextBuilder para cargar TODA la información corporativa.
 * El modelo está instruido para NO inventar información.
 */
require_once __DIR__ . '/../../src/App/bootstrap.php';
require_once __DIR__ . '/../../src/Chat/OpenRouterClient.php';
require_once __DIR__ . '/../../src/Chat/ContextBuilder.php';
require_once __DIR__ . '/../../src/Auth/AuthService.php';

use App\Response;
use App\Session;
use Auth\AuthService;
use Chat\OpenRouterClient;
use Chat\ContextBuilder;

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

// Cargar contexto específico para FAQ desde docs/context_faq/
// Separado del chat principal para permitir instrucciones específicas
$faqContextDir = dirname(dirname(__DIR__)) . '/docs/context_faq';
$contextBuilder = new ContextBuilder($faqContextDir);
$systemPrompt = $contextBuilder->buildSystemPrompt();

// Crear cliente OpenRouter con modelo rápido para FAQ
// - qwen/qwen-plus: buena relación calidad/precio para FAQ
// - temperature 0.1: respuestas muy deterministas, menos creatividad = menos alucinaciones
// - max_tokens 600: respuestas cortas pero suficientes
$llmClient = new OpenRouterClient(
    null,                   // API key desde .env
    'qwen/qwen-plus',       // Modelo rápido vía OpenRouter
    $systemPrompt,
    0.1,                    // Temperature muy baja para máxima fiabilidad
    600                     // Tokens suficientes para respuestas concisas
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
    $reply = $llmClient->generateWithMessages($messages);
    
    Response::json([
        'reply' => $reply,
        'model' => $llmClient->getModel()
    ]);
} catch (\Exception $e) {
    Response::error('faq_error', 'Error al procesar pregunta: ' . $e->getMessage(), 500);
}
