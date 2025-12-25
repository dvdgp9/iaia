-- Migration: Background Jobs para procesamiento asíncrono
-- Fecha: 2025-12-25
-- Descripción: Sistema de cola de trabajos en background (podcasts, etc.)

CREATE TABLE IF NOT EXISTS background_jobs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  job_type VARCHAR(50) NOT NULL COMMENT 'Tipo de job: podcast, etc.',
  status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
  progress_text VARCHAR(255) DEFAULT NULL COMMENT 'Texto de progreso para mostrar al usuario',
  input_data JSON COMMENT 'Datos de entrada del job',
  output_data JSON COMMENT 'Resultado del job (cuando completed)',
  error_message TEXT COMMENT 'Mensaje de error (cuando failed)',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  started_at DATETIME DEFAULT NULL,
  completed_at DATETIME DEFAULT NULL,
  
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_status (status),
  INDEX idx_user_status (user_id, status),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índice para limpiar jobs antiguos (opcional, ejecutar periódicamente)
-- DELETE FROM background_jobs WHERE completed_at < DATE_SUB(NOW(), INTERVAL 7 DAY);
