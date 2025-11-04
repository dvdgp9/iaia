<?php
namespace Repos;

use App\DB;
use PDO;

class UsersRepo {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? DB::pdo();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, email, password_hash, first_name, last_name, status, is_superadmin FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updateLastLoginAt(int $userId): void
    {
        $this->pdo->prepare('UPDATE users SET last_login_at = NOW(), updated_at = NOW() WHERE id = ?')->execute([$userId]);
    }

    public function getRoles(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT r.slug FROM roles r INNER JOIN user_roles ur ON ur.role_id = r.id WHERE ur.user_id = ?');
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        return array_values($rows);
    }

    public function updateProfile(int $userId, string $firstName, string $lastName): void
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare('UPDATE users SET first_name = ?, last_name = ?, updated_at = ? WHERE id = ?');
        $stmt->execute([$firstName, $lastName, $now, $userId]);
    }

    public function updatePassword(int $userId, string $newPasswordHash): void
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare('UPDATE users SET password_hash = ?, updated_at = ? WHERE id = ?');
        $stmt->execute([$newPasswordHash, $now, $userId]);
    }

    public function getActivityStats(int $userId): array
    {
        // Total de conversaciones
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM conversations WHERE user_id = ?');
        $stmt->execute([$userId]);
        $totalConversations = (int)$stmt->fetchColumn();

        // Conversaciones esta semana
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM conversations WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
        $stmt->execute([$userId]);
        $conversationsThisWeek = (int)$stmt->fetchColumn();

        // Total de mensajes enviados
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM messages WHERE user_id = ? AND role = "user"');
        $stmt->execute([$userId]);
        $totalMessages = (int)$stmt->fetchColumn();

        // Mensajes esta semana
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM messages WHERE user_id = ? AND role = "user" AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
        $stmt->execute([$userId]);
        $messagesThisWeek = (int)$stmt->fetchColumn();

        return [
            'total_conversations' => $totalConversations,
            'conversations_this_week' => $conversationsThisWeek,
            'total_messages' => $totalMessages,
            'messages_this_week' => $messagesThisWeek
        ];
    }
}
