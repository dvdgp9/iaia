<?php
/**
 * Script temporal para ejecutar la ingesta RAG desde el navegador
 * ¡ELIMINAR DESPUÉS DE USAR!
 */
require_once __DIR__ . '/../../../src/App/bootstrap.php';
use App\Session;

// Inicializar sesión
Session::start();

// Seguridad básica: solo admin
$user = Session::user();
if (!$user || $user['role'] !== 'admin') {
    die("Acceso denegado. Debes ser admin.");
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
