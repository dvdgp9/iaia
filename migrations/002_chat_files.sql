-- Migración: Tabla para almacenar archivos subidos al chat
-- Ejecutar en Plesk > Bases de datos > phpMyAdmin

CREATE TABLE IF NOT EXISTS chat_files (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  conversation_id BIGINT UNSIGNED NULL,
  message_id BIGINT UNSIGNED NULL,
  original_name VARCHAR(255) NOT NULL,
  stored_name VARCHAR(255) NOT NULL,
  mime_type VARCHAR(100) NOT NULL,
  size_bytes BIGINT UNSIGNED NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  expires_at DATETIME NOT NULL,
  CONSTRAINT fk_chat_files_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_chat_files_conversation FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE SET NULL,
  INDEX idx_expires (expires_at),
  INDEX idx_user_conv (user_id, conversation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Añadir campo file_id a la tabla messages para asociar archivos
ALTER TABLE messages ADD COLUMN file_id BIGINT UNSIGNED NULL AFTER content;
ALTER TABLE messages ADD CONSTRAINT fk_messages_file FOREIGN KEY (file_id) REFERENCES chat_files(id) ON DELETE SET NULL;
