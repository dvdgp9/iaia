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

        // Determinar dominio (opcional) para abarcar subdominios en prod
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (!$host && $appUrl) {
            $parsed = parse_url($appUrl);
            $host = $parsed['host'] ?? '';
        }
        $cookieDomain = '';
        if ($host) {
            // Extraer dominio base (e.g., ebonia.es) si hay subdominio
            $parts = explode('.', $host);
            if (count($parts) >= 2) {
                $cookieDomain = $parts[count($parts)-2] . '.' . $parts[count($parts)-1];
            }
        }

        // Evitar cache para respuestas con sesión
        session_cache_limiter('nocache');

        // Fijar path global
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            // Fijar dominio base si se pudo calcular (permite www. y subdominios)
            'domain' => $cookieDomain ?: '',
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
