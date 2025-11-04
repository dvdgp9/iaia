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

    public function listByUser(int $userId, string $sort = 'updated_at'): array
    {
        $allowed = ['updated_at', 'created_at', 'title', 'favorite'];
        $orderBy = in_array($sort, $allowed) ? $sort : 'updated_at';
        
        if ($orderBy === 'favorite') {
            $sql = "SELECT id, title, status, is_favorite, created_at, updated_at FROM conversations WHERE user_id = ? ORDER BY is_favorite DESC, updated_at DESC";
        } else {
            $direction = $sort === 'title' ? 'ASC' : 'DESC';
            $sql = "SELECT id, title, status, is_favorite, created_at, updated_at FROM conversations WHERE user_id = ? ORDER BY {$orderBy} {$direction}";
        }
        
        $stmt = $this->pdo->prepare($sql);
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

    public function touch(int $conversationId): void
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare('UPDATE conversations SET updated_at = ? WHERE id = ?');
        $stmt->execute([$now, $conversationId]);
    }

    public function autoTitle(int $conversationId, string $firstMessage): void
    {
        // Solo si el título es el genérico "Nueva conversación"
        $conv = $this->pdo->prepare('SELECT title FROM conversations WHERE id = ? LIMIT 1');
        $conv->execute([$conversationId]);
        $row = $conv->fetch();
        if (!$row || $row['title'] !== 'Nueva conversación') {
            return;
        }
        // Tomar primeras 60 caracteres del mensaje
        $title = mb_substr($firstMessage, 0, 60);
        if (mb_strlen($firstMessage) > 60) {
            $title .= '...';
        }
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare('UPDATE conversations SET title = ?, updated_at = ? WHERE id = ?');
        $stmt->execute([$title, $now, $conversationId]);
    }

    public function toggleFavorite(int $userId, int $conversationId): bool
    {
        // Obtener estado actual y verificar ownership
        $stmt = $this->pdo->prepare('SELECT is_favorite FROM conversations WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$conversationId, $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            return false;
        }
        $newValue = $row['is_favorite'] ? 0 : 1;
        $now = date('Y-m-d H:i:s');
        $upd = $this->pdo->prepare('UPDATE conversations SET is_favorite = ?, updated_at = ? WHERE id = ? AND user_id = ?');
        $upd->execute([$newValue, $now, $conversationId, $userId]);
        return true;
    }
}
