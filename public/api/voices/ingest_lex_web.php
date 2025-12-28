<?php
/**
 * Script temporal para ejecutar la ingesta RAG desde el navegador
 * ¡ELIMINAR DESPUÉS DE USAR!
 * 
 * Este script incluye directamente la lógica de ingesta para evitar
 * problemas con exec() y rutas de PHP en servidores compartidos.
 */

// Aumentar límites para procesamiento largo
set_time_limit(600); // 10 minutos
ini_set('memory_limit', '512M');

// Flush output en tiempo real
ob_implicit_flush(true);
if (ob_get_level()) ob_end_flush();

require_once __DIR__ . '/../../../src/App/bootstrap.php';
require_once __DIR__ . '/../../../src/Rag/QdrantClient.php';
require_once __DIR__ . '/../../../src/Rag/EmbeddingService.php';

use App\Env;
use Rag\QdrantClient;
use Rag\EmbeddingService;

// Configuración
define('COLLECTION_NAME', 'lex_convenios');
define('VECTOR_SIZE', 4096);
define('CHUNK_SIZE', 500);
define('CHUNK_OVERLAP', 50);
define('BATCH_SIZE', 5); // Reducido para evitar timeouts

$conveniosPath = __DIR__ . '/../../../docs/context/voices/lex/convenios';

echo "<html><head><title>Ingesta RAG</title></head><body>";
echo "<h1>🔄 Ingesta RAG para Lex</h1>";
echo "<pre style='background: #1e1e1e; color: #d4d4d4; padding: 20px; border-radius: 8px; white-space: pre-wrap;'>";

function output($msg) {
    echo htmlspecialchars($msg) . "\n";
    flush();
}

try {
    // Verificar API key
    $openrouterKey = Env::get('OPENROUTER_API_KEY');
    if (!$openrouterKey) {
        throw new Exception("OPENROUTER_API_KEY no configurada en .env");
    }
    output("✓ API Key encontrada");

    // Verificar configuración Qdrant
    $qdrantHost = Env::get('QDRANT_HOST', 'localhost');
    $qdrantPort = (int) Env::get('QDRANT_PORT', 6333);
    output("→ Conectando a Qdrant en {$qdrantHost}:{$qdrantPort}...");

    $qdrant = new QdrantClient($qdrantHost, $qdrantPort);
    $embeddings = new EmbeddingService($openrouterKey);

    // Verificar conexión
    if (!$qdrant->health()) {
        throw new Exception("No se puede conectar con Qdrant. ¿Está el contenedor corriendo?");
    }
    output("✓ Conexión con Qdrant OK");

    // Crear colección si no existe
    if (!$qdrant->collectionExists(COLLECTION_NAME)) {
        output("→ Creando colección '" . COLLECTION_NAME . "'...");
        $qdrant->createCollection(COLLECTION_NAME, VECTOR_SIZE, 'Cosine');
        output("✓ Colección creada");
    } else {
        output("✓ Colección ya existe");
    }

    // Buscar archivos
    $files = glob($conveniosPath . '/*.pdf');
    $txtFiles = glob($conveniosPath . '/*.txt');
    $mdFiles = glob($conveniosPath . '/*.md');
    $files = array_merge($files, $txtFiles, $mdFiles);
    
    // Filtrar README.md
    $files = array_filter($files, fn($f) => basename($f) !== 'README.md');

    if (empty($files)) {
        throw new Exception("No se encontraron archivos en: {$conveniosPath}");
    }
    output("\n📁 Archivos encontrados: " . count($files));

    $totalChunks = 0;
    $pointId = 1;
    $errors = [];

    foreach ($files as $file) {
        $filename = basename($file);
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        
        output("\n--- Procesando: {$filename} ---");
        
        // Extraer texto
        $text = '';
        if ($extension === 'pdf') {
            // Intentar con pdftotext primero
            $tempFile = sys_get_temp_dir() . '/lex_pdf_' . uniqid() . '.txt';
            @exec("pdftotext -layout -enc UTF-8 " . escapeshellarg($file) . " " . escapeshellarg($tempFile) . " 2>&1", $pdfOutput, $pdfReturn);
            
            if ($pdfReturn === 0 && file_exists($tempFile)) {
                $text = file_get_contents($tempFile);
                @unlink($tempFile);
                output("  Extraído con pdftotext: " . strlen($text) . " chars");
            } else {
                // Fallback: intentar leer como texto plano (algunos PDFs simples)
                $rawContent = @file_get_contents($file);
                // Extraer texto entre streams de PDF (muy básico)
                if (preg_match_all('/stream\s*(.*?)\s*endstream/s', $rawContent, $matches)) {
                    foreach ($matches[1] as $stream) {
                        $decoded = @gzuncompress($stream);
                        if ($decoded) {
                            $text .= preg_replace('/[^\x20-\x7E\xA0-\xFF\n\r\t]/', ' ', $decoded);
                        }
                    }
                }
                if (strlen($text) < 100) {
                    output("  ⚠ No se pudo extraer texto del PDF (pdftotext no disponible)");
                    $errors[] = "PDF sin texto extraíble: {$filename}";
                    continue;
                }
                output("  Extraído con fallback: " . strlen($text) . " chars");
            }
        } else {
            $text = file_get_contents($file);
            output("  Leído directamente: " . strlen($text) . " chars");
        }

        if (strlen(trim($text)) < 50) {
            output("  ⚠ Archivo vacío o muy corto, saltando...");
            continue;
        }

        // Chunking
        $chunks = chunkText($text, CHUNK_SIZE, CHUNK_OVERLAP, $filename);
        output("  Chunks generados: " . count($chunks));

        // Procesar en batches
        $batches = array_chunk($chunks, BATCH_SIZE);
        
        foreach ($batches as $batchIndex => $batch) {
            output("  Batch " . ($batchIndex + 1) . "/" . count($batches) . "...");
            
            try {
                $batchTexts = array_column($batch, 'text');
                $vectors = $embeddings->embedBatch($batchTexts);
                
                $points = [];
                foreach ($batch as $i => $chunk) {
                    $points[] = [
                        'id' => $pointId++,
                        'vector' => $vectors[$i],
                        'payload' => [
                            'text' => $chunk['text'],
                            'document_id' => pathinfo($filename, PATHINFO_FILENAME),
                            'document_name' => $filename,
                            'chunk_index' => $chunk['index'],
                            'section' => $chunk['section'] ?? ''
                        ]
                    ];
                }
                
                $qdrant->upsertPoints(COLLECTION_NAME, $points);
                $totalChunks += count($batch);
                output("    ✓ " . count($batch) . " chunks indexados");
                
            } catch (Exception $e) {
                output("    ✗ Error: " . $e->getMessage());
                $errors[] = "Error en {$filename}: " . $e->getMessage();
            }
            
            // Pequeña pausa para no saturar la API
            usleep(200000); // 200ms
        }
    }

    output("\n" . str_repeat("=", 50));
    output("✅ INGESTA COMPLETADA");
    output("Total chunks indexados: {$totalChunks}");
    
    $count = $qdrant->countPoints(COLLECTION_NAME);
    output("Puntos en colección: {$count}");
    
    if (!empty($errors)) {
        output("\n⚠ Errores encontrados:");
        foreach ($errors as $err) {
            output("  - " . $err);
        }
    }

} catch (Exception $e) {
    output("\n❌ ERROR FATAL: " . $e->getMessage());
}

