<?php
require_once __DIR__ . '/../../../src/App/bootstrap.php';
require_once __DIR__ . '/../../../src/Auth/AuthService.php';
require_once __DIR__ . '/../../../src/Auth/RememberService.php';

use App\Response;
use App\Session;
use Auth\AuthService;
use Auth\RememberService;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('method_not_allowed', 'Sólo POST', 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$email = trim($input['email'] ?? '');
$password = (string)($input['password'] ?? '');
$remember = !empty($input['remember']);

if ($email === '' || $password === '') {
    Response::error('validation_error', 'Email y password son obligatorios', 400);
}

$user = AuthService::login($email, $password);

if ($remember) {
    // Crear token persistente en BD + cookie
    RememberService::createToken((int)$user['id']);
}

Response::json([
    'user' => $user,
    'csrf_token' => $_SESSION['csrf_token'] ?? null,
]);
