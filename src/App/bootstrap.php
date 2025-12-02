<?php
use App\Env;
use App\Session;

require_once __DIR__ . '/Env.php';
require_once __DIR__ . '/Response.php';
require_once __DIR__ . '/Session.php';
require_once __DIR__ . '/DB.php';

// Gestures
require_once dirname(__DIR__) . '/Gestures/GestureExecutionsRepo.php';

// Cargar .env desde la raíz del proyecto
$root = dirname(dirname(__DIR__));
Env::load($root . '/.env');

// Iniciar sesión y CSRF
Session::start();
