<?php
namespace Chat;

use App\Env;
use App\Response;

class LlmProviderFactory
{
    public static function create(?string $provider = null, ?string $model = null): LlmProvider
    {
        $providerName = strtolower($provider ?? (Env::get('LLM_PROVIDER') ?? 'gemini'));

        switch ($providerName) {
            case 'gemini':
                // Permitimos sobreescribir el modelo por parámetro, manteniendo el valor por defecto de GeminiClient
                $client = $model !== null
                    ? new GeminiClient(null, $model)
                    : new GeminiClient();
                return new GeminiProvider($client);

            default:
                Response::error('llm_provider_not_supported', 'Proveedor LLM no soportado: ' . $providerName, 400);
        }
    }
}
