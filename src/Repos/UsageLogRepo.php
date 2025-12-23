<?php
namespace Repos;

use App\DB;
use PDO;

class UsageLogRepo {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? DB::pdo();
    }

    /**
     * Registra una acción de uso
     */
    public function log(int $userId, string $actionType, int $count = 1, ?array $metadata = null): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO usage_log (user_id, action_type, count, metadata, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ');
        $stmt->execute([
            $userId,
            $actionType,
            $count,
            $metadata ? json_encode($metadata) : null
        ]);
    }

    /**
     * Obtiene estadísticas globales por tipo de acción, opcionalmente filtradas por fecha
     */
    public function getGlobalStats(?int $days = null): array
    {
        $dateFilter = $days ? 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)' : '';
        $params = $days ? [$days] : [];
        
        $stmt = $this->pdo->prepare("
            SELECT 
                action_type,
                SUM(count) as total
            FROM usage_log
            $dateFilter
            GROUP BY action_type
        ");
        $stmt->execute($params);
        
        $results = [];
        foreach ($stmt->fetchAll() as $row) {
            $results[$row['action_type']] = (int)$row['total'];
        }
        return $results;
    }

    /**
     * Obtiene estadísticas por usuario, opcionalmente filtradas por fecha
     */
    public function getStatsByUser(?int $days = null): array
    {
        $dateFilter = $days ? 'AND ul.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)' : '';
        $params = $days ? [$days] : [];
        
        $stmt = $this->pdo->prepare("
            SELECT 
                u.id as user_id,
                u.first_name,
                u.last_name,
                u.email,
                u.last_login_at,
                COALESCE(SUM(CASE WHEN ul.action_type = 'conversation' THEN ul.count END), 0) as conversations,
                COALESCE(SUM(CASE WHEN ul.action_type = 'message' THEN ul.count END), 0) as messages,
                COALESCE(SUM(CASE WHEN ul.action_type = 'image' THEN ul.count END), 0) as images,
                COALESCE(SUM(CASE WHEN ul.action_type = 'gesture' THEN ul.count END), 0) as gestures,
                COALESCE(SUM(CASE WHEN ul.action_type = 'voice' THEN ul.count END), 0) as voices
            FROM users u
            LEFT JOIN usage_log ul ON ul.user_id = u.id $dateFilter
            GROUP BY u.id
            ORDER BY messages DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
