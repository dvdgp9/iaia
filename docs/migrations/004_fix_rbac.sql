-- Corrección del sistema RBAC
-- Aplicar: mysql -u[USER] -p[PASS] ebonia_db < docs/migrations/004_fix_rbac.sql

-- 1. Asignar TODOS los permisos al rol 'admin' (id=1)
INSERT INTO role_permissions (role_id, permission_id, created_at)
SELECT 1, id, NOW() FROM permissions
ON DUPLICATE KEY UPDATE created_at=VALUES(created_at);

-- 2. Asignar permisos básicos al rol 'user' (id=2)
INSERT INTO role_permissions (role_id, permission_id, created_at)
SELECT 2, id, NOW() FROM permissions 
WHERE slug IN (
  'chat.use',
  'conversations.manage_own',
  'voices.view',
  'gestures.view',
  'gestures.run'
)
ON DUPLICATE KEY UPDATE created_at=VALUES(created_at);

-- 3. Asignar rol 'admin' al usuario superadmin (id=1, invitado@ebone.es)
INSERT INTO user_roles (user_id, role_id, created_at)
VALUES (1, 1, NOW())
ON DUPLICATE KEY UPDATE created_at=VALUES(created_at);

-- 4. Asignar rol 'user' a Lucía (id=6, lucia@ebone.es)
INSERT INTO user_roles (user_id, role_id, created_at)
VALUES (6, 2, NOW())
ON DUPLICATE KEY UPDATE created_at=VALUES(created_at);

-- Verificación
SELECT 'Permisos asignados a roles:' AS '';
SELECT r.name AS rol, COUNT(rp.permission_id) AS num_permisos
FROM roles r
LEFT JOIN role_permissions rp ON r.id = rp.role_id
GROUP BY r.id, r.name;

SELECT '' AS '';
SELECT 'Usuarios con roles asignados:' AS '';
SELECT u.email, r.name AS rol
FROM users u
INNER JOIN user_roles ur ON u.id = ur.user_id
INNER JOIN roles r ON ur.role_id = r.id;
