<?php
namespace Auth;

use App\Env;
use App\Response;
use App\Session;

class AuthService {
    public static function login(string $email, string $password): array {
        $adminEmail = Env::get('ADMIN_EMAIL');
        $adminPass = Env::get('ADMIN_PASSWORD');
        if ($adminEmail && $adminPass && strtolower($email) === strtolower($adminEmail) && $password === $adminPass) {
            $user = [
                'id' => 1,
                'email' => $adminEmail,
                'first_name' => 'Admin',
                'last_name' => 'Ebonia',
                'roles' => ['admin']
            ];
            Session::login($user);
            return $user;
        }
        Response::error('invalid_credentials', 'Credenciales inválidas', 401);
        return [];
    }

    public static function requireAuth(): array {
        $user = Session::user();
        if (!$user) {
            Response::error('unauthorized', 'No autenticado', 401);
        }
        return $user;
    }
}
