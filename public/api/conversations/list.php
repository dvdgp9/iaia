<?php
require_once __DIR__ . '/../../../src/App/bootstrap.php';
require_once __DIR__ . '/../../../src/Auth/AuthService.php';
require_once __DIR__ . '/../../../src/Repos/ConversationsRepo.php';

use App\Response;
use Auth\AuthService;
use Repos\ConversationsRepo;

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('method_not_allowed', 'Sólo GET', 405);
}

$user = AuthService::requireAuth();

$repo = new ConversationsRepo();
$list = $repo->listByUser((int)$user['id']);

Response::json(['items' => $list]);
