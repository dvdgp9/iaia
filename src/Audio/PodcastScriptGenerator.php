<?php
namespace Audio;

use Chat\OpenRouterClient;

/**
 * Generates podcast scripts in dialogue format from article content
 */
class PodcastScriptGenerator
{
    private OpenRouterClient $llmClient;
    private string $speaker1 = 'Iris';
    private string $speaker2 = 'Bruno';

    public function __construct(?OpenRouterClient $llmClient = null)
    {
        $this->llmClient = $llmClient ?? new OpenRouterClient(
            null,
            'google/gemini-3-flash-preview',
            null,
            0.8,  // Mayor creatividad
            16384 // Más tokens para guiones largos
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
            return ['success' => false, 'error' => 'The article is too short to generate a podcast (minimum ~100 words)'];
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
                return ['success' => false, 'error' => 'Could not generate the podcast script'];
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
            return ['success' => false, 'error' => 'Error generating script: ' . $e->getMessage()];
        }
    }

    /**
     * Construye el prompt para generar el guion
     */
    private function buildPrompt(string $content, string $title, int $targetMinutes): string
    {
        $titleSection = $title ? "ARTICLE TITLE: {$title}\n\n" : '';
        $targetWords = $this->calculateTargetWords($targetMinutes);
        
        return <<<PROMPT
You are an expert scriptwriter for high-quality educational podcasts, in the style of NotebookLM or "Deep Dive". Your job is to transform technical articles into natural, deep, and entertaining conversations.

{$titleSection}CONTENT TO TRANSFORM:
---
{$content}
---

═══════════════════════════════════════════════════════════════
OBJECTIVE: Generate a {$targetMinutes}-minute podcast script (~{$targetWords} words)
between {$this->speaker1} (woman, main host) and {$this->speaker2} (man, expert co-host).
═══════════════════════════════════════════════════════════════

## CONVERSATIONAL STYLE (VERY IMPORTANT)

The podcast should sound like TWO EXPERT FRIENDS chatting at a bar, not like hosts reading a script. MUST include:

1. **SHORT INTERJECTED REACTIONS** - Essential for naturalness:
   - "Exactly.", "Yes.", "Right.", "Of course.", "Totally.", "That's it."
   - "Wow.", "Geez.", "Oh my.", "Check this out.", "Ah, I see."
   - "Really?", "Just one?", "Wait, wait..."
   - These reactions should appear FREQUENTLY (every 2-3 long interventions)

2. **FICTIONAL PERSONAL ANECDOTES** - Make the content human:
   - "I remember once I spent hours on this problem..."
   - "Something similar happened to me the other day..."
   - "A colleague of mine always says that..."

3. **VIVID METAPHORS AND ANALOGIES**:
   - "It's like having the bricks but not the mortar"
   - "It's the difference between searching for a needle in a haystack and asking Google"
   - "It's like the plumber: they install the pipes but don't decide what water flows through"

4. **COLLOQUIAL EXPRESSIONS**:
   - "the crux of the matter", "bread and butter", "the million-dollar question"
   - "doesn't beat around the bush", "let's get to the point", "connecting the dots"
   - "the elephant in the room", "mind-blowing", "that's a whole different story"

5. **RHETORICAL QUESTIONS AND PAUSES**:
   - "And what's the problem? Well..."
   - "Think about it for a moment: if you have X, how are you going to...?"
   - "The million-dollar question is..."

6. **NATURAL INTERRUPTIONS**:
   - One can cut off the other to add something
   - "Wait, this is important..."
   - "Sorry to interrupt, but..."

## STRUCTURE

1. **OPENING (30-45 sec)**: Greeting (the podcast is called "The Pulse of IAIA") + provocative hook about the topic.
2. **DEVELOPMENT (85% of time)**: 
   - Explore each concept IN DEPTH
   - Not just explain WHAT, but WHY it matters
   - Give concrete examples and real scenarios
   - Ask questions between them that go deeper
3. **CLOSING (30-45 sec)**: Reflection that invites thinking + warm farewell

## ROLES

- **{$this->speaker1}**: Introduces topics, asks intelligent questions, summarizes key points, connects ideas
- **{$this->speaker2}**: Provides technical depth, tells anecdotes, uses analogies, responds with detail

## CRITICAL RULES

- ONLY information from the article. NEVER invent data, figures, dates, or real names.
- Vary the length of interventions: some long (3-5 sentences), others very short (1 word)
- The script should be MUCH longer than a summary: develop, don't summarize

## EXAMPLE OF DESIRED STYLE

{$this->speaker1}: Today we're going to analyze a topic that's almost provocative: why our current systems are failing.
{$this->speaker2}: And look, it's not that they fail with bad intentions. It's something worse: the idea is that they're fundamentally inadequate for today's problems. I remember once I spent hours trying to find a bug, only to realize the problem came from a place I didn't even know existed.
{$this->speaker1}: That story is the bread and butter for so many people.
{$this->speaker2}: Exactly.
{$this->speaker1}: And the underlying problem is that we're using 20th-century tools for 21st-century problems.
{$this->speaker2}: Yes, very simple in theory.
{$this->speaker1}: But today a single click can trigger a chain reaction that touches twenty different systems. And our tools are still, in essence, the same as before.
{$this->speaker2}: The elephant in the room, basically. The author describes it brilliantly: they say it's like playing detective with one hand tied behind your back.
{$this->speaker1}: I love that analogy.

## OUTPUT FORMAT

First a brief summary (1-2 lines):
---SUMMARY---
[Summary here]
---END_SUMMARY---

Then the complete script:
---SCRIPT---
{$this->speaker1}: [Text]
{$this->speaker2}: [Text]
...
---END_SCRIPT---

GENERATE THE COMPLETE SCRIPT NOW. Remember: it should be long, deep, natural, and entertaining.
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
        // Search for script between markers (English)
        if (preg_match('/---SCRIPT---\s*([\s\S]*?)\s*---END_SCRIPT---/i', $response, $matches)) {
            return trim($matches[1]);
        }
        // Fallback: Spanish markers
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
        // English markers
        if (preg_match('/---SUMMARY---\s*([\s\S]*?)\s*---END_SUMMARY---/i', $response, $matches)) {
            $sum = $this->normalizeSummary($matches[1]);
            if ($this->isValidSummary($sum)) return $sum;
        }

        if (preg_match('/---SUMMARY---\s*([\s\S]*?)\s*---SCRIPT---/i', $response, $matches)) {
            $sum = $this->normalizeSummary($matches[1]);
            if ($this->isValidSummary($sum)) return $sum;
        }

        // Fallback: Spanish markers
        if (preg_match('/---RESUMEN---\s*([\s\S]*?)\s*---FIN_RESUMEN---/i', $response, $matches)) {
            $sum = $this->normalizeSummary($matches[1]);
            if ($this->isValidSummary($sum)) return $sum;
        }

        if (preg_match('/---RESUMEN---\s*([\s\S]*?)\s*---GUION---/i', $response, $matches)) {
            $sum = $this->normalizeSummary($matches[1]);
            if ($this->isValidSummary($sum)) return $sum;
        }

        $lines = array_values(array_filter(array_map('trim', explode("\n", $response))));
        foreach ($lines as $l) {
            if ($l === '' || stripos($l, '---') !== false) continue;
            if (preg_match('/^(' . preg_quote($this->speaker1, '/') . '|' . preg_quote($this->speaker2, '/') . '):/i', $l)) continue;
            $sum = $this->normalizeSummary($l);
            if ($this->isValidSummary($sum)) return $sum;
        }

        return 'Podcast generated';
    }

    private function normalizeSummary(string $text): string
    {
        $t = trim($text);
        $t = preg_replace('/\s+/', ' ', $t);
        return mb_substr($t, 0, 200);
    }

    private function isValidSummary(string $text): bool
    {
        if ($text === '' ) return false;
        $lower = mb_strtolower($text);
        if (strpos($lower, '---summary---') !== false) return false;
        if (strpos($lower, '---resumen---') !== false) return false;
        if (strpos($lower, '[summary here]') !== false) return false;
        if (strpos($lower, '[resumen aquí]') !== false) return false;
        return true;
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
