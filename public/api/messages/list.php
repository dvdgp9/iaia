<?php
require_once __DIR__ . '/../../../src/App/bootstrap.php';
require_once __DIR__ . '/../../../src/Auth/AuthService.php';
require_once __DIR__ . '/../../../src/Repos/ConversationsRepo.php';
require_once __DIR__ . '/../../../src/Repos/MessagesRepo.php';
require_once __DIR__ . '/../../../src/Repos/ChatFilesRepo.php';

use App\Response;
use Auth\AuthService;
use Repos\ConversationsRepo;
use Repos\MessagesRepo;
use Repos\ChatFilesRepo;

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('method_not_allowed', 'Sólo GET', 405);
}

$user = AuthService::requireAuth();
$conversationId = isset($_GET['conversation_id']) ? (int)$_GET['conversation_id'] : 0;
if ($conversationId <= 0) {
    Response::error('validation_error', 'conversation_id es obligatorio', 400);
}

$convos = new ConversationsRepo();
if (!$convos->findByIdForUser($conversationId, (int)$user['id'])) {
    Response::error('not_found', 'Conversación no encontrada', 404);
}

$msgs = new MessagesRepo();
$filesRepo = new ChatFilesRepo();
$items = $msgs->listByConversation($conversationId);

// Enriquecer mensajes con información de archivos e imágenes
foreach ($items as &$item) {
    if (!empty($item['file_id'])) {
        $file = $filesRepo->findById((int)$item['file_id']);
        if ($file) {
            $item['file'] = [
                'id' => $file['id'],
                'name' => $file['original_name'],
                'mime_type' => $file['mime_type'],
                'url' => '/api/files/serve.php?id=' . $file['id'],
                'expired' => strtotime($file['expires_at']) < time()
            ];
        }
    }
    
    // Decodificar images JSON si existe
    if (!empty($item['images'])) {
        $item['images'] = json_decode($item['images'], true);
    }
}

Response::json(['items' => $items]);
