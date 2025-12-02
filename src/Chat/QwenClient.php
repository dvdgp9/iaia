<?php
namespace Chat;

use App\Env;
use App\Response;

class QwenClient {
    private string $apiKey;
    private string $model;
    private ?string $systemInstruction;
    private ?float $temperature;
    private ?int $maxTokens;

    public function __construct(
        ?string $apiKey = null, 
        ?string $model = null, 
        ?string $systemInstruction = null,
        ?float $temperature = null,
        ?int $maxTokens = null
    ) {
        $this->apiKey = $apiKey ?? (Env::get('QWEN_API_KEY') ?? '');
        $this->model = $model ?? (Env::get('QWEN_MODEL') ?? 'qwen-plus');
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
     */
    public function generateWithMessages(array $messages): string
    {
        if (!$this->apiKey) {
            Response::error('qwen_api_key_missing', 'Falta QWEN_API_KEY en .env', 500);
        }

        // Qwen usa el endpoint compatible con OpenAI
        $url = 'https://dashscope-intl.aliyuncs.com/compatible-mode/v1/chat/completions';


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
        foreach ($messages as $m) {
            $content = [];
            
            // Agregar archivo si existe (para mensajes de usuario)
            if (isset($m['file']) && $m['role'] === 'user') {
                $file = $m['file'];
                // Qwen soporta imágenes en formato base64
                if (str_starts_with($file['mime_type'], 'image/')) {
                    $content[] = [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => 'data:' . $file['mime_type'] . ';base64,' . $file['data']
                        ]
                    ];
                }
            }
            
            // Agregar texto si no está vacío
            if (!empty($m['content'])) {
                if (isset($m['file'])) {
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
        
        // Añadir parámetros opcionales
        if ($this->temperature !== null) {
            $payload['temperature'] = $this->temperature;
        }
        if ($this->maxTokens !== null) {
            $payload['max_tokens'] = $this->maxTokens;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT => 30,
        ]);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $err) {
            Response::error('qwen_request_failed', 'Fallo al contactar con Qwen: ' . $err, 502);
        }

        $data = json_decode($raw, true);
        if ($status < 200 || $status >= 300) {
            $msg = $data['error']['message'] ?? $data['message'] ?? ('HTTP '.$status);
            Response::error('qwen_bad_response', 'Error de Qwen: ' . $msg, 502);
        }

        $text = $data['choices'][0]['message']['content'] ?? '';
        if ($text === '') {
            Response::error('qwen_empty', 'Respuesta vacía de Qwen', 502);
        }
        return $text;
    }
}
