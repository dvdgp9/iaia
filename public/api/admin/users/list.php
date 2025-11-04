<?php
require_once __DIR__ . '/../../../../src/App/bootstrap.php';
require_once __DIR__ . '/../../../../src/Auth/AdminGuard.php';
require_once __DIR__ . '/../../../../src/Repos/UsersRepo.php';

use App\Response;
use Auth\AdminGuard;
use Repos\UsersRepo;

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('method_not_allowed', 'Sólo GET', 405);
}

AdminGuard::requireSuperadmin();

$repo = new UsersRepo();
$users = $repo->listAll();

Response::json(['users' => $users]);
