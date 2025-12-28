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

// Detectar tipo de archivo
$extension = strtolower(pathinfo($doc['path'], PATHINFO_EXTENSION));

// Si es PDF u otro binario, no se puede mostrar en el visor de texto
if (in_array($extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx'])) {
    Response::json([
        'success' => true,
        'document' => [
            'id' => $doc['id'],
            'name' => $doc['name'],
            'size' => $doc['size'],
            'type' => $extension,
            'isBinary' => true,
            'message' => 'Este es un archivo PDF. Los documentos están indexados y disponibles para consulta con el asistente Lex. Si necesitas ver el contenido completo, por favor descarga el archivo.'
        ]
    ]);
}

// Leer contenido de archivos de texto
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
        'type' => $extension,
        'isBinary' => false,
        'content' => $content
    ]
]);
