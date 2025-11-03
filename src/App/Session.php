<?php
namespace App;

class Session {
    public static function start(): void {
        if (session_status() === PHP_SESSION_ACTIVE) return;
        // Detectar HTTPS correctamente detrás de proxy/CDN
        $xfp = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
        $appUrl = $_ENV['APP_URL'] ?? getenv('APP_URL') ?: '';
        $schemeFromEnv = '';
        if ($appUrl) {
            $parsed = parse_url($appUrl);
            $schemeFromEnv = $parsed['scheme'] ?? '';
        }
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || strtolower($xfp) === 'https'
            || strtolower($schemeFromEnv) === 'https';

        // Fijar path global
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            // No fijamos dominio para dejar que el navegador lo ajuste al host actual
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_name('ebonia_session');
        session_start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public static function requireCsrf(): void {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$token || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            Response::error('csrf_invalid', 'CSRF token inválido o ausente', 403);
        }
    }

    public static function user(): ?array {
        return $_SESSION['user'] ?? null;
    }

    public static function login(array $user): void {
        $_SESSION['user'] = $user;
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'] ?? '/', $params['domain'] ?? '', $params['secure'] ?? false, $params['httponly'] ?? true);
        }
        session_destroy();
    }
}
