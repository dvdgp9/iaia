<?php
require_once __DIR__ . '/../../src/App/bootstrap.php';
require_once __DIR__ . '/../../src/Auth/AuthService.php';

use App\Response;
use App\Session;
use Auth\AuthService;

// Content-Type y método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('method_not_allowed', 'Sólo POST', 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$email = trim($input['email'] ?? '');
$password = (string)($input['password'] ?? '');

if ($email === '' || $password === '') {
    Response::error('validation_error', 'Email y password son obligatorios', 400);
}

$user = AuthService::login($email, $password);

Response::json([
    'user' => $user,
    'csrf_token' => $_SESSION['csrf_token'] ?? null,
]);
