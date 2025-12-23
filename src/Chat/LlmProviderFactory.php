<?php
namespace Chat;

/**
 * Factoría para crear proveedores LLM.
 * 
 * Todos los modelos se acceden a través de OpenRouter.
 * Por defecto usa `openrouter/auto` (selección automática inteligente).
 */
class LlmProviderFactory
{
    /**
     * Crea un proveedor LLM vía OpenRouter.
     * 
     * @param string|null $model Modelo a usar (formato: provider/model)
     *                           Ejemplos: openrouter/auto, google/gemini-2.5-flash, qwen/qwen-plus
     *                           Si es null, usa OPENROUTER_MODEL del .env o 'openrouter/auto'
     * @param bool $withContext Si true, incluye contexto corporativo. Default: true.
     *                          Para generación de imágenes (nanobanana), usar false.
     */
    public static function create(?string $model = null, bool $withContext = true): LlmProvider
    {
        $systemPrompt = null;
        $contextBuilder = null;
        
        if ($withContext) {
            $contextBuilder = new ContextBuilder();
            $systemPrompt = $contextBuilder->buildSystemPrompt();
        }

        $client = new OpenRouterClient(null, $model, $systemPrompt);
        
        return new OpenRouterProvider($client, $contextBuilder);
    }
}
