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
     * Prioriza archivos de instrucciones al inicio.
     * 
     * @return array<string>
     */
    private function getMarkdownFiles(): array
    {
        $files = glob($this->contextDir . '/*.md');
        if ($files === false) {
            return [];
        }

        // Ordenar alfabéticamente primero
        sort($files);
        
        // Archivos prioritarios que deben ir al inicio (en este orden)
        $priorityFiles = [
            $this->contextDir . '/system_prompt.md',
            $this->contextDir . '/faq_prompt.md',
        ];
        
        // Mover archivos prioritarios al inicio
        foreach (array_reverse($priorityFiles) as $priorityFile) {
            if (in_array($priorityFile, $files)) {
                $files = array_values(array_diff($files, [$priorityFile]));
                array_unshift($files, $priorityFile);
            }
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
