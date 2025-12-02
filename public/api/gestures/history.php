<?php
/**
 * API: Listar historial de ejecuciones de gestos
 * GET /api/gestures/history.php?type=write-article
 */

require_once __DIR__ . '/../../../src/App/bootstrap.php';

use App\Session;
use App\Response;
use Gestures\GestureExecutionsRepo;

Session::start();
$user = Session::user();
if (!$user) {
    Response::error('unauthorized', 'Sesión no válida', 401);
}

$gestureType = $_GET['type'] ?? null;
$limit = min((int)($_GET['limit'] ?? 20), 50);

$repo = new GestureExecutionsRepo();

if ($gestureType) {
    $items = $repo->listByUserAndType($user['id'], $gestureType, $limit);
} else {
    $items = $repo->listByUser($user['id'], $limit);
}

Response::json([
    'items' => $items
]);
