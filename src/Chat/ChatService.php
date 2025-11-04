<?php
namespace Chat;

class ChatService {
    private GeminiClient $client;

    public function __construct(?GeminiClient $client = null)
    {
        $this->client = $client ?? new GeminiClient();
    }

    public function reply(string $userMessage): array
    {
        $answer = $this->client->generateText($userMessage);
        return [ 'role' => 'assistant', 'content' => $answer ];
    }

    /**
     * @param array<int, array{role:string, content:string}> $history
     */
    public function replyWithHistory(array $history): array
    {
        $answer = $this->client->generateWithMessages($history);
        return [ 'role' => 'assistant', 'content' => $answer ];
    }
}
