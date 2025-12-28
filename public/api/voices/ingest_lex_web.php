<?php
/**
 * Script temporal para ejecutar la ingesta RAG desde el navegador
 * ¡ELIMINAR DESPUÉS DE USAR!
 */
require_once __DIR__ . '/../../../src/App/bootstrap.php';
use App\Session;

// Inicializar sesión
Session::start();

/** 
 * COMENTADO TEMPORALMENTE PARA PERMITIR EJECUCIÓN
 * El usuario David Gutiérrez (ID 1) tiene problemas de sesión en esta URL específica.
 * BORRAR ESTE ARCHIVO INMEDIATAMENTE DESPUÉS DE LA INGESTA.
 *
$user = Session::user();
if (!$user) {
    die("Acceso denegado. No hay sesión de usuario activa.");
}
if ($user['role'] !== 'admin' && $user['role'] !== 'superadmin') {
    die("Acceso denegado. Tu rol actual es: " . htmlspecialchars($user['role']) . ". Se requiere admin.");
}
*/

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

// Intentar localizar el binario de PHP
$phpBinary = 'php';
$possiblePaths = [
    '/usr/bin/php',
    '/usr/local/bin/php',
    '/usr/bin/php8.1',
    '/usr/bin/php8.2',
    '/usr/bin/php8.3',
    '/opt/plesk/php/8.1/bin/php',
    '/opt/plesk/php/8.2/bin/php',
    '/opt/plesk/php/8.3/bin/php',
    '/opt/plesk/php/8.4/bin/php',
    'C:\Program Files\PHP\v8.2\php.exe',
];

foreach ($possiblePaths as $path) {
    if (@is_executable($path)) {
        $phpBinary = $path;
        break;
    }
}

// Si no se encuentra ninguno, intentar con 'php' pero capturar el error de forma más limpia
echo "Intentando usar binario: " . htmlspecialchars($phpBinary) . "\n";
echo "Ruta absoluta del script: " . htmlspecialchars($scriptPath) . "\n";
echo "--------------------------------------------------\n";

passthru("$phpBinary " . escapeshellarg($scriptPath) . " 2>&1", $returnVar);

if ($returnVar === 0) {
    echo "\n\n✅ INGESTA COMPLETADA CON ÉXITO";
} else {
    echo "\n\n❌ ERROR EN LA INGESTA (Código: $returnVar)";
}
echo "</pre>";
