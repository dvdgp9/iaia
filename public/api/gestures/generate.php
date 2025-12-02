<?php
/**
 * API: Ejecutar gesto y guardar resultado
 * POST /api/gestures/generate.php
 * 
 * Body JSON:
 * {
 *   "gesture_type": "write-article",
 *   "prompt": "...",
 *   "input_data": { ... },      // Datos del formulario para guardar
 *   "content_type": "blog",     // Opcional
 *   "business_line": "ebone"    // Opcional
 * }
 */

require_once __DIR__ . '/../../../src/App/bootstrap.php';
require_once __DIR__ . '/../../../src/Chat/ContextBuilder.php';
require_once __DIR__ . '/../../../src/Chat/LlmProvider.php';
require_once __DIR__ . '/../../../src/Chat/GeminiClient.php';
require_once __DIR__ . '/../../../src/Chat/GeminiProvider.php';
require_once __DIR__ . '/../../../src/Chat/QwenClient.php';
require_once __DIR__ . '/../../../src/Chat/QwenProvider.php';
require_once __DIR__ . '/../../../src/Chat/LlmProviderFactory.php';

use App\Session;
use App\Response;
use Chat\LlmProviderFactory;
use Chat\ContextBuilder;
use Gestures\GestureExecutionsRepo;

Session::start();
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

// Parsear body
$body = json_decode(file_get_contents('php://input'), true);
if (!$body) {
    Response::error('invalid_body', 'Body JSON inválido', 400);
}

$gestureType = $body['gesture_type'] ?? '';
$prompt = $body['prompt'] ?? '';
$inputData = $body['input_data'] ?? [];
$contentType = $body['content_type'] ?? null;
$businessLine = $body['business_line'] ?? null;

if (!$gestureType || !$prompt) {
    Response::error('missing_params', 'Faltan parámetros requeridos', 400);
}

// Construir contexto del sistema
$contextBuilder = new ContextBuilder();
$systemInstruction = $contextBuilder->build();

// Crear cliente LLM
$provider = LlmProviderFactory::create();
$provider->setSystemInstruction($systemInstruction);

// Generar contenido
try {
    $response = $provider->generateWithMessages([
        ['role' => 'user', 'content' => $prompt]
    ]);
} catch (\Exception $e) {
    Response::error('llm_error', 'Error al generar contenido: ' . $e->getMessage(), 500);
}

// Generar título automático (primeras palabras del tema/qué)
$title = generateTitle($inputData, $contentType);

// Guardar en historial
$repo = new GestureExecutionsRepo();
$executionId = $repo->create([
    'user_id' => $user['id'],
    'gesture_type' => $gestureType,
    'title' => $title,
    'input_data' => $inputData,
    'output_content' => $response,
    'content_type' => $contentType,
    'business_line' => $businessLine,
    'model' => $_ENV['LLM_PROVIDER'] ?? 'gemini',
]);

Response::json([
    'success' => true,
    'execution_id' => $executionId,
    'content' => $response,
    'title' => $title,
]);

/**
 * Genera un título descriptivo basado en los datos de entrada
 */
function generateTitle(array $inputData, ?string $contentType): string
{
    // Intentar extraer el tema/asunto principal
    $topic = $inputData['topic'] ?? $inputData['what'] ?? $inputData['theme'] ?? '';
    
    if ($topic) {
        // Truncar a 80 caracteres
        $title = mb_strlen($topic) > 80 ? mb_substr($topic, 0, 77) . '...' : $topic;
    } else {
        // Fallback con tipo de contenido
        $typeLabels = [
            'informativo' => 'Artículo informativo',
            'blog' => 'Post de blog',
            'nota-prensa' => 'Nota de prensa',
        ];
        $title = $typeLabels[$contentType] ?? 'Contenido generado';
        $title .= ' - ' . date('d/m H:i');
    }
    
    return $title;
}
