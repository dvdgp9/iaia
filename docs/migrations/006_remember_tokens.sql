-- Tabla para tokens de "Recordarme" persistentes
-- Soluciona el problema de que las sesiones PHP se borren antes de que expire la cookie

CREATE TABLE IF NOT EXISTS remember_tokens (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  token_hash VARCHAR(64) NOT NULL,  -- SHA256 del token (nunca guardamos el token en claro)
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  KEY idx_remember_user (user_id),
  KEY idx_remember_token (token_hash),
  KEY idx_remember_expires (expires_at),
  
  CONSTRAINT fk_remember_user FOREIGN KEY (user_id) 
    REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
