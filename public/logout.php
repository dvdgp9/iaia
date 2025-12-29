<?php
require_once __DIR__ . '/../src/App/bootstrap.php';
use App\Session;

Session::start();
Session::logout();
header('Location: /login.php');
exit;
