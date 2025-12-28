<?php
/**
 * Script de ingesta RAG - Procesa UN archivo por llamada
 * Recarga la página para procesar el siguiente archivo
 * ¡ELIMINAR DESPUÉS DE USAR!
 */

set_time_limit(120); // 2 minutos max por archivo
ini_set('memory_limit', '256M');

require_once __DIR__ . '/../../../src/App/bootstrap.php';
require_once __DIR__ . '/../../../src/Rag/QdrantClient.php';
require_once __DIR__ . '/../../../src/Rag/EmbeddingService.php';

use App\Env;
use Rag\QdrantClient;
use Rag\EmbeddingService;

// Configuración
define('COLLECTION_NAME', 'lex_convenios');
define('VECTOR_SIZE', 4096);
define('CHUNK_SIZE', 1000); // Aumentado para artículos más largos
define('CHUNK_OVERLAP', 100);
define('BATCH_SIZE', 2); // Reducido un poco para compensar chunks más grandes

$conveniosPath = __DIR__ . '/../../../docs/context/voices/lex/convenios';
$progressFile = sys_get_temp_dir() . '/lex_ingest_progress.json';

// Leer progreso
$progress = file_exists($progressFile) ? json_decode(file_get_contents($progressFile), true) : [];
$processedFiles = $progress['processed'] ?? [];
$pointId = $progress['pointId'] ?? 1;
$totalChunks = $progress['totalChunks'] ?? 0;

