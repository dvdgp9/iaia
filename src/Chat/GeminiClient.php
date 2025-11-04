<?php
namespace Chat;

use App\Env;
use App\Response;

class GeminiClient {
    private string $apiKey;
    private string $model;

    public function __construct(?string $apiKey = null, ?string $model = null)
    {
        $this->apiKey = $apiKey ?? (Env::get('GEMINI_API_KEY') ?? '');
        $this->model = $model ?? (Env::get('GEMINI_MODEL') ?? 'gemini-2.5-flash');
    }

    public function generateText(string $prompt): string
    {
        return $this->generateWithMessages([
            [ 'role' => 'user', 'content' => $prompt ]
        ]);
    }

    /**
     * @param array<int, array{role:string, content:string}> $messages
     */
    public function generateWithMessages(array $messages): string
    {
        if (!$this->apiKey) {
            Response::error('gemini_api_key_missing', 'Falta GEMINI_API_KEY en .env', 500);
        }

        $url = sprintf('https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            urlencode($this->model), urlencode($this->apiKey)
        );

        // Mapear roles internos ('user'|'assistant') a Gemini ('user'|'model')
        $contents = [];
        foreach ($messages as $m) {
            $role = $m['role'] === 'assistant' ? 'model' : 'user';
            $contents[] = [
                'role' => $role,
                'parts' => [ [ 'text' => (string)$m['content'] ] ]
            ];
        }

        $payload = [ 'contents' => $contents ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [ 'Content-Type: application/json; charset=utf-8' ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT => 30,
        ]);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $err) {
            Response::error('gemini_request_failed', 'Fallo al contactar con Gemini', 502);
        }

        $data = json_decode($raw, true);
        if ($status < 200 || $status >= 300) {
            $msg = $data['error']['message'] ?? ('HTTP '.$status);
            Response::error('gemini_bad_response', $msg, 502);
        }

        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if ($text === '') {
            Response::error('gemini_empty', 'Respuesta vacía de Gemini', 502);
        }
        return $text;
    }
}
