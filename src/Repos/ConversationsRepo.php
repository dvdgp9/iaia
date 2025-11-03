<?php
namespace Repos;

use App\DB;
use PDO;

class ConversationsRepo {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? DB::pdo();
    }

    public function listByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, title, status, created_at, updated_at FROM conversations WHERE user_id = ? ORDER BY updated_at DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll() ?: [];
    }

    public function findByIdForUser(int $conversationId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, title, status, created_at, updated_at FROM conversations WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$conversationId, $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(int $userId, ?string $title = null): int
    {
        $now = date('Y-m-d H:i:s');
        $title = $title && trim($title) !== '' ? trim($title) : 'Nueva conversación';
        $stmt = $this->pdo->prepare('INSERT INTO conversations (user_id, title, status, created_at, updated_at) VALUES (?,?,"active",?,?)');
        $stmt->execute([$userId, $title, $now, $now]);
        return (int)$this->pdo->lastInsertId();
    }

    public function rename(int $userId, int $conversationId, string $title): void
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare('UPDATE conversations SET title = ?, updated_at = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([trim($title), $now, $conversationId, $userId]);
    }

    public function delete(int $userId, int $conversationId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM conversations WHERE id = ? AND user_id = ?');
        $stmt->execute([$conversationId, $userId]);
    }
}
