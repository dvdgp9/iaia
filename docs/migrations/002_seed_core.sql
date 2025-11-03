-- Seeds mínimos (roles, permissions, companies, departments)

INSERT INTO roles (name, slug, created_at, updated_at)
VALUES
  ('Administrador', 'admin', NOW(), NOW()),
  ('Usuario', 'user', NOW(), NOW())
ON DUPLICATE KEY UPDATE name=VALUES(name), updated_at=VALUES(updated_at);

INSERT INTO permissions (name, slug, created_at, updated_at)
VALUES
  ('Usar chat', 'chat.use', NOW(), NOW()),
  ('Gestionar conversaciones propias', 'conversations.manage_own', NOW(), NOW()),
  ('Ver voces', 'voices.view', NOW(), NOW()),
  ('Gestionar voces', 'voices.manage', NOW(), NOW()),
  ('Ver gestos', 'gestures.view', NOW(), NOW()),
  ('Ejecutar gestos', 'gestures.run', NOW(), NOW()),
  ('Gestionar gestos', 'gestures.manage', NOW(), NOW()),
  ('Gestionar usuarios', 'users.manage', NOW(), NOW())
ON DUPLICATE KEY UPDATE name=VALUES(name), updated_at=VALUES(updated_at);

-- Empresas iniciales
INSERT INTO companies (name, slug, active, created_at, updated_at)
VALUES
  ('Grupo Ebone', 'grupo-ebone', 1, NOW(), NOW()),
  ('Ebone Servicios', 'ebone-servicios', 1, NOW(), NOW()),
  ('Uniges-3', 'uniges-3', 1, NOW(), NOW()),
  ('CUBOFIT', 'cubofit', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE name=VALUES(name), active=VALUES(active), updated_at=VALUES(updated_at);

-- Departamentos (sin company_id específico en MVP)
INSERT INTO departments (company_id, name, slug, created_at, updated_at)
VALUES
  (NULL, 'Marketing', 'marketing', NOW(), NOW()),
  (NULL, 'Contabilidad', 'contabilidad', NOW(), NOW()),
  (NULL, 'Laboral', 'laboral', NOW(), NOW()),
  (NULL, 'Proyectos', 'proyectos', NOW(), NOW()),
  (NULL, 'Operaciones', 'operaciones', NOW(), NOW())
ON DUPLICATE KEY UPDATE name=VALUES(name), updated_at=VALUES(updated_at);
