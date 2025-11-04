<?php
namespace Repos;

use App\DB;
use PDO;

class DepartmentsRepo {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? DB::pdo();
    }

    public function listAll(): array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, slug FROM departments ORDER BY name ASC');
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }
}
