-- Migración 004: Historial de ejecuciones de gestos
-- Fecha: 2025-12-03
-- Descripción: Crea tabla para almacenar el historial de contenido generado por gestos

CREATE TABLE gesture_executions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  gesture_type VARCHAR(50) NOT NULL,           -- 'write-article', 'translate', etc.
  title VARCHAR(200) NOT NULL,                 -- Título auto-generado del resultado
  input_data JSON NOT NULL,                    -- Datos del formulario
  output_content LONGTEXT NOT NULL,            -- Contenido generado
  content_type VARCHAR(50) NULL,               -- Subtipo: 'informativo', 'blog', 'nota-prensa'
  business_line VARCHAR(50) NULL,              -- 'ebone', 'cubofit', 'uniges'
  model VARCHAR(120) NULL,                     -- Modelo LLM usado
  is_favorite TINYINT(1) NOT NULL DEFAULT 0,   -- Para marcar favoritos
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  
  KEY gesture_executions_user_id_idx (user_id),
  KEY gesture_executions_type_idx (gesture_type),
  KEY gesture_executions_user_type_idx (user_id, gesture_type, created_at DESC),
  
  CONSTRAINT fk_gesture_executions_user_id 
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
