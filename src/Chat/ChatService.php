<?php
namespace Chat;

class ChatService {
    private LlmProvider $provider;

    public function __construct(?LlmProvider $provider = null)
    {
        // Por defecto usamos la factoría, actualmente sólo Gemini
        $this->provider = $provider ?? LlmProviderFactory::create();
    }

    public function reply(string $userMessage): array
    {
        $answer = $this->provider->generate([
            [ 'role' => 'user', 'content' => $userMessage ],
        ]);
        return [ 'role' => 'assistant', 'content' => $answer ];
    }

    /**
     * @param array<int, array{role:string, content:string, file?:array}> $history
     * @param array|null $modalities Modalidades de salida (ej: ['image', 'text'])
     */
    public function replyWithHistory(array $history, ?array $modalities = null): array
    {
        $answer = $this->provider->generate($history, $modalities);
        return [ 'role' => 'assistant', 'content' => $answer ];
    }

    /**
     * Obtiene las imágenes generadas en la última respuesta
     */
    public function getLastImages(): ?array
    {
        if (method_exists($this->provider, 'getLastImages')) {
            return $this->provider->getLastImages();
        }
        return null;
    }
}
