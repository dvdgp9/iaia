<?php
namespace Voices;

/**
 * Construye el contexto especializado para cada voz
 * Lee archivos de docs/context/voices/{voice_id}/ para cargar el conocimiento específico
 */
class VoiceContextBuilder
{
    private string $voiceId;
    private string $contextPath;
    
    // Definición de voces disponibles
    private static array $voices = [
        'lex' => [
            'name' => 'Lex',
            'role' => 'Asistente Legal del Grupo Ebone',
            'description' => 'Experto en convenios colectivos, normativas laborales y documentación legal interna.',
            'personality' => 'Profesional, preciso y claro. Cita fuentes cuando sea posible.',
            'folder' => 'lex'
        ],
        'cubo' => [
            'name' => 'Cubo',
            'role' => 'Asistente de CUBOFIT',
            'description' => 'Especialista en productos fitness, equipamiento deportivo y especificaciones técnicas.',
            'personality' => 'Entusiasta, técnico y orientado al cliente.',
            'folder' => 'cubo'
        ],
        'uniges' => [
            'name' => 'Uniges',
            'role' => 'Asistente de UNIGES-3',
            'description' => 'Experto en gestión de instalaciones deportivas y servicios municipales.',
            'personality' => 'Profesional, eficiente y orientado a soluciones.',
            'folder' => 'uniges'
        ]
    ];

    public function __construct(string $voiceId)
    {
        $this->voiceId = $voiceId;
        $this->contextPath = dirname(dirname(__DIR__)) . '/docs/context/voices/' . $voiceId;
    }

    /**
     * Verifica si la voz existe
     */
    public function voiceExists(): bool
    {
        return isset(self::$voices[$this->voiceId]);
    }

    /**
     * Obtiene la información de la voz
     */
    public function getVoiceInfo(): ?array
    {
        return self::$voices[$this->voiceId] ?? null;
    }

    /**
     * Construye el system prompt completo para la voz
     */
    public function buildSystemPrompt(): ?string
    {
        if (!$this->voiceExists()) {
            return null;
        }

        $voice = self::$voices[$this->voiceId];
        
        // System prompt base
        $prompt = "# Identidad\n";
        $prompt .= "Eres **{$voice['name']}**, {$voice['role']}.\n\n";
        $prompt .= "## Descripción\n{$voice['description']}\n\n";
        $prompt .= "## Personalidad\n{$voice['personality']}\n\n";
        
        // Instrucciones generales
        $prompt .= "## Instrucciones\n";
        $prompt .= "- Responde siempre en español\n";
        $prompt .= "- Sé conciso pero completo\n";
        $prompt .= "- Cuando cites documentos, indica la fuente\n";
        $prompt .= "- Si no tienes información sobre algo, indícalo claramente\n";
        $prompt .= "- Mantén un tono profesional y accesible\n\n";

        // Cargar documentos de contexto específicos
        $contextDocs = $this->loadContextDocuments();
        if ($contextDocs) {
            $prompt .= "## Documentación de referencia\n";
            $prompt .= "A continuación tienes la documentación que debes usar para responder consultas:\n\n";
            $prompt .= $contextDocs;
        }

        return $prompt;
    }

    /**
     * Carga todos los documentos .md de la carpeta de contexto de la voz
     */
    private function loadContextDocuments(): string
    {
        if (!is_dir($this->contextPath)) {
            return '';
        }

        $content = '';
        $files = glob($this->contextPath . '/*.md');
        
        foreach ($files as $file) {
            $filename = basename($file, '.md');
            $fileContent = file_get_contents($file);
            
            if ($fileContent) {
                $content .= "### Documento: " . ucfirst(str_replace('_', ' ', $filename)) . "\n";
                $content .= $fileContent . "\n\n";
            }
        }

        return $content;
    }

    /**
     * Lista los documentos disponibles para esta voz
     */
    public function listDocuments(): array
    {
        if (!is_dir($this->contextPath)) {
            return [];
        }

        $docs = [];
        $files = glob($this->contextPath . '/*.md');
        
        foreach ($files as $file) {
            $filename = basename($file, '.md');
            $docs[] = [
                'id' => $filename,
                'name' => ucfirst(str_replace('_', ' ', $filename)),
                'path' => $file,
                'size' => filesize($file)
            ];
        }

        return $docs;
    }

    /**
     * Obtiene todas las voces disponibles
     */
    public static function getAllVoices(): array
    {
        return self::$voices;
    }
}
