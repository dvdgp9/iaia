<?php
namespace App;

class Response {
    public static function json($data, int $status = 200): void {
        // Limpiar cualquier output previo
        if (ob_get_level() > 0) {
            ob_clean();
        }
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function error(string $code, string $message, int $status = 400): void {
        self::json([ 'error' => [ 'code' => $code, 'message' => $message ] ], $status);
    }
}
