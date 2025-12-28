<?php
namespace Voices;

use Rag\QdrantClient;
use Rag\EmbeddingService;
use Rag\LexRetriever;

/**
 * Construye el contexto especializado para cada voz
 * Lee archivos de docs/context/voices/{voice_id}/ para cargar el conocimiento específico
 * Para voces con RAG habilitado, usa búsqueda semántica en lugar de cargar todo el contexto
 */
class VoiceContextBuilder
{
    private string $voiceId;
    private string $contextPath;
    private ?LexRetriever $retriever = null;
    
    // Definición de voces disponibles
    private static array $voices = [
        'lex' => [
            'name' => 'Lex',
            'role' => 'Asistente Legal del Grupo Ebone',
            'description' => 'Experto en convenios colectivos, normativas laborales y documentación legal interna.',
            'personality' => 'Profesional, preciso y claro. Cita fuentes cuando sea posible.',
            'folder' => 'lex',
            'rag_enabled' => true,  // Usa RAG para esta voz
            'rag_collection' => 'lex_convenios'
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
     * Lista los documentos disponibles para esta voz (incluyendo convenios RAG)
     */
    public function listDocuments(): array
    {
        $docs = [];
        
        // 1. Documentos estáticos (.md)
        if (is_dir($this->contextPath)) {
            $files = glob($this->contextPath . '/*.md');
            foreach ($files as $file) {
                $filename = basename($file, '.md');
                $docs[] = [
                    'id' => $filename,
                    'name' => ucfirst(str_replace('_', ' ', $filename)),
                    'type' => 'static',
                    'path' => $file,
                    'size' => filesize($file)
                ];
            }
        }

        // 2. Documentos RAG (convenios en PDF/TXT/MD dentro de la subcarpeta convenios)
        $ragPath = $this->contextPath . '/convenios';
        if (is_dir($ragPath)) {
            $files = glob($ragPath . '/*.{pdf,txt,md}', GLOB_BRACE);
            foreach ($files as $file) {
                $filename = basename($file);
                if ($filename === 'README.md') continue;
                
                $docs[] = [
                    'id' => 'rag_' . md5($filename),
                    'name' => $filename,
                    'type' => 'rag',
                    'path' => $file,
                    'size' => filesize($file)
                ];
            }
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

    /**
     * Verifica si la voz tiene RAG habilitado
     */
    public function hasRagEnabled(): bool
    {
        $voice = self::$voices[$this->voiceId] ?? null;
        return $voice && ($voice['rag_enabled'] ?? false);
    }

    /**
     * Inicializa el retriever RAG para esta voz
     */
    public function initRetriever(string $openaiKey, string $qdrantHost = 'localhost', int $qdrantPort = 6333): void
    {
        if (!$this->hasRagEnabled()) {
            return;
        }

        $voice = self::$voices[$this->voiceId];
        $collection = $voice['rag_collection'] ?? 'default';

        $qdrant = new QdrantClient($qdrantHost, $qdrantPort);
        $embeddings = new EmbeddingService($openaiKey);
        $this->retriever = new LexRetriever($qdrant, $embeddings, $collection);
    }

    /**
     * Obtiene el retriever RAG
     */
    public function getRetriever(): ?LexRetriever
    {
        return $this->retriever;
    }

    /**
     * Construye el system prompt con contexto RAG
     * Usa búsqueda semántica para encontrar los chunks relevantes
     */
    public function buildSystemPromptWithRag(string $userQuery, int $topK = 5): ?string
    {
        if (!$this->voiceExists()) {
            return null;
        }

        $voice = self::$voices[$this->voiceId];
        
        // Obtener lista de todos los documentos para que la IA sepa qué tiene
        $allDocs = $this->listDocuments();
        $docListText = "";
        foreach ($allDocs as $doc) {
            $docListText .= "- " . $doc['name'] . "\n";
        }

        // System prompt base
        $prompt = "# Identidad\n";
        $prompt .= "Eres **{$voice['name']}**, {$voice['role']}.\n\n";
        $prompt .= "## Descripción\n{$voice['description']}\n\n";
        $prompt .= "## Personalidad\n{$voice['personality']}\n\n";
        
        $prompt .= "## Documentación Disponible\n";
        $prompt .= "Tienes acceso a los siguientes documentos y convenios colectivos:\n";
        $prompt .= $docListText . "\n";

        // Instrucciones generales
        $prompt .= "## Instrucciones\n";
        $prompt .= "- Responde siempre en español\n";
        $prompt .= "- Sé conciso pero completo\n";
        $prompt .= "- **IMPORTANTE**: Cita siempre el nombre del documento exacto del que extraes la información.\n";
        $prompt .= "- Si el usuario te pregunta qué documentos o convenios tienes, proporciónale la lista de arriba.\n";
        $prompt .= "- Si no tienes información suficiente en los fragmentos recuperados, indícalo claramente.\n";
        $prompt .= "- Mantén un tono profesional y accesible\n";
        $prompt .= "- No inventes información que no esté en la documentación proporcionada\n\n";

        // Obtener contexto relevante via RAG
        if ($this->retriever && $this->retriever->isReady()) {
            $chunks = $this->retriever->retrieve($userQuery, $topK);
            $ragContext = $this->retriever->formatForPrompt($chunks);
            $prompt .= $ragContext;
        } else {
            // Fallback a documentos estáticos si RAG no está disponible
            $contextDocs = $this->loadContextDocuments();
            if ($contextDocs) {
                $prompt .= "## Documentación de referencia\n";
                $prompt .= "A continuación tienes la documentación que debes usar para responder consultas:\n\n";
                $prompt .= $contextDocs;
            }
        }

        return $prompt;
    }

    /**
     * Verifica si el RAG está listo (colección existe y tiene datos)
     */
    public function isRagReady(): bool
    {
        return $this->retriever && $this->retriever->isReady();
    }

    /**
     * Obtiene estadísticas del RAG
     */
    public function getRagStats(): array
    {
        if (!$this->retriever) {
            return ['enabled' => false];
        }

        $stats = $this->retriever->getStats();
        $stats['enabled'] = true;
        return $stats;
    }
}
