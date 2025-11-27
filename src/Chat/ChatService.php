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
     */
    public function replyWithHistory(array $history): array
    {
        $answer = $this->provider->generate($history);
        return [ 'role' => 'assistant', 'content' => $answer ];
    }
}
