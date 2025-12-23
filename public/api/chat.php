<?php
require_once __DIR__ . '/../../src/App/bootstrap.php';
require_once __DIR__ . '/../../src/Chat/ContextBuilder.php';
require_once __DIR__ . '/../../src/Chat/LlmProvider.php';
require_once __DIR__ . '/../../src/Chat/OpenRouterClient.php';
require_once __DIR__ . '/../../src/Chat/OpenRouterProvider.php';
require_once __DIR__ . '/../../src/Chat/LlmProviderFactory.php';
require_once __DIR__ . '/../../src/Chat/ChatService.php';
require_once __DIR__ . '/../../src/Auth/AuthService.php';
require_once __DIR__ . '/../../src/Repos/ConversationsRepo.php';
require_once __DIR__ . '/../../src/Repos/MessagesRepo.php';
require_once __DIR__ . '/../../src/Repos/ChatFilesRepo.php';

use App\Response;
use App\Session;
use Auth\AuthService;
use Chat\ChatService;
use Chat\LlmProviderFactory;
use Repos\ConversationsRepo;
use Repos\MessagesRepo;
use Repos\ChatFilesRepo;

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
$imageMode = !empty($input['image_mode']); // Modo generación de imágenes (nanobanana)

// Opcional: permitir elegir modelo desde el cliente (formato: provider/model)
$modelName = isset($input['model']) && $input['model'] !== ''
    ? (string)$input['model']
    : 'qwen/qwen-plus';

// Si es modo imagen, forzar modelo de generación de imágenes
if ($imageMode) {
    $modelName = 'google/gemini-3-pro-image-preview';
}

// Validar que haya mensaje o archivo
if ($message === '' && !$file && !$fileId) {
    Response::error('validation_error', 'Se requiere un mensaje o archivo', 400);
}

$filesRepo = new ChatFilesRepo();

// Si hay file_id, cargar archivo desde la base de datos
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

// Validar archivo si existe (inline o cargado)
if ($file) {
    if (!isset($file['mime_type']) || !isset($file['data'])) {
        Response::error('validation_error', 'Datos de archivo inválidos', 400);
    }
    
    // Validar tipo MIME
    $allowedTypes = ['application/pdf', 'image/png', 'image/jpeg', 'image/gif', 'image/webp'];
    if (!in_array($file['mime_type'], $allowedTypes)) {
        Response::error('validation_error', 'Tipo de archivo no soportado', 400);
    }
    // Si es imagen y el cliente no ha especificado modelo, forzar uno multimodal (Gemini 3 Flash Preview)
    if (
        (!isset($input['model']) || $input['model'] === '')
        && str_starts_with((string)$file['mime_type'], 'image/')
    ) {
        $modelName = 'google/gemini-3-flash-preview';
    }
}

$convos = new ConversationsRepo();
$msgs = new MessagesRepo();

if ($conversationId <= 0) {
    $conversationId = $convos->create((int)$user['id'], null);
}

// Guardar mensaje de usuario (con file_id si existe)
$userMsgId = $msgs->create($conversationId, (int)$user['id'], 'user', $message, null, null, null, $fileId);

// Actualizar archivo con conversation_id y message_id si es nuevo
if ($fileId) {
    $filesRepo->updateConversationId($fileId, $conversationId);
    $filesRepo->updateMessageId($fileId, $userMsgId);
}

// Auto-titular si el título sigue siendo el genérico
$convos->autoTitle($conversationId, $message);

// Para generación de imágenes (nanobanana), no enviar contexto corporativo
$withContext = !$imageMode;
$provider = LlmProviderFactory::create($modelName, $withContext);
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
    $maxContextChars = 50000;
    
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

// Determinar modalities para la generación
$modalities = $imageMode ? ['image', 'text'] : null;

$assistantMsg = $svc->replyWithHistory($history, $modalities);

// Determinar el modelo usado
$usedModel = $provider->getModel();

// Obtener imágenes generadas si las hay
$generatedImages = $svc->getLastImages();

// Guardar respuesta de asistente
$assistantMsgId = $msgs->create($conversationId, null, 'assistant', $assistantMsg['content'], $usedModel ?: null, null, null);

// Actualizar updated_at de la conversación
$convos->touch($conversationId);

$response = [
    'conversation' => [ 'id' => $conversationId ],
    'message' => [
        'id' => $assistantMsgId,
        'role' => $assistantMsg['role'],
        'content' => $assistantMsg['content'],
        'model' => $usedModel ?: null
    ],
    'context_truncated' => $contextTruncated
];

// Incluir imágenes generadas si las hay
if ($generatedImages && !empty($generatedImages)) {
    $response['message']['images'] = $generatedImages;
}

Response::json($response);
