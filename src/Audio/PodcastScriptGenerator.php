<?php
namespace Audio;

use Chat\OpenRouterClient;

/**
 * Genera guiones de podcast en formato diálogo a partir de contenido de artículos
 */
class PodcastScriptGenerator
{
    private OpenRouterClient $llmClient;
    private string $speaker1 = 'Ana';
    private string $speaker2 = 'Carlos';

    public function __construct(?OpenRouterClient $llmClient = null)
    {
        $this->llmClient = $llmClient ?? new OpenRouterClient(
            null,
            'google/gemini-3-flash-preview',
            null,
            0.7,
            8192
        );
    }

    /**
     * Genera un guion de podcast a partir del contenido de un artículo
     * 
     * @param string $content El contenido del artículo
     * @param string $title Título del artículo (opcional)
     * @param int $targetMinutes Duración objetivo en minutos
     * @return array ['success' => bool, 'script' => string, 'summary' => string, 'error' => string|null]
     */
    public function generate(string $content, string $title = '', int $targetMinutes = 10): array
    {
        $wordCount = str_word_count($content);
        
        // Estimar palabras del guion según duración objetivo
        // ~150 palabras por minuto hablado
        $targetWords = $targetMinutes * 150;
        
        // Ajustar si el artículo es muy corto
        if ($wordCount < 100) {
            return ['success' => false, 'error' => 'El artículo es demasiado corto para generar un podcast (mínimo ~100 palabras)'];
        }
        
        if ($wordCount < $targetWords / 2) {
            $targetMinutes = max(3, (int)($wordCount / 75)); // Mínimo 3 minutos
        }

        $prompt = $this->buildPrompt($content, $title, $targetMinutes);

        try {
            $response = $this->llmClient->generateText($prompt);
            
            // Parsear la respuesta
            $script = $this->parseScript($response);
            $summary = $this->extractSummary($response);

            if (empty($script)) {
                return ['success' => false, 'error' => 'No se pudo generar el guion del podcast'];
            }

            return [
                'success' => true,
                'script' => $script,
                'summary' => $summary,
                'speaker1' => $this->speaker1,
                'speaker2' => $this->speaker2,
                'estimated_duration' => $this->estimateDuration($script)
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Error generando guion: ' . $e->getMessage()];
        }
    }

    /**
     * Construye el prompt para generar el guion
     */
    private function buildPrompt(string $content, string $title, int $targetMinutes): string
    {
        $titleSection = $title ? "TÍTULO: {$title}\n\n" : '';
        
        return <<<PROMPT
Eres un guionista experto en podcasts informativos y divulgativos. Tu tarea es transformar el siguiente artículo en un guion de podcast conversacional entre dos presentadores: {$this->speaker1} (mujer) y {$this->speaker2} (hombre).

{$titleSection}CONTENIDO DEL ARTÍCULO:
---
{$content}
---

INSTRUCCIONES:

1. DURACIÓN OBJETIVO: Aproximadamente {$targetMinutes} minutos (~{$this->calculateTargetWords($targetMinutes)} palabras de diálogo)

2. ESTRUCTURA DEL PODCAST:
   - APERTURA: Saludo breve y presentación del tema (30 segundos)
   - DESARROLLO: Exploración del contenido principal con intercambio natural (80% del tiempo)
   - CIERRE: Resumen de puntos clave y despedida (30 segundos)

3. ESTILO:
   - Conversación natural y fluida, como dos amigos informados hablando
   - {$this->speaker1}: Suele introducir temas y hacer preguntas que guían la conversación
   - {$this->speaker2}: Aporta datos, contexto y reflexiones complementarias
   - Alternar turnos de forma natural (no mecánica)
   - Incluir pequeñas reacciones naturales ("Exacto", "Es interesante", "Fíjate que...")
   - Evitar jerga excesiva, hacer accesible el contenido

4. REGLAS CRÍTICAS:
   - SOLO usar información del artículo. NO inventar datos, cifras, fechas o nombres.
   - Si falta información, omitir o ser genérico, nunca inventar.
   - El diálogo debe ser en ESPAÑOL de España.
   - Cada intervención debe ser sustancial (2-4 frases), no monosílabos.

5. FORMATO DE SALIDA:
   Primero escribe un breve resumen del tema (1-2 líneas) entre marcadores:
   ---RESUMEN---
   [Resumen aquí]
   ---FIN_RESUMEN---

   Luego el guion con este formato exacto:
   ---GUION---
   {$this->speaker1}: [Texto de la intervención]
   {$this->speaker2}: [Texto de la intervención]
   {$this->speaker1}: [Texto de la intervención]
   ...
   ---FIN_GUION---

Genera el guion completo ahora.
PROMPT;
    }

    /**
     * Calcula palabras objetivo según minutos
     */
    private function calculateTargetWords(int $minutes): int
    {
        return $minutes * 150;
    }

    /**
     * Parsea el guion de la respuesta
     */
    private function parseScript(string $response): string
    {
        // Buscar el guion entre marcadores
        if (preg_match('/---GUION---\s*([\s\S]*?)\s*---FIN_GUION---/i', $response, $matches)) {
            return trim($matches[1]);
        }
        
        // Fallback: buscar líneas que empiecen con los nombres de los speakers
        $lines = [];
        $pattern = '/^(' . preg_quote($this->speaker1, '/') . '|' . preg_quote($this->speaker2, '/') . '):\s*(.+)$/m';
        
        if (preg_match_all($pattern, $response, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $lines[] = $match[0];
            }
            return implode("\n", $lines);
        }

        return '';
    }

    /**
     * Extrae el resumen de la respuesta
     */
    private function extractSummary(string $response): string
    {
        if (preg_match('/---RESUMEN---\s*([\s\S]*?)\s*---FIN_RESUMEN---/i', $response, $matches)) {
            return trim($matches[1]);
        }
        
        // Fallback: primera línea no vacía
        $lines = array_filter(explode("\n", $response), 'trim');
        return isset($lines[0]) ? substr(trim($lines[0]), 0, 200) : 'Podcast generado';
    }

    /**
     * Estima la duración del guion en segundos
     */
    private function estimateDuration(string $script): int
    {
        $wordCount = str_word_count($script);
        // ~2.5 palabras por segundo en habla natural
        return (int)($wordCount / 2.5);
    }

    /**
     * Getters para los nombres de los speakers
     */
    public function getSpeaker1(): string
    {
        return $this->speaker1;
    }

    public function getSpeaker2(): string
    {
        return $this->speaker2;
    }
}
