<?php
/**
 * Script temporal para ejecutar la ingesta RAG desde el navegador
 * ¡ELIMINAR DESPUÉS DE USAR!
 */
require_once __DIR__ . '/../../../src/App/bootstrap.php';
use App\Session;

// Inicializar sesión
Session::start();

// DEBUG temporal - Eliminar después
echo "<!-- DEBUG SESSION: ";
var_dump($_SESSION);
echo " -->";

// Seguridad básica: solo admin
$user = Session::user();
if (!$user) {
    die("Acceso denegado. No hay sesión de usuario activa. Asegúrate de estar logueado en " . $_SERVER['HTTP_HOST']);
}

// Si eres superadmin pero el role no es exactamente 'admin', ajustamos la comprobación
// Opcionalmente, puedes comentar la línea de abajo temporalmente para forzar la ejecución
if ($user['role'] !== 'admin' && $user['role'] !== 'superadmin') {
    die("Acceso denegado. Tu rol actual es: " . htmlspecialchars($user['role']) . ". Se requiere admin.");
}

echo "<h1>Iniciando ingesta RAG...</h1>";
echo "<p>Esto puede tardar varios minutos (procesando 20 convenios)...</p>";
echo "<pre style='background: #1e1e1e; color: #d4d4d4; padding: 20px; border-radius: 8px; overflow-x: auto;'>";

// Ejecutar el script de ingesta y capturar salida
$output = [];
$returnVar = 0;
$scriptPath = __DIR__ . '/../../../scripts/rag/ingest_lex.php';

if (!file_exists($scriptPath)) {
    die("Error: No se encuentra el script de ingesta en $scriptPath");
}

exec("php " . escapeshellarg($scriptPath) . " 2>&1", $output, $returnVar);

echo htmlspecialchars(implode("\n", $output));

if ($returnVar === 0) {
    echo "\n\n✅ INGESTA COMPLETADA CON ÉXITO";
} else {
    echo "\n\n❌ ERROR EN LA INGESTA (Código: $returnVar)";
}
echo "</pre>";
