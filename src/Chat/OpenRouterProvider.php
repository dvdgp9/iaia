<?php
namespace Chat;

class OpenRouterProvider implements LlmProvider
{
    private OpenRouterClient $client;

    public function __construct(?OpenRouterClient $client = null, ?ContextBuilder $contextBuilder = null)
    {
        // Construir el contexto corporativo
        $contextBuilder = $contextBuilder ?? new ContextBuilder();
        $systemPrompt = $contextBuilder->buildSystemPrompt();

        // Si no se pasó un cliente, crearlo con el contexto
        if ($client === null) {
            $this->client = new OpenRouterClient(null, null, $systemPrompt);
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

    /**
     * Obtiene el modelo usado por el cliente
     */
    public function getModel(): string
    {
        return $this->client->getModel();
    }
}
