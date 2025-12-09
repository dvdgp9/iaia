<?php
namespace Chat;

use App\Env;
use App\Response;

class LlmProviderFactory
{
    public static function create(?string $provider = null, ?string $model = null): LlmProvider
    {
        // Default: OpenRouter (gateway unificado para todos los modelos)
        $providerName = strtolower($provider ?? (Env::get('LLM_PROVIDER') ?? 'openrouter'));

        // Construir el contexto corporativo (común a todos los proveedores)
        $contextBuilder = new ContextBuilder();
        $systemPrompt = $contextBuilder->buildSystemPrompt();

        switch ($providerName) {
            case 'openrouter':
                // OpenRouter: gateway unificado (Gemini, Qwen, GPT, Claude, etc.)
                $client = $model !== null
                    ? new OpenRouterClient(null, $model, $systemPrompt)
                    : new OpenRouterClient(null, null, $systemPrompt);
                return new OpenRouterProvider($client, $contextBuilder);

            case 'gemini':
                // Gemini directo (legacy, usar OpenRouter preferiblemente)
                $client = $model !== null
                    ? new GeminiClient(null, $model, $systemPrompt)
                    : new GeminiClient(null, null, $systemPrompt);
                return new GeminiProvider($client, $contextBuilder);

            case 'qwen':
                // Qwen directo (legacy, usar OpenRouter preferiblemente)
                $client = $model !== null
                    ? new QwenClient(null, $model, $systemPrompt)
                    : new QwenClient(null, null, $systemPrompt);
                return new QwenProvider($client, $contextBuilder);

            default:
                Response::error('llm_provider_not_supported', 'Proveedor LLM no soportado: ' . $providerName, 400);
        }
    }
}
