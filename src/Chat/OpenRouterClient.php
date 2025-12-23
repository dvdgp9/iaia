<?php
namespace Chat;

use App\Env;
use App\Response;

/**
 * Cliente para OpenRouter (https://openrouter.ai)
 * 
 * OpenRouter es un gateway que provee acceso unificado a múltiples LLMs
 * (Gemini, GPT, Claude, Qwen, etc.) con una API compatible con OpenAI.
 * 
 * Modelos se especifican como "provider/model":
 * - openrouter/auto (selección automática inteligente)
 * - google/gemini-2.5-flash
 * - qwen/qwen-plus
 * - openai/gpt-4o
 * - anthropic/claude-3.5-sonnet
 */
class OpenRouterClient {
    private string $apiKey;
    private string $model;
    private ?string $usedModel = null; // Modelo real usado (para openrouter/auto)
    private ?string $systemInstruction;
    private ?float $temperature;
    private ?int $maxTokens;
    private ?array $lastImages = null; // Imágenes generadas en última respuesta
    private string $baseUrl = 'https://openrouter.ai/api/v1/chat/completions';

    public function __construct(
        ?string $apiKey = null, 
        ?string $model = null, 
        ?string $systemInstruction = null,
        ?float $temperature = null,
        ?int $maxTokens = null
    ) {
        $this->apiKey = $apiKey ?? (Env::get('OPENROUTER_API_KEY') ?? '');
        $this->model = $model ?? (Env::get('OPENROUTER_MODEL') ?? 'openrouter/auto');
        $this->systemInstruction = $systemInstruction;
        $this->temperature = $temperature;
        $this->maxTokens = $maxTokens;
    }

    public function generateText(string $prompt): string
    {
        return $this->generateWithMessages([
            [ 'role' => 'user', 'content' => $prompt ]
        ]);
    }

    /**
     * @param array<int, array{role:string, content:string, file?:array}> $messages
     * @param array|null $modalities Modalidades de salida (ej: ['image', 'text'] para generación de imágenes)
     */
    public function generateWithMessages(array $messages, ?array $modalities = null): string
    {
        if (!$this->apiKey) {
            Response::error('openrouter_api_key_missing', 'Falta OPENROUTER_API_KEY en .env', 500);
        }

        // Construir mensajes en formato OpenAI
        $messagesPayload = [];
        
        // Agregar system instruction si existe
        if ($this->systemInstruction !== null && $this->systemInstruction !== '') {
            $messagesPayload[] = [
                'role' => 'system',
                'content' => $this->systemInstruction
            ];
        }
        
        // Agregar mensajes del historial
        $hasPdf = false;
        foreach ($messages as $m) {
            $content = [];
            
            // Agregar archivo si existe (para mensajes de usuario)
            if (isset($m['file']) && $m['role'] === 'user') {
                $file = $m['file'];
                // OpenRouter/OpenAI soporta imágenes en formato base64
                if (str_starts_with($file['mime_type'], 'image/')) {
                    $content[] = [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => 'data:' . $file['mime_type'] . ';base64,' . $file['data']
                        ]
                    ];
                } elseif ($file['mime_type'] === 'application/pdf') {
                    // Para PDFs, usar bloque type:file + file-parser plugin
                    $hasPdf = true;
                    $filename = $file['name'] ?? 'document.pdf';
                    $content[] = [
                        'type' => 'file',
                        'file' => [
                            'filename' => $filename,
                            'file_data' => 'data:application/pdf;base64,' . $file['data']
                        ]
                    ];
                }
            }
            
            // Agregar texto
            if (!empty($m['content'])) {
                if (!empty($content)) {
                    // Si hay archivo, usar formato array de contenido
                    $content[] = [
                        'type' => 'text',
                        'text' => (string)$m['content']
                    ];
                } else {
                    // Si no hay archivo, usar string directo
                    $content = (string)$m['content'];
                }
            }
            
            $messagesPayload[] = [
                'role' => $m['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => $content
            ];
        }

        $payload = [
            'model' => $this->model,
            'messages' => $messagesPayload
        ];
        // Si hay PDF, añadir plugin file-parser con engine pdf-text por defecto
        if ($hasPdf) {
            $payload['plugins'] = [
                [
                    'id' => 'file-parser',
                    'pdf' => [ 'engine' => 'pdf-text' ]
                ]
            ];
        }
        // Si hay modalities (ej: generación de imágenes), añadirlas
        if ($modalities !== null && !empty($modalities)) {
            $payload['modalities'] = $modalities;
        }
        
        // Añadir parámetros opcionales
        if ($this->temperature !== null) {
            $payload['temperature'] = $this->temperature;
        }
        if ($this->maxTokens !== null) {
            $payload['max_tokens'] = $this->maxTokens;
        }

        $ch = curl_init($this->baseUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
                'HTTP-Referer: ' . (Env::get('APP_URL') ?? 'https://ebonia.es'),
                'X-Title: Ebonia'
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT => 120, // Más tiempo para generación de imágenes
        ]);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $err) {
            Response::error('openrouter_request_failed', 'Fallo al contactar con OpenRouter: ' . $err, 502);
        }

        $data = json_decode($raw, true);
        if ($status < 200 || $status >= 300) {
            $msg = $data['error']['message'] ?? $data['message'] ?? ('HTTP '.$status);
            Response::error('openrouter_bad_response', 'Error de OpenRouter: ' . $msg, 502);
        }

        $message = $data['choices'][0]['message'] ?? [];
        $text = $message['content'] ?? '';
        
        // Capturar imágenes generadas si existen
        $this->lastImages = null;
        if (isset($message['images']) && is_array($message['images'])) {
            $this->lastImages = $message['images'];
        }
        
        // Para generación de imágenes, el texto puede estar vacío pero tener imágenes
        if ($text === '' && empty($this->lastImages)) {
            Response::error('openrouter_empty', 'Respuesta vacía de OpenRouter', 502);
        }
        
        // Capturar el modelo real usado (importante para openrouter/auto)
        $this->usedModel = $data['model'] ?? $this->model;
        
        return $text;
    }

    /**
     * Obtiene el modelo usado en la última generación.
     * Si se usó openrouter/auto, devuelve el modelo real seleccionado.
     */
    public function getModel(): string
    {
        return $this->usedModel ?? $this->model;
    }

    /**
     * Obtiene el modelo configurado (antes de auto-selección)
     */
    public function getConfiguredModel(): string
    {
        return $this->model;
    }

    /**
     * Obtiene las imágenes generadas en la última respuesta (si las hay)
     * @return array|null Array de imágenes con formato [{type: 'image_url', image_url: {url: 'data:...'}}]
     */
    public function getLastImages(): ?array
    {
        return $this->lastImages;
    }
}
