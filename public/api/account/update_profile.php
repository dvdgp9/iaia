<?php
require_once __DIR__ . '/../../../src/App/bootstrap.php';
require_once __DIR__ . '/../../../src/Auth/AuthService.php';
require_once __DIR__ . '/../../../src/Repos/UsersRepo.php';

use App\Response;
use App\Session;
use Auth\AuthService;
use Repos\UsersRepo;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('method_not_allowed', 'Sólo POST', 405);
}

Session::requireCsrf();
$user = AuthService::requireAuth();

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$firstName = trim($input['first_name'] ?? '');
$lastName = trim($input['last_name'] ?? '');

if ($firstName === '' || $lastName === '') {
    Response::error('validation_error', 'Nombre y apellidos son obligatorios', 400);
}

$repo = new UsersRepo();
$repo->updateProfile((int)$user['id'], $firstName, $lastName);

// Actualizar sesión
$_SESSION['user']['first_name'] = $firstName;
$_SESSION['user']['last_name'] = $lastName;

Response::json([
    'success' => true,
    'user' => [
        'first_name' => $firstName,
        'last_name' => $lastName
    ]
]);
