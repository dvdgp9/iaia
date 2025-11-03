<?php
namespace Auth;

class Passwords {
    public static function hash(string $password): string {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    public static function verify(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
}
