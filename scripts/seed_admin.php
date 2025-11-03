<?php
require_once __DIR__ . '/../src/App/bootstrap.php';
require_once __DIR__ . '/../src/Auth/Passwords.php';

use App\DB;
use Auth\Passwords;

$pdo = DB::pdo();
$adminEmail = getenv('ADMIN_EMAIL') ?: null;
$adminPass = getenv('ADMIN_PASSWORD') ?: null;
if (!$adminEmail || !$adminPass) {
    fwrite(STDERR, "ADMIN_EMAIL/ADMIN_PASSWORD no definidos en .env\n");
    exit(1);
}

$now = date('Y-m-d H:i:s');
$hash = Passwords::hash($adminPass);

// Crear usuario si no existe
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$adminEmail]);
    $existing = $stmt->fetchColumn();
    if ($existing) {
        echo "= Usuario admin ya existe: $adminEmail (id=$existing)\n";
    } else {
        $ins = $pdo->prepare('INSERT INTO users (company_id, department_id, email, password_hash, first_name, last_name, is_superadmin, status, created_at, updated_at) VALUES (NULL, NULL, ?, ?, ?, ?, 1, "active", ?, ?)');
        $ins->execute([$adminEmail, $hash, 'Admin', 'Ebonia', $now, $now]);
        $userId = (int)$pdo->lastInsertId();
        echo "+ Usuario admin creado: $adminEmail (id=$userId)\n";

        // Asegurar rol admin
        // Obtener role_id admin
        $roleId = (int)($pdo->query("SELECT id FROM roles WHERE slug='admin' LIMIT 1")->fetchColumn());
        if ($roleId) {
            $pdo->prepare('INSERT IGNORE INTO user_roles (user_id, role_id, created_at) VALUES (?,?,?)')
                ->execute([$userId, $roleId, $now]);
            echo "+ Rol admin asignado\n";
        }
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, "! Error: " . $e->getMessage() . "\n");
    exit(1);
}

echo "Seed admin completado.\n";
