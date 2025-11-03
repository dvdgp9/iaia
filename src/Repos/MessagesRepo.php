<?php
namespace Repos;

use App\DB;
use PDO;

class MessagesRepo {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? DB::pdo();
    }

    public function listByConversation(int $conversationId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, role, content, model, input_tokens, output_tokens, created_at FROM messages WHERE conversation_id = ? ORDER BY id ASC');
        $stmt->execute([$conversationId]);
        return $stmt->fetchAll() ?: [];
    }

    public function create(int $conversationId, ?int $userId, string $role, string $content, ?string $model = null, ?int $inputTokens = null, ?int $outputTokens = null): int
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare('INSERT INTO messages (conversation_id, user_id, role, content, model, input_tokens, output_tokens, created_at) VALUES (?,?,?,?,?,?,?,?)');
        $stmt->execute([$conversationId, $userId, $role, $content, $model, $inputTokens, $outputTokens, $now]);
        return (int)$this->pdo->lastInsertId();
    }
}
