<?php
require_once __DIR__ . '/../../../src/App/bootstrap.php';

use App\Response;
use App\Session;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('method_not_allowed', 'Sólo POST', 405);
}

Session::requireCsrf();
Session::logout();
http_response_code(204);
