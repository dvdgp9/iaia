<?php
namespace App;

class Env {
    public static function load(string $path): void {
        if (!is_file($path)) return;
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) continue;
            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            $key = trim($key);
            $value = trim($value);
            if ($key === '') continue;
            // Remove optional quotes
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }
            $_ENV[$key] = $value;
            putenv($key.'='.$value);
        }
    }

    public static function get(string $key, ?string $default = null): ?string {
        $val = $_ENV[$key] ?? getenv($key);
        return $val === false ? $default : ($val ?? $default);
    }
}
