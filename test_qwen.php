<?php
// Script de diagnóstico para verificar la conexión con Qwen
require_once __DIR__ . '/src/App/Env.php';

use App\Env;

// Cargar variables de entorno
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

echo "=== DIAGNÓSTICO QWEN API ===\n\n";

// 1. Verificar que las variables existen
$apiKey = getenv('QWEN_API_KEY');
$model = getenv('QWEN_MODEL');
$provider = getenv('LLM_PROVIDER');

echo "1. Variables de entorno:\n";
echo "   QWEN_API_KEY: " . ($apiKey ? '✓ Presente' : '✗ NO ENCONTRADA') . "\n";
if ($apiKey) {
    echo "   - Longitud: " . strlen($apiKey) . " caracteres\n";
    echo "   - Primeros 10 chars: " . substr($apiKey, 0, 10) . "...\n";
    echo "   - Últimos 5 chars: ..." . substr($apiKey, -5) . "\n";
    echo "   - Tiene espacios al inicio: " . ($apiKey !== ltrim($apiKey) ? '✗ SÍ (PROBLEMA!)' : '✓ No') . "\n";
    echo "   - Tiene espacios al final: " . ($apiKey !== rtrim($apiKey) ? '✗ SÍ (PROBLEMA!)' : '✓ No') . "\n";
    echo "   - Empieza con 'sk-': " . (str_starts_with($apiKey, 'sk-') ? '✓ Sí' : '✗ No (¿es correcta?)') . "\n";
}
echo "   QWEN_MODEL: " . ($model ?: '(por defecto: qwen-plus)') . "\n";
echo "   LLM_PROVIDER: " . ($provider ?: 'NO CONFIGURADO') . "\n\n";

if (!$apiKey) {
    echo "❌ ERROR: No se encuentra QWEN_API_KEY en .env\n";
    exit(1);
}

// 2. Limpiar la API key de espacios
$apiKey = trim($apiKey);

// 3. Hacer una petición de prueba
echo "2. Probando conexión con Qwen API...\n";

$url = 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions';
$data = [
    'model' => $model ?: 'qwen-plus',
    'messages' => [
        [
            'role' => 'user',
            'content' => 'Di hola'
        ]
    ]
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ],
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_TIMEOUT => 30,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "   - HTTP Status: " . $httpCode . "\n";

if ($error) {
    echo "   - cURL Error: " . $error . "\n";
}

if ($response) {
    $decoded = json_decode($response, true);
    echo "   - Respuesta:\n";
    echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "✅ ÉXITO! La API funciona correctamente\n";
        if (isset($decoded['choices'][0]['message']['content'])) {
            echo "   Respuesta de Qwen: " . $decoded['choices'][0]['message']['content'] . "\n";
        }
    } else {
        echo "❌ ERROR: La API devolvió un error\n";
        if (isset($decoded['error']['message'])) {
            echo "   Mensaje: " . $decoded['error']['message'] . "\n";
        }
        if (isset($decoded['message'])) {
            echo "   Mensaje: " . $decoded['message'] . "\n";
        }
    }
} else {
    echo "❌ ERROR: No se recibió respuesta\n";
}

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";
