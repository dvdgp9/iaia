-- Migración 005: Historial de ejecuciones de voces
-- Fecha: 2025-12-03
-- Descripción: Crea tabla para almacenar el historial de chats con voces especializadas

CREATE TABLE IF NOT EXISTS voice_executions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  voice_id VARCHAR(50) NOT NULL,                 -- 'lex', 'cubo', 'uniges', etc.
  title VARCHAR(200) NOT NULL,                   -- Título auto-generado del chat
  input_data JSON NOT NULL,                      -- Historial de mensajes
  output_content LONGTEXT NOT NULL,              -- Última respuesta generada
  model VARCHAR(120) NULL,                       -- Modelo LLM usado
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  
  KEY voice_executions_user_id_idx (user_id),
  KEY voice_executions_voice_idx (voice_id),
  KEY voice_executions_user_voice_idx (user_id, voice_id, updated_at DESC),
  
  CONSTRAINT fk_voice_executions_user_id 
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
