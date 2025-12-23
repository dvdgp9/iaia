-- Tabla de auditoría para tracking permanente de uso
-- Los registros aquí NO se borran nunca, sirven para estadísticas históricas

CREATE TABLE IF NOT EXISTS usage_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    action_type ENUM('message', 'image', 'gesture', 'voice', 'conversation') NOT NULL,
    count INT UNSIGNED NOT NULL DEFAULT 1,
    metadata JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_action (user_id, action_type),
    INDEX idx_created_at (created_at),
    INDEX idx_action_type (action_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