echo "</pre>";
echo "<p><strong>Recuerda borrar este archivo después de usarlo.</strong></p>";
echo "</body></html>";

// === Funciones auxiliares ===

function chunkText(string $text, int $targetTokens, int $overlap, string $filename): array
{
    $charsPerToken = 4;
    $targetChars = $targetTokens * $charsPerToken;
    $overlapChars = $overlap * $charsPerToken;
    
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    
    $chunks = [];
    $start = 0;
    $index = 0;
    $length = strlen($text);
    
    while ($start < $length) {
        $end = min($start + $targetChars, $length);
        
        if ($end < $length) {
            $lastPeriod = strrpos(substr($text, $start, $end - $start), '. ');
            $lastNewline = strrpos(substr($text, $start, $end - $start), "\n");
            $cutPoint = max($lastPeriod, $lastNewline);
            
            if ($cutPoint !== false && $cutPoint > ($targetChars * 0.5)) {
                $end = $start + $cutPoint + 1;
            }
        }
        
        $chunkText = trim(substr($text, $start, $end - $start));
        
        if (!empty($chunkText) && strlen($chunkText) > 20) {
            $section = '';
            if (preg_match('/^(Artículo\s+\d+|CAPÍTULO\s+[IVXLC]+|Sección\s+\d+)[.:]\s*(.+?)(?:\n|$)/i', $chunkText, $matches)) {
                $section = trim($matches[1] . ': ' . $matches[2]);
            }
            
            $chunks[] = [
                'text' => $chunkText,
                'index' => $index++,
                'section' => $section
            ];
        }
        
        $start = $end - $overlapChars;
        if ($start >= $length - $overlapChars) break;
    }
    
    return $chunks;
}
