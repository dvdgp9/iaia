<?php
// Script para listar los modelos de Gemini disponibles para la API Key configurada

require_once __DIR__ . '/../src/App/bootstrap.php';

use App\Env;

$apiKey = Env::get('GEMINI_API_KEY');
if (!$apiKey) {
    fwrite(STDERR, "No se encontró GEMINI_API_KEY en .env\n");
    exit(1);
}

$url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . urlencode($apiKey);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [ 'Content-Type: application/json; charset=utf-8' ],
    CURLOPT_TIMEOUT => 15,
]);

$raw = curl_exec($ch);
$err = curl_error($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($raw === false || $err) {
    fwrite(STDERR, "Fallo en la petición cURL: $err\n");
    exit(1);
}

if ($status < 200 || $status >= 300) {
    fwrite(STDERR, "Error de la API de Google (HTTP $status):\n$raw\n");
    exit(1);
}

$data = json_decode($raw, true);
if (!$data || !isset($data['models'])) {
    fwrite(STDERR, "Respuesta inesperada de la API:\n$raw\n");
    exit(1);
}

// Imprimir solo los modelos que soportan 'generateContent'
$supportedModels = [];
foreach ($data['models'] as $model) {
    if (in_array('generateContent', $model['supportedGenerationMethods'] ?? [])) {
        $supportedModels[] = [
            'name' => $model['name'],
            'displayName' => $model['displayName'],
            'description' => $model['description'],
        ];
    }
}

if (empty($supportedModels)) {
    echo "No se encontraron modelos que soporten 'generateContent'. Respuesta completa:\n";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} else {
    echo "Modelos disponibles que soportan 'generateContent':\n";
    echo json_encode($supportedModels, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

echo "\n";
