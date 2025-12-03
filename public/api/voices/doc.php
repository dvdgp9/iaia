<?php
/**
 * API: Obtener contenido de un documento de una voz
 * GET /api/voices/doc.php?voice_id=lex&doc_id=convenio_colectivo
 */

require_once __DIR__ . '/../../../src/App/bootstrap.php';
require_once __DIR__ . '/../../../src/Voices/VoiceContextBuilder.php';

use App\Session;
use App\Response;
use Voices\VoiceContextBuilder;

$user = Session::user();
if (!$user) {
    Response::error('unauthorized', 'Sesión no válida', 401);
}

$voiceId = $_GET['voice_id'] ?? '';
$docId = $_GET['doc_id'] ?? '';

if (!$voiceId) {
    Response::error('missing_voice', 'Se requiere voice_id', 400);
}
if (!$docId) {
    Response::error('missing_doc', 'Se requiere doc_id', 400);
}

$builder = new VoiceContextBuilder($voiceId);

if (!$builder->voiceExists()) {
    Response::error('invalid_voice', 'Voz no encontrada', 404);
}

// Buscar el documento
$docs = $builder->listDocuments();
$doc = null;
foreach ($docs as $d) {
    if ($d['id'] === $docId) {
        $doc = $d;
        break;
    }
}

if (!$doc) {
    Response::error('not_found', 'Documento no encontrado', 404);
}

// Leer contenido
$content = file_get_contents($doc['path']);
if ($content === false) {
    Response::error('read_error', 'Error al leer el documento', 500);
}

Response::json([
    'success' => true,
    'document' => [
        'id' => $doc['id'],
        'name' => $doc['name'],
        'size' => $doc['size'],
        'content' => $content
    ]
]);
