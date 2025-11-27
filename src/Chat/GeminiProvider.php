<?php
namespace Chat;

class GeminiProvider implements LlmProvider
{
    private GeminiClient $client;

    public function __construct(?GeminiClient $client = null)
    {
        $this->client = $client ?? new GeminiClient();
    }

    /**
     * @param array<int, array{role:string, content:string, file?:array}> $messages
     */
    public function generate(array $messages): string
    {
        return $this->client->generateWithMessages($messages);
    }
}
