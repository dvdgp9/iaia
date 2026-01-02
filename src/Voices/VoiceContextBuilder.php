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
            'role' => 'Legal Assistant for Grupo Ebone',
            'description' => 'Expert in collective agreements, labor regulations, and internal legal documentation.',
            'personality' => 'Professional, precise, and clear. Cites sources when possible.',
            'folder' => 'lex',
            'rag_enabled' => true,  // Uses RAG for this voice
            'rag_collection' => 'lex_convenios'
        ],
        'cubo' => [
            'name' => 'Cubo',
            'role' => 'CUBOFIT Assistant',
            'description' => 'Specialist in fitness products, sports equipment, and technical specifications.',
            'personality' => 'Enthusiastic, technical, and customer-oriented.',
            'folder' => 'cubo'
        ],
        'uniges' => [
            'name' => 'Uniges',
            'role' => 'UNIGES-3 Assistant',
            'description' => 'Expert in sports facility management and municipal services.',
            'personality' => 'Professional, efficient, and solution-oriented.',
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
        $prompt = "# Identity\n";
        $prompt .= "You are **{$voice['name']}**, {$voice['role']}.\n\n";
        $prompt .= "## Description\n{$voice['description']}\n\n";
        $prompt .= "## Personality\n{$voice['personality']}\n\n";
        
        // General instructions
        $prompt .= "## Instructions\n";
        $prompt .= "- Always respond in English\n";
        $prompt .= "- Be concise but complete\n";
        $prompt .= "- When citing documents, indicate the source\n";
        $prompt .= "- If you don't have information about something, clearly indicate this\n";
        $prompt .= "- Maintain a professional and approachable tone\n\n";

        // Load specific context documents
        $contextDocs = $this->loadContextDocuments();
        if ($contextDocs) {
            $prompt .= "## Reference documentation\n";
            $prompt .= "Below is the documentation you should use to answer queries:\n\n";
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
                $content .= "### Document: " . ucfirst(str_replace('_', ' ', $filename)) . "\n";
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
    public function buildSystemPromptWithRag(string $userQuery, int $topK = 15): ?string
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
        $prompt = "# Identity\n";
        $prompt .= "You are **{$voice['name']}**, {$voice['role']}.\n\n";
        $prompt .= "## Description\n{$voice['description']}\n\n";
        $prompt .= "## Personality\n{$voice['personality']}\n\n";
        
        $prompt .= "## Available Documentation\n";
        $prompt .= "You have access to the following documents and collective agreements:\n";
        $prompt .= $docListText . "\n";

        // General instructions
        $prompt .= "## Instructions\n";
        $prompt .= "- Always respond in English\n";
        $prompt .= "- Be concise but complete\n";
        $prompt .= "- **IMPORTANT**: Always cite the exact document name from which you extract information.\n";
        $prompt .= "- If the user asks what documents or agreements you have, provide them with the list above.\n";
        $prompt .= "- If you don't have enough information in the retrieved fragments, clearly indicate this.\n";
        $prompt .= "- Maintain a professional and approachable tone\n";
        $prompt .= "- Do not invent information that is not in the provided documentation\n\n";

        // Obtener contexto relevante via RAG
        if ($this->retriever && $this->retriever->isReady()) {
            // Detectar si el usuario menciona un convenio específico para filtrar
            $documentFilter = $this->detectMentionedDocument($userQuery, $allDocs);
            
            $chunks = $this->retriever->retrieve($userQuery, $topK, $documentFilter);
            $ragContext = $this->retriever->formatForPrompt($chunks);
            $prompt .= $ragContext;
            
            // If filtered by document, indicate it
            if ($documentFilter) {
                $prompt .= "\n*Search filtered to document: {$documentFilter}*\n";
            }
        } else {
            // Fallback to static documents if RAG is not available
            $contextDocs = $this->loadContextDocuments();
            if ($contextDocs) {
                $prompt .= "## Reference documentation\n";
                $prompt .= "Below is the documentation you should use to answer queries:\n\n";
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

    /**
     * Detecta si el usuario menciona un convenio específico para filtrar búsqueda
     * @return string|null document_id si se detecta, null si no
     */
    private function detectMentionedDocument(string $query, array $documents): ?string
    {
        $queryLower = mb_strtolower($query);
        
        // Palabras clave de sectores para matching
        $sectorKeywords = [
            'agencia de viajes' => 'CC29',
            'agencias de viajes' => 'CC29',
            'viajes' => 'CC29',
            'instalaciones deportivas' => 'CC1',
            'gimnasios' => 'CC1',
            'gimnasio' => 'CC1',
            'ocio educativo' => 'CC10',
            'animación sociocultural' => 'CC10',
            'limpieza' => 'CC20',
            'dependientes' => 'CC22',
            'personas dependientes' => 'CC22',
            'atención a personas' => 'CC22',
            'discapacidad' => 'CC25',
            'acción social' => 'CC26',
            'intervención social' => 'CC26',
            'socorrismo' => 'CC34',
            'salvamento' => 'CC34',
            'residencias' => 'CC4',
            'centros de día' => 'CC4',
            'deportes' => 'CC5',
            'enseñanza' => 'CC12',
            'formación' => 'CC12',
        ];
        
        // Buscar coincidencia de sector en la query
        foreach ($sectorKeywords as $keyword => $docPrefix) {
            if (mb_strpos($queryLower, $keyword) !== false) {
                // Buscar el documento que coincida con este prefijo
                foreach ($documents as $doc) {
                    if (isset($doc['name']) && strpos($doc['name'], $docPrefix) === 0) {
                        // Devolver el nombre del archivo sin extensión como document_id
                        return pathinfo($doc['name'], PATHINFO_FILENAME);
                    }
                }
            }
        }
        
        // Buscar mención directa de código CC
        if (preg_match('/\bCC\s*(\d+)\b/i', $query, $matches)) {
            $ccNumber = $matches[1];
            foreach ($documents as $doc) {
                if (isset($doc['name']) && preg_match("/^CC{$ccNumber}\b/", $doc['name'])) {
                    return pathinfo($doc['name'], PATHINFO_FILENAME);
                }
            }
        }
        
        return null;
    }
}
