<?php
/**
 * Script temporal para ejecutar la ingesta RAG desde el navegador
 * ¡ELIMINAR DESPUÉS DE USAR!
 */
require_once __DIR__ . '/../../src/App/bootstrap.php';
use App\Session;

// Seguridad básica: solo admin
$user = Session::user();
if (!$user || $user['role'] !== 'admin') {
    die("Acceso denegado. Debes ser admin.");
}

echo "<h1>Iniciando ingesta RAG...</h1>";
echo "<pre>";

// Ejecutar el script de ingesta y capturar salida
$output = [];
$returnVar = 0;
exec("php " . __DIR__ . "/ingest_lex.php 2>&1", $output, $returnVar);

echo implode("\n", $output);

if ($returnVar === 0) {
    echo "\n\n✅ INGESTA COMPLETADA CON ÉXITO";
} else {
    echo "\n\n❌ ERROR EN LA INGESTA (Código: $returnVar)";
}
echo "</pre>";
