<?php
namespace Chat;

/**
 * Interfaz común para cualquier proveedor de LLM.
 *
 * Recibe un historial de mensajes en el mismo formato que ya usa GeminiClient::generateWithMessages:
 * array<int, array{role:string, content:string, file?:array}>
 */
interface LlmProvider
{
    /**
     * @param array<int, array{role:string, content:string, file?:array}> $messages
     */
    public function generate(array $messages): string;
}
