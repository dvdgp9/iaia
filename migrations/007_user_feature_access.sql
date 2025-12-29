-- Migración 007: Sistema de permisos por usuario para features (gestos, voces, generación de imágenes)
-- Fecha: 2025-12-29

-- Tabla principal de acceso a features por usuario
-- feature_type: 'gesture', 'voice', 'feature' (para features globales como image-generation)
-- feature_slug: identificador único de la feature (ej: 'write-article', 'lex', 'image-generation')
-- Por defecto, si no existe registro = usuario NO tiene acceso (whitelist)
-- Si existe registro con enabled=1 = usuario TIENE acceso

CREATE TABLE IF NOT EXISTS user_feature_access (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  feature_type ENUM('gesture', 'voice', 'feature') NOT NULL,
  feature_slug VARCHAR(50) NOT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  -- Cada usuario solo puede tener un registro por feature
  UNIQUE KEY uq_user_feature (user_id, feature_type, feature_slug),
  
  -- Índices para consultas frecuentes
  KEY idx_user_type (user_id, feature_type),
  KEY idx_feature (feature_type, feature_slug),
  
  CONSTRAINT fk_user_feature_user 
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Catálogo de features disponibles (para UI de admin)
-- Esto permite mostrar todas las opciones disponibles en el panel de admin
CREATE TABLE IF NOT EXISTS available_features (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  feature_type ENUM('gesture', 'voice', 'feature') NOT NULL,
  feature_slug VARCHAR(50) NOT NULL,
  name VARCHAR(100) NOT NULL,
  description VARCHAR(255) NULL,
  icon VARCHAR(50) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  UNIQUE KEY uq_feature (feature_type, feature_slug),
  KEY idx_type_active (feature_type, is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed de features disponibles
INSERT INTO available_features (feature_type, feature_slug, name, description, icon, sort_order) VALUES
-- Gestos
('gesture', 'write-article', 'Escribir artículo', 'Genera artículos, blogs y notas de prensa', 'iconoir-page-edit', 1),
('gesture', 'social-media', 'Redes sociales', 'Crea publicaciones para redes sociales', 'iconoir-send-diagonal', 2),
('gesture', 'podcast-from-article', 'Podcast desde artículo', 'Convierte artículos en podcasts con IA', 'iconoir-podcast', 3),

-- Voces
('voice', 'lex', 'Lex', 'Asistente legal de Ebone', 'iconoir-balance', 1),

-- Features globales
('feature', 'image-generation', 'Generación de imágenes', 'Crear imágenes con nanobanana 🍌', 'iconoir-media-image', 1);

-- Por defecto, dar acceso a todas las features al superadmin (user_id=1)
INSERT INTO user_feature_access (user_id, feature_type, feature_slug, enabled)
SELECT 1, feature_type, feature_slug, 1 
FROM available_features 
WHERE is_active = 1;
