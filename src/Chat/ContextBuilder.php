<?php
namespace Chat;

/**
 * Construye el contexto corporativo para inyectar en los LLM.
 * 
 * Lee todos los archivos markdown de docs/context/ y los combina
 * en un único prompt de sistema. Este contexto es el mismo para
 * todos los proveedores (Gemini, OpenAI, etc.), cada uno lo usa
 * en su formato nativo.
 */
class ContextBuilder
{
    private string $contextDir;

    public function __construct(?string $contextDir = null)
    {
        $this->contextDir = $contextDir ?? dirname(dirname(__DIR__)) . '/docs/context';
    }

    /**
     * Construye el prompt de sistema completo con todo el contexto corporativo.
     */
    public function buildSystemPrompt(): string
    {
        if (!is_dir($this->contextDir)) {
            return $this->getDefaultPrompt();
        }

        $content = '';
        $files = $this->getMarkdownFiles();

        if (empty($files)) {
            return $this->getDefaultPrompt();
        }

        foreach ($files as $file) {
            $fileContent = file_get_contents($file);
            if ($fileContent !== false) {
                $content .= $fileContent . "\n\n---\n\n";
            }
        }

        return trim($content);
    }

    /**
     * Obtiene todos los archivos .md del directorio de contexto, ordenados alfabéticamente.
     * 
     * @return array<string>
     */
    private function getMarkdownFiles(): array
    {
        $files = glob($this->contextDir . '/*.md');
        if ($files === false) {
            return [];
        }

        // Ordenar para tener un orden predecible (system_prompt.md primero si existe)
        sort($files);
        
        // Priorizar system_prompt.md al inicio si existe
        $systemPrompt = $this->contextDir . '/system_prompt.md';
        if (in_array($systemPrompt, $files)) {
            $files = array_diff($files, [$systemPrompt]);
            array_unshift($files, $systemPrompt);
        }

        return $files;
    }

    /**
     * Prompt por defecto si no hay archivos de contexto.
     */
    private function getDefaultPrompt(): string
    {
        return "Eres Ebonia, un asistente de IA corporativa profesional y útil.";
    }
}
