<?php
namespace Audio;

/**
 * Extractor de contenido de artículos desde URLs y archivos
 */
class ContentExtractor
{
    /**
     * Extrae el contenido de texto de una URL
     */
    public function extractFromUrl(string $url): array
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return ['success' => false, 'error' => 'URL no válida'];
        }

        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: Mozilla/5.0 (compatible; EbonIA/1.0)\r\n",
                'timeout' => 30
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);

        $html = @file_get_contents($url, false, $ctx);

        if ($html === false) {
            return ['success' => false, 'error' => 'No se pudo acceder a la URL'];
        }

        // Extraer título
        $title = '';
        if (preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $matches)) {
            $title = html_entity_decode(trim($matches[1]), ENT_QUOTES, 'UTF-8');
        }

        // Extraer contenido principal
        $content = $this->extractMainContent($html);

        if (empty($content)) {
            return ['success' => false, 'error' => 'No se pudo extraer contenido del artículo'];
        }

        return [
            'success' => true,
            'title' => $title,
            'content' => $content,
            'source' => parse_url($url, PHP_URL_HOST),
            'url' => $url,
            'word_count' => str_word_count($content)
        ];
    }

    /**
     * Extrae el contenido de un archivo PDF (base64)
     */
    public function extractFromPdf(string $base64Data): array
    {
        $pdfData = base64_decode($base64Data);
        
        if ($pdfData === false) {
            return ['success' => false, 'error' => 'Datos PDF inválidos'];
        }

        // Guardar temporalmente el PDF
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf_');
        file_put_contents($tempFile, $pdfData);

        try {
            // Intentar con pdftotext (si está disponible)
            $text = $this->extractWithPdftotext($tempFile);
            
            if (empty($text)) {
                // Fallback: extracción básica
                $text = $this->extractPdfBasic($pdfData);
            }

            unlink($tempFile);

            if (empty($text)) {
                return ['success' => false, 'error' => 'No se pudo extraer texto del PDF'];
            }

            return [
                'success' => true,
                'title' => 'Documento PDF',
                'content' => $text,
                'source' => 'PDF upload',
                'word_count' => str_word_count($text)
            ];
        } catch (\Exception $e) {
            @unlink($tempFile);
            return ['success' => false, 'error' => 'Error procesando PDF: ' . $e->getMessage()];
        }
    }

    /**
     * Extrae el contenido de un archivo de texto plano
     */
    public function extractFromText(string $text): array
    {
        $text = trim($text);
        
        if (empty($text)) {
            return ['success' => false, 'error' => 'El texto está vacío'];
        }

        // Intentar extraer un título de la primera línea
        $lines = explode("\n", $text);
        $title = trim($lines[0]);
        if (strlen($title) > 100) {
            $title = substr($title, 0, 97) . '...';
        }

        return [
            'success' => true,
            'title' => $title,
            'content' => $text,
            'source' => 'Texto directo',
            'word_count' => str_word_count($text)
        ];
    }

    /**
     * Extrae el contenido principal de HTML limpiando boilerplate
     */
    private function extractMainContent(string $html): string
    {
        // Eliminar scripts, styles, nav, header, footer, aside
        $html = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html);
        $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);
        $html = preg_replace('/<nav[^>]*>.*?<\/nav>/is', '', $html);
        $html = preg_replace('/<header[^>]*>.*?<\/header>/is', '', $html);
        $html = preg_replace('/<footer[^>]*>.*?<\/footer>/is', '', $html);
        $html = preg_replace('/<aside[^>]*>.*?<\/aside>/is', '', $html);
        $html = preg_replace('/<form[^>]*>.*?<\/form>/is', '', $html);
        $html = preg_replace('/<!--.*?-->/s', '', $html);

        // Buscar contenido en article, main, o div con clases típicas de contenido
        $contentPatterns = [
            '/<article[^>]*>(.*?)<\/article>/is',
            '/<main[^>]*>(.*?)<\/main>/is',
            '/<div[^>]*class="[^"]*(?:content|article|post|entry|story)[^"]*"[^>]*>(.*?)<\/div>/is',
        ];

        $content = '';
        foreach ($contentPatterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $content = $matches[1];
                break;
            }
        }

        // Si no encontramos contenido específico, usar el body
        if (empty($content)) {
            if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches)) {
                $content = $matches[1];
            } else {
                $content = $html;
            }
        }

        // Convertir párrafos y headers a texto con saltos de línea
        $content = preg_replace('/<\/p>/i', "\n\n", $content);
        $content = preg_replace('/<\/h[1-6]>/i', "\n\n", $content);
        $content = preg_replace('/<br\s*\/?>/i', "\n", $content);
        $content = preg_replace('/<li[^>]*>/i', "\n• ", $content);

        // Eliminar todas las etiquetas HTML restantes
        $content = strip_tags($content);

        // Decodificar entidades HTML
        $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');

        // Limpiar espacios múltiples y líneas vacías
        $content = preg_replace('/[ \t]+/', ' ', $content);
        $content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content);
        $content = trim($content);

        return $content;
    }

    /**
     * Extrae texto de PDF usando pdftotext (poppler-utils)
     */
    private function extractWithPdftotext(string $filePath): string
    {
        $output = [];
        $returnVar = 0;
        
        exec("which pdftotext 2>/dev/null", $output, $returnVar);
        
        if ($returnVar !== 0) {
            return '';
        }

        $textFile = $filePath . '.txt';
        exec("pdftotext -enc UTF-8 " . escapeshellarg($filePath) . " " . escapeshellarg($textFile) . " 2>/dev/null", $output, $returnVar);
        
        if ($returnVar !== 0 || !file_exists($textFile)) {
            return '';
        }

        $text = file_get_contents($textFile);
        @unlink($textFile);

        return trim($text);
    }

    /**
     * Extracción básica de texto de PDF (sin dependencias externas)
     */
    private function extractPdfBasic(string $pdfData): string
    {
        // Buscar streams de texto en el PDF
        $text = '';
        
        // Buscar texto entre BT y ET (Begin Text / End Text)
        if (preg_match_all('/BT\s*(.*?)\s*ET/s', $pdfData, $matches)) {
            foreach ($matches[1] as $block) {
                // Extraer strings entre paréntesis
                if (preg_match_all('/\(([^)]+)\)/', $block, $stringMatches)) {
                    $text .= implode(' ', $stringMatches[1]) . ' ';
                }
                // Extraer strings hexadecimales
                if (preg_match_all('/<([0-9A-Fa-f]+)>/', $block, $hexMatches)) {
                    foreach ($hexMatches[1] as $hex) {
                        $text .= hex2bin($hex) . ' ';
                    }
                }
            }
        }

        // Limpiar
        $text = preg_replace('/[^\x20-\x7E\xA0-\xFF\n]/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }
}
