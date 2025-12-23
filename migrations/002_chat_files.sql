-- Migración: Tabla para almacenar archivos subidos al chat
-- Ejecutar en Plesk > Bases de datos > phpMyAdmin

CREATE TABLE IF NOT EXISTS chat_files (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  conversation_id INT NULL,
  message_id INT NULL,
  original_name VARCHAR(255) NOT NULL,
  stored_name VARCHAR(255) NOT NULL,
  mime_type VARCHAR(100) NOT NULL,
  size_bytes INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  expires_at DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE SET NULL,
  INDEX idx_expires (expires_at),
  INDEX idx_user_conv (user_id, conversation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Añadir campo file_id a la tabla messages para asociar archivos
ALTER TABLE messages ADD COLUMN file_id INT NULL AFTER content;
ALTER TABLE messages ADD CONSTRAINT fk_messages_file FOREIGN KEY (file_id) REFERENCES chat_files(id) ON DELETE SET NULL;
