<?php
namespace Chat;

/**
 * Interfaz común para proveedores de LLM.
 *
 * Formato de mensajes: array<int, array{role:string, content:string, file?:array}>
 */
interface LlmProvider
{
    /**
     * @param array<int, array{role:string, content:string, file?:array}> $messages
     */
    public function generate(array $messages): string;

    /**
     * Obtiene el modelo usado por el proveedor
     */
    public function getModel(): string;
}
