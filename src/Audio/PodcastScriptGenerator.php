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
Eres un guionista experto en podcasts de tecnología y divulgación con un estilo narrativo único, similar a "Deep Dive" o charlas informales entre expertos. Tu tarea es transformar el siguiente artículo en un guion de podcast extremadamente natural, dinámico y conversacional entre {$this->speaker1} (mujer) y {$this->speaker2} (hombre).

{$titleSection}CONTENIDO DEL ARTÍCULO:
---
{$content}
---

INSTRUCCIONES DE ESTILO Y TONO (CRÍTICO):

1. **TONO CONVERSACIONAL REAL, NO LEÍDO**:
   - Olvida que es un texto escrito. Debe sonar a dos colegas expertos charlando en una cafetería.
   - Usa expresiones coloquiales y naturales: "Ojo", "El pan de cada día", "La madre del cordero", "Te explota la cabeza", "Es un marrón".
   - Usa muletillas naturales con moderación: "A ver...", "Pues...", "Claro, es que...", "¿No?".
   - Evita el lenguaje enciclopédico o robótico. No digas "El artículo afirma que...", di "Lo que me ha flipado es que...".

2. **DINÁMICA DE INTERACCIÓN**:
   - **¡NO hagas entrevista!** No es Q&A. Es una charla bidireccional.
   - Ambos saben del tema, se complementan, se quitan la palabra, se dan la razón.
   - Usa **intervenciones cortas y rápidas**. Evita monólogos largos.
   - Haz que se interrumpan o terminen las frases del otro.
   - Incluye "backchanneling" (reacciones breves mientras el otro habla): "Claro", "Totalmente", "Uff, ya ves", "Exacto".

3. **STORYTELLING Y EMOCIÓN**:
   - Empieza con una anécdota, una situación vivida o un "hook" emocional, no con "Hoy vamos a hablar de...".
   - Ejemplo de inicio: "El otro día me pasó algo que me hizo acordarme de este tema..." o "A ver, confiesa, ¿cuántas veces te has peleado con...?"
   - Conecta los conceptos técnicos con dolores reales del día a día (frustración, cansancio, alegría).
   - Usa metáforas visuales potentes (ej: "buscar una aguja en un pajar", "es como jugar a detectives con una mano atada").

4. **ESTRUCTURA**:
   - **Inicio (Hook)**: Anécdota o planteamiento del problema desde la experiencia personal.
   - **Nudo (Análisis)**: Desgranan el contenido del artículo como si lo estuvieran descubriendo o debatiendo. Se sorprenden mutuamente.
   - **Desenlace (Conclusión)**: Reflexión final abierta o "takeaway" práctico, despedida informal.

5. **REGLAS TÉCNICAS**:
   - Duración: ~{$targetMinutes} minutos (~{$this->calculateTargetWords($targetMinutes)} palabras).
   - Idioma: ESPAÑOL de España (Castellano).
   - Solo usa información del artículo, pero adáptala a este estilo. Si falta info, usa generalidades lógicas, no inventes datos.

FORMATO DE SALIDA:

Primero escribe un breve resumen del tema (1-2 líneas) entre marcadores:
---RESUMEN---
[Resumen aquí]
---FIN_RESUMEN---

Luego el guion con este formato exacto:
---GUION---
{$this->speaker1}: [Texto...]
{$this->speaker2}: [Texto...]
...
---FIN_GUION---

Genera el guion ahora. Hazlo brillante, humano y enganchante.
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
