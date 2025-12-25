<?php
require_once dirname(__DIR__, 3) . '/src/App/bootstrap.php';

use App\Session;
use App\Response;
use Jobs\BackgroundJobsRepo;

// Iniciar sesión y verificar autenticación
Session::start();
if (!Session::isAuthenticated()) {
    Response::error('not_authenticated', 'Usuario no autenticado', 401);
}

$user = Session::user();
$userId = $user['id'];

// Obtener datos del request
$input = json_decode(file_get_contents('php://input'), true);
$jobId = $input['job_id'] ?? null;

if (!$jobId) {
    Response::error('missing_job_id', 'Falta el ID del job', 400);
}

$repo = new BackgroundJobsRepo();
$job = $repo->getById($jobId);

if (!$job) {
    Response::error('job_not_found', 'Job no encontrado', 404);
}

// Verificar que el job pertenece al usuario
if ($job['user_id'] !== $userId) {
    Response::error('forbidden', 'No tienes permiso para cancelar este job', 403);
}

// Solo se pueden cancelar jobs pending o processing
if (!in_array($job['status'], ['pending', 'processing'])) {
    Response::error('invalid_status', 'El job no se puede cancelar (estado: ' . $job['status'] . ')', 400);
}

// Marcar como failed con mensaje de cancelación
$repo->markFailed($jobId, 'Cancelado por el usuario');

Response::success([
    'message' => 'Job cancelado correctamente',
    'job_id' => $jobId
]);
