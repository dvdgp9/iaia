-- Añadir favoritos a conversaciones
-- Aplicar: mysql -u[USER] -p[PASS] [DATABASE] < docs/migrations/003_add_favorites.sql

ALTER TABLE conversations 
ADD COLUMN is_favorite TINYINT(1) NOT NULL DEFAULT 0 AFTER status,
ADD INDEX conversations_user_favorite_idx (user_id, is_favorite);
