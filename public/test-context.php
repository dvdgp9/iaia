<?php
/**
 * Test temporal para verificar el contexto corporativo
 * ELIMINAR DESPUÉS DE PROBAR
 */
require_once __DIR__ . '/../src/App/bootstrap.php';
require_once __DIR__ . '/../src/Chat/ContextBuilder.php';

use Chat\ContextBuilder;

echo "<pre>";
echo "=== TEST DE CONTEXTO CORPORATIVO ===\n\n";

$contextBuilder = new ContextBuilder();
$systemPrompt = $contextBuilder->buildSystemPrompt();

echo "Directorio de contexto (ContextBuilder usa): " . dirname(dirname(__DIR__)) . "/docs/context\n";
echo "Longitud del system prompt: " . strlen($systemPrompt) . " caracteres\n";
echo "Primeros 500 caracteres:\n";
echo "---\n";
echo htmlspecialchars(substr($systemPrompt, 0, 500));
echo "\n---\n\n";

// Verificar archivos
$contextDir = dirname(__DIR__) . '/docs/context';
echo "Buscando archivos en: $contextDir\n";
if (is_dir($contextDir)) {
    $files = glob($contextDir . '/*.md');
    echo "Archivos encontrados: " . count($files) . "\n";
    foreach ($files as $f) {
        echo "  - " . basename($f) . " (" . filesize($f) . " bytes)\n";
    }
} else {
    echo "ERROR: Directorio no existe!\n";
}

echo "</pre>";
