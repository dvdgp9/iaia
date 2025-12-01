<?php
require_once __DIR__ . '/../../../src/App/bootstrap.php';
require_once __DIR__ . '/../../../src/Auth/AuthService.php';
require_once __DIR__ . '/../../../src/Repos/FoldersRepo.php';

use App\Response;
use App\Session;
use Auth\AuthService;
use Repos\FoldersRepo;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('method_not_allowed', 'Sólo POST', 405);
}

$user = AuthService::requireAuth();
Session::requireCsrf();

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$id = (int)($input['id'] ?? 0);
$name = isset($input['name']) ? trim((string)$input['name']) : '';

if ($id <= 0) {
    Response::error('validation_error', 'ID de carpeta inválido', 400);
}

if ($name === '') {
    Response::error('validation_error', 'El nombre es obligatorio', 400);
}

if (mb_strlen($name) > 150) {
    Response::error('validation_error', 'El nombre no puede exceder 150 caracteres', 400);
}

$repo = new FoldersRepo();

if (!$repo->rename((int)$user['id'], $id, $name)) {
    Response::error('not_found', 'Carpeta no encontrada', 404);
}

Response::json(['ok' => true]);
