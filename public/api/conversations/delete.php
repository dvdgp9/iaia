<?php
require_once __DIR__ . '/../../../src/App/bootstrap.php';
require_once __DIR__ . '/../../../src/Auth/AuthService.php';
require_once __DIR__ . '/../../../src/Repos/ConversationsRepo.php';

use App\Response;
use App\Session;
use Auth\AuthService;
use Repos\ConversationsRepo;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('method_not_allowed', 'Sólo POST', 405);
}

$user = AuthService::requireAuth();

// Soportar tanto JSON como FormData (para sendBeacon al cerrar página)
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    Session::requireCsrf();
} else {
    // FormData - leer de $_POST y validar CSRF manualmente
    $input = $_POST;
    $csrfToken = $_POST['csrf_token'] ?? '';
    if ($csrfToken !== ($_SESSION['csrf_token'] ?? '')) {
        Response::error('csrf_error', 'Token CSRF inválido', 403);
    }
}

$id = (int)($input['id'] ?? 0);
if ($id <= 0) {
    Response::error('validation_error', 'Campo id es obligatorio', 400);
}

$repo = new ConversationsRepo();
if (!$repo->findByIdForUser($id, (int)$user['id'])) {
    Response::error('not_found', 'Conversación no encontrada', 404);
}
$repo->delete((int)$user['id'], $id);
Response::json(['ok' => true]);
