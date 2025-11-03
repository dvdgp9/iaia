<?php
require_once __DIR__ . '/../../src/App/bootstrap.php';
require_once __DIR__ . '/../../src/Chat/GeminiClient.php';
require_once __DIR__ . '/../../src/Chat/ChatService.php';
require_once __DIR__ . '/../../src/Auth/AuthService.php';

use App\Response;
use App\Session;
use Auth\AuthService;
use Chat\ChatService;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('method_not_allowed', 'Sólo POST', 405);
}

// Requiere sesión y CSRF
$user = AuthService::requireAuth();
Session::requireCsrf();

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$message = trim((string)($input['message'] ?? ''));
if ($message === '') {
    Response::error('validation_error', 'El campo message es obligatorio', 400);
}

$svc = new ChatService();
$assistantMsg = $svc->reply($message);

Response::json([
    'conversation' => [ 'id' => null, 'title' => 'MVP (no persistido)' ],
    'message' => [
        'id' => null,
        'role' => $assistantMsg['role'],
        'content' => $assistantMsg['content'],
        'model' => getenv('GEMINI_MODEL') ?: 'gemini-1.5-flash'
    ]
]);
