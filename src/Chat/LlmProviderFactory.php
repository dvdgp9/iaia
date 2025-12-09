<?php
namespace Chat;

use App\Env;
use App\Response;

class LlmProviderFactory
{
    /**
     * Crea un proveedor LLM.
     * 
     * Desde la migración a OpenRouter, todos los modelos se acceden a través
     * del gateway unificado. El parámetro $model permite especificar el modelo
     * en formato "provider/model" (ej: "google/gemini-2.5-flash", "qwen/qwen-plus").
     * 
     * @param string|null $provider Ignorado (siempre usa OpenRouter)
     * @param string|null $model Modelo a usar (formato: provider/model)
     */
    public static function create(?string $provider = null, ?string $model = null): LlmProvider
    {
        // Construir el contexto corporativo
        $contextBuilder = new ContextBuilder();
        $systemPrompt = $contextBuilder->buildSystemPrompt();

        // OpenRouter: gateway unificado para todos los modelos
        $client = $model !== null
            ? new OpenRouterClient(null, $model, $systemPrompt)
            : new OpenRouterClient(null, null, $systemPrompt);
        
        return new OpenRouterProvider($client, $contextBuilder);
    }
}
