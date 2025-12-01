<?php
namespace Chat;

class GeminiProvider implements LlmProvider
{
    private GeminiClient $client;

    public function __construct(?GeminiClient $client = null, ?ContextBuilder $contextBuilder = null)
    {
        // Construir el contexto corporativo
        $contextBuilder = $contextBuilder ?? new ContextBuilder();
        $systemPrompt = $contextBuilder->buildSystemPrompt();

        // Si no se pasó un cliente, crearlo con el contexto
        if ($client === null) {
            $this->client = new GeminiClient(null, null, $systemPrompt);
        } else {
            $this->client = $client;
        }
    }

    /**
     * @param array<int, array{role:string, content:string, file?:array}> $messages
     */
    public function generate(array $messages): string
    {
        return $this->client->generateWithMessages($messages);
    }
}