// Reset si se pide
if (isset($_GET['reset'])) {
    @unlink($progressFile);
    
    // También intentar borrar la colección en Qdrant para limpieza total
    try {
        $qdrantHost = Env::get('QDRANT_HOST', 'localhost');
        $qdrantPort = (int) Env::get('QDRANT_PORT', 6333);
        $qdrant = new Rag\QdrantClient($qdrantHost, $qdrantPort);
        if ($qdrant->collectionExists(COLLECTION_NAME)) {
            $qdrant->deleteCollection(COLLECTION_NAME);
        }
    } catch (\Exception $e) {
        // Ignorar errores aquí, el reset de archivos es lo principal
    }

    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

// HTML
echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Ingesta RAG</title>";
echo "<meta http-equiv='refresh' content='3'>"; // Auto-refresh cada 3s
echo "<style>body{font-family:system-ui;max-width:800px;margin:40px auto;padding:20px;background:#0d1117;color:#c9d1d9}";
echo "pre{background:#161b22;padding:20px;border-radius:8px;overflow-x:auto}";
echo ".ok{color:#3fb950}.err{color:#f85149}.warn{color:#d29922}a{color:#58a6ff}</style></head><body>";

echo "<h1>🔄 Ingesta RAG para Lex</h1>";

try {
    // Verificar API key
    $openrouterKey = Env::get('OPENROUTER_API_KEY');
    if (!$openrouterKey) {
        throw new Exception("OPENROUTER_API_KEY no configurada");
    }

    // Conectar Qdrant
    $qdrantHost = Env::get('QDRANT_HOST', 'localhost');
    $qdrantPort = (int) Env::get('QDRANT_PORT', 6333);
    $qdrant = new QdrantClient($qdrantHost, $qdrantPort);
    $embeddings = new EmbeddingService($openrouterKey);

    if (!$qdrant->health()) {
        throw new Exception("No se puede conectar con Qdrant en {$qdrantHost}:{$qdrantPort}");
    }

    // Crear colección si no existe
    if (!$qdrant->collectionExists(COLLECTION_NAME)) {
        $qdrant->createCollection(COLLECTION_NAME, VECTOR_SIZE, 'Cosine');
        echo "<p class='ok'>✓ Colección creada</p>";
    }

    // Buscar archivos pendientes
    $txtFiles = glob($conveniosPath . '/*.txt');
    $files = array_filter($txtFiles, fn($f) => basename($f) !== 'README.md');
    $files = array_values($files);
    
    $pending = array_filter($files, fn($f) => !in_array(basename($f), $processedFiles));
    $pending = array_values($pending);

    $total = count($files);
    $done = count($processedFiles);

    echo "<h2>Progreso: {$done}/{$total} archivos</h2>";
    echo "<progress value='{$done}' max='{$total}' style='width:100%;height:30px'></progress>";

    if (empty($pending)) {
        // Terminado
        echo "<pre>";
        echo "<span class='ok'>✅ INGESTA COMPLETADA</span>\n\n";
        echo "Archivos procesados: {$done}\n";
        echo "Total chunks indexados: {$totalChunks}\n";
        $count = $qdrant->countPoints(COLLECTION_NAME);
        echo "Puntos en colección: {$count}\n";
        echo "</pre>";
        echo "<p><a href='?reset=1'>🔄 Reiniciar ingesta</a></p>";
        echo "<p><strong>Ahora borra este archivo: public/api/voices/ingest_lex_web.php</strong></p>";
        // Quitar auto-refresh
        echo "<script>document.querySelector('meta[http-equiv]').remove();</script>";
        exit;
    }

    // Procesar siguiente archivo
    $file = $pending[0];
    $filename = basename($file);
    
    echo "<pre>";
    echo "→ Procesando: <strong>{$filename}</strong>\n";

    $text = file_get_contents($file);
    // Limpiar caracteres problemáticos para JSON
    $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', ' ', $text);
    $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
    // Eliminar caracteres no UTF-8 válidos
    $text = iconv('UTF-8', 'UTF-8//IGNORE', $text);
    // Normalizar espacios y saltos de línea
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $text = preg_replace('/\n{3,}/', "\n\n", $text);
    
    echo "  Tamaño: " . number_format(strlen($text)) . " chars\n";

    if (strlen(trim($text)) < 50) {
        echo "<span class='warn'>  ⚠ Archivo vacío, saltando...</span>\n";
        $processedFiles[] = $filename;
    } else {
        // Chunking
        $chunks = chunkText($text, CHUNK_SIZE, CHUNK_OVERLAP);
        echo "  Chunks: " . count($chunks) . "\n";

        // Procesar en batches
        $batches = array_chunk($chunks, BATCH_SIZE);
        $fileChunks = 0;
        
        foreach ($batches as $batchIndex => $batch) {
            try {
                // Sanitizar cada texto para que sea JSON-safe
                $batchTexts = array_map(function($chunk) {
                    $t = $chunk['text'];
                    // Forzar encoding UTF-8 limpio
                    $t = mb_convert_encoding($t, 'UTF-8', 'UTF-8');
                    // Verificar que sea JSON-encodeable, si no, limpiar
                    if (json_encode($t) === false) {
                        $t = preg_replace('/[^\PC\s]/u', '', $t);
                    }
                    return $t;
                }, $batch);
                
                $vectors = $embeddings->embedBatch($batchTexts);
                
                $points = [];
                foreach ($batch as $i => $chunk) {
                    $points[] = [
                        'id' => $pointId++,
                        'vector' => $vectors[$i],
                        'payload' => [
                            'text' => $batchTexts[$i], // Usar texto sanitizado
                            'document_id' => pathinfo($filename, PATHINFO_FILENAME),
                            'document_name' => $filename,
                            'chunk_index' => $chunk['index'],
                            'section' => $chunk['section'] ?? ''
                        ]
                    ];
                }
                
                $qdrant->upsertPoints(COLLECTION_NAME, $points);
                $fileChunks += count($batch);
                $totalChunks += count($batch);
                
            } catch (Exception $e) {
                echo "<span class='err'>  ✗ Batch error: " . htmlspecialchars($e->getMessage()) . "</span>\n";
            }
            
            usleep(300000); // 300ms entre batches
        }
        
        echo "<span class='ok'>  ✓ {$fileChunks} chunks indexados</span>\n";
        $processedFiles[] = $filename;
    }

    // Guardar progreso
    file_put_contents($progressFile, json_encode([
        'processed' => $processedFiles,
        'pointId' => $pointId,
        'totalChunks' => $totalChunks
    ]));

    echo "\n<span class='warn'>Recargando automáticamente en 3 segundos...</span>";
    echo "</pre>";

} catch (Exception $e) {
    echo "<pre class='err'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<script>document.querySelector('meta[http-equiv]').remove();</script>";
}

echo "</body></html>";

// === Funciones ===

/**
 * Chunking inteligente por artículos para documentos legales
 * Detecta artículos, capítulos y secciones, evitando cortar unidades semánticas
 */
function chunkText(string $text, int $targetTokens, int $overlap): array
{
    $charsPerToken = 4;
    $maxChars = $targetTokens * $charsPerToken;
    
    // Normalizar saltos de línea y espacios
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $text = preg_replace('/[ \t]+/', ' ', $text);
    $text = preg_replace('/\n{3,}/', "\n\n", $text);
    
    // Patrones para detectar divisiones estructurales
    $patterns = [
        'capitulo' => '/^(CAPÍTULO|TÍTULO|PARTE|SECCIÓN)\s+([IVXLCDM]+|[0-9]+)[.:\s]/im',
        'articulo' => '/^((Artículo|Art\.|ARTÍCULO)\s*([0-9]+))([.:\s]|$)/im',
    ];
    
    // Dividir por artículos primero
    $articles = [];
    $lines = explode("\n", $text);
    $currentArticle = ['header' => '', 'content' => '', 'type' => 'preambulo'];
    $currentChapter = '';
    
    foreach ($lines as $line) {
        $trimmedLine = trim($line);
        if (empty($trimmedLine)) {
            $currentArticle['content'] .= "\n";
            continue;
        }
        
        // Detectar capítulo/título
        if (preg_match($patterns['capitulo'], $trimmedLine, $m)) {
            $currentChapter = $trimmedLine;
            $currentArticle['content'] .= $line . "\n";
            continue;
        }
        
        // Detectar nuevo artículo
        if (preg_match($patterns['articulo'], $trimmedLine, $m)) {
            // Guardar artículo anterior si tiene contenido
            if (!empty(trim($currentArticle['content']))) {
                $articles[] = $currentArticle;
            }
            // Iniciar nuevo artículo
            $currentArticle = [
                'header' => trim($m[1]),
                'content' => ($currentChapter ? $currentChapter . "\n" : '') . $line . "\n",
                'type' => 'articulo',
                'chapter' => $currentChapter
            ];
            continue;
        }
        
        // Agregar línea al artículo actual
        $currentArticle['content'] .= $line . "\n";
    }
    
    // Guardar último artículo
    if (!empty(trim($currentArticle['content']))) {
        $articles[] = $currentArticle;
    }
    
    // Si no se detectaron artículos, usar el texto completo como un solo bloque
    if (empty($articles)) {
        $articles[] = [
            'header' => '',
            'content' => $text,
            'type' => 'documento',
            'chapter' => ''
        ];
    }
    
    // Agrupar artículos en chunks respetando límite de tamaño
    $chunks = [];
    $index = 0;
    $buffer = '';
    $bufferHeaders = [];
    
    foreach ($articles as $article) {
        $articleText = trim($article['content']);
        $articleLen = strlen($articleText);
        
        // Si un artículo solo es muy grande, dividirlo
        if ($articleLen > $maxChars * 1.5) {
            // Guardar buffer actual si tiene contenido
            if (!empty($buffer)) {
                $chunks[] = [
                    'text' => trim($buffer),
                    'index' => $index++,
                    'section' => implode(', ', array_unique($bufferHeaders))
                ];
                $buffer = '';
                $bufferHeaders = [];
            }
            
            // Dividir artículo grande en sub-chunks
            $subChunks = splitLargeArticle($articleText, $maxChars, $article['header']);
            foreach ($subChunks as $subChunk) {
                $chunks[] = [
                    'text' => $subChunk,
                    'index' => $index++,
                    'section' => $article['header']
                ];
            }
            continue;
        }
        
        // Si agregar este artículo excede el límite, crear nuevo chunk
        // Pero intentamos ser generosos: si el artículo es pequeño y el buffer no está gigante, lo metemos
        if (!empty($buffer) && (strlen($buffer) + $articleLen) > ($maxChars * 1.2)) {
            $chunks[] = [
                'text' => trim($buffer),
                'index' => $index++,
                'section' => implode(', ', array_unique($bufferHeaders))
            ];
            $buffer = '';
            $bufferHeaders = [];
        }
        
        // Agregar artículo al buffer
        $buffer .= $articleText . "\n\n";
        if (!empty($article['header'])) {
            $bufferHeaders[] = $article['header'];
        }
    }
    
    // Guardar último buffer
    if (!empty(trim($buffer))) {
        $chunks[] = [
            'text' => trim($buffer),
            'index' => $index++,
            'section' => implode(', ', array_unique($bufferHeaders))
        ];
    }
    
    return $chunks;
}

/**
 * Divide un artículo muy largo en sub-chunks por párrafos
 */
function splitLargeArticle(string $text, int $maxChars, string $header): array
{
    $paragraphs = preg_split('/\n\n+/', $text);
    $chunks = [];
    $buffer = $header ? $header . "\n" : '';
    
    foreach ($paragraphs as $para) {
        $para = trim($para);
        if (empty($para)) continue;
        
        if (!empty($buffer) && (strlen($buffer) + strlen($para)) > $maxChars) {
            $chunks[] = trim($buffer);
            $buffer = $header ? $header . "\n" : '';
        }
        
        $buffer .= $para . "\n\n";
    }
    
    if (!empty(trim($buffer))) {
        $chunks[] = trim($buffer);
    }
    
    return $chunks;
}
