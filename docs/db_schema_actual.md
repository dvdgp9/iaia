# Ebonia - Esquema de Base de Datos (Estado Real)

**Fecha de auditoría:** 2025-12-01  
**Base de datos:** `ebonia_db`  
**Servidor:** localhost:3306  
**Charset:** utf8mb4 / utf8mb4_unicode_ci

---

## Resumen Ejecutivo

Este documento refleja el **estado real** de la base de datos de Ebonia tras la aplicación de migraciones:
- ✅ **Migración 001_init.sql** aplicada (estructura base)
- ✅ **Migración 002_seed_core.sql** aplicada (datos iniciales)
- ✅ **Migración 003_add_favorites.sql** aplicada (campo is_favorite)
- ✅ **Migración 004_gesture_executions.sql** aplicada (historial de gestos)
- ✅ **Migración 005_voice_executions.sql** aplicada (historial de voces)
- ✅ **Migración 006_remember_tokens.sql** aplicada (tokens de recordarme)

### Estadísticas

| Tabla | Registros | Estado |
|-------|-----------|--------|
| companies | 4 | ✅ Poblada |
| departments | 5 | ✅ Poblada |
| users | 2 | ✅ Poblada |
| roles | 2 | ✅ Poblada |
| permissions | 8 | ✅ Poblada |
| conversations | 14 | ✅ En uso |
| messages | 36 | ✅ En uso |
| folders | 0 | ⚪ Vacía |
| voices | 0 | ⚪ Vacía |
| gestures | 0 | ⚪ Vacía |
| gesture_executions | 0 | ⚪ Vacía |
| voice_executions | 0 | ⚪ Vacía |
| remember_tokens | 0 | ⚪ Vacía |
| user_roles | 0 | ⚠️ Vacía (crítico) |
| role_permissions | 0 | ⚠️ Vacía (crítico) |

---

## 1. Tablas de Organización

### 1.1. `companies`
Empresas del grupo Ebone.

**Estructura:**
```sql
CREATE TABLE companies (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(150) NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY companies_slug_uq (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Datos actuales (4 registros):**
| id | name | slug | active |
|----|------|------|--------|
| 1 | Grupo Ebone | grupo-ebone | 1 |
| 2 | Ebone Servicios | ebone-servicios | 1 |
| 3 | Uniges-3 | uniges-3 | 1 |
| 4 | CUBOFIT | cubofit | 1 |

---

### 1.2. `departments`
Departamentos corporativos.

**Estructura:**
```sql
CREATE TABLE departments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id BIGINT UNSIGNED NULL,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  KEY departments_company_id_idx (company_id),
  UNIQUE KEY departments_slug_uq (slug),
  CONSTRAINT fk_departments_company_id FOREIGN KEY (company_id)
    REFERENCES companies(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Datos actuales (5 registros):**
| id | company_id | name | slug |
|----|------------|------|------|
| 1 | NULL | Marketing | marketing |
| 2 | NULL | Contabilidad | contabilidad |
| 3 | NULL | Laboral | laboral |
| 4 | NULL | Proyectos | proyectos |
| 5 | NULL | Operaciones | operaciones |

**Nota:** Todos los departamentos son corporativos (company_id NULL) en el MVP.

---

## 2. Tablas de Usuarios y Autenticación

### 2.1. `users`
Usuarios internos de Ebonia.

**Estructura:**
```sql
CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id BIGINT UNSIGNED NULL,
  department_id BIGINT UNSIGNED NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  first_name VARCHAR(80) NOT NULL,
  last_name VARCHAR(120) NOT NULL,
  is_superadmin TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('active','disabled') NOT NULL DEFAULT 'active',
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY users_email_uq (email),
  KEY users_company_id_idx (company_id),
  KEY users_department_id_idx (department_id),
  CONSTRAINT fk_users_company_id FOREIGN KEY (company_id)
    REFERENCES companies(id) ON DELETE SET NULL,
  CONSTRAINT fk_users_department_id FOREIGN KEY (department_id)
    REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Datos actuales (2 registros):**
| id | email | first_name | last_name | is_superadmin | status | last_login_at |
|----|-------|------------|-----------|---------------|--------|---------------|
| 1 | invitado@ebone.es | David | Gutiérrez | 1 | active | 2025-12-01 12:01:42 |
| 6 | lucia@ebone.es | Lucía | Rosales | 0 | active | 2025-11-04 13:02:31 |

**Nota crítica:** Usuario ID 1 tiene `is_superadmin=1` pero NO tiene roles asignados en `user_roles`.

---

### 2.2. `roles`
Roles del sistema RBAC.

**Estructura:**
```sql
CREATE TABLE roles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  slug VARCHAR(80) NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY roles_slug_uq (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Datos actuales (2 registros):**
| id | name | slug |
|----|------|------|
| 1 | Administrador | admin |
| 2 | Usuario | user |

---

### 2.3. `permissions`
Permisos disponibles en el sistema.

**Estructura:**
```sql
CREATE TABLE permissions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY permissions_slug_uq (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Datos actuales (8 registros):**
| id | name | slug |
|----|------|------|
| 1 | Usar chat | chat.use |
| 2 | Gestionar conversaciones propias | conversations.manage_own |
| 3 | Ver voces | voices.view |
| 4 | Gestionar voces | voices.manage |
| 5 | Ver gestos | gestures.view |
| 6 | Ejecutar gestos | gestures.run |
| 7 | Gestionar gestos | gestures.manage |
| 8 | Gestionar usuarios | users.manage |

---

### 2.4. `user_roles`
Relación muchos a muchos entre usuarios y roles.

**Estructura:**
```sql
CREATE TABLE user_roles (
  user_id BIGINT UNSIGNED NOT NULL,
  role_id BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (user_id, role_id),
  KEY user_roles_role_id_idx (role_id),
  CONSTRAINT fk_user_roles_user_id FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_user_roles_role_id FOREIGN KEY (role_id)
    REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Datos actuales:** ⚠️ **VACÍA** (0 registros)

---

### 2.6. `remember_tokens`
Tokens persistentes para la funcionalidad **Recordarme (30 días)**. Permiten restaurar la sesión de usuario aunque la sesión PHP haya expirado, manteniendo la seguridad mediante tokens rotativos almacenados en la base de datos.

**Estructura:**
```sql
CREATE TABLE remember_tokens (
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
```

**Datos actuales:** ⚪ **VACÍA** (0 registros, se crean al usar "Recordarme")

---

### 2.5. `role_permissions`
Relación muchos a muchos entre roles y permisos.

**Estructura:**
```sql
CREATE TABLE role_permissions (
  role_id BIGINT UNSIGNED NOT NULL,
  permission_id BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  KEY role_permissions_permission_id_idx (permission_id),
  CONSTRAINT fk_role_permissions_role_id FOREIGN KEY (role_id)
    REFERENCES roles(id) ON DELETE CASCADE,
  CONSTRAINT fk_role_permissions_permission_id FOREIGN KEY (permission_id)
    REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Datos actuales:** ⚠️ **VACÍA** (0 registros)

---

## 3. Tablas de Conversaciones

### 3.1. `conversations`
Conversaciones de usuarios con el asistente.

**Estructura:**
```sql
CREATE TABLE conversations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  folder_id BIGINT UNSIGNED NULL,
  voice_id BIGINT UNSIGNED NULL,
  company_id BIGINT UNSIGNED NULL,
  title VARCHAR(200) NOT NULL,
  status ENUM('active','archived') NOT NULL DEFAULT 'active',
  is_favorite TINYINT(1) NOT NULL DEFAULT 0,  -- Añadido en migración 003
  metadata JSON NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  KEY conversations_user_id_idx (user_id),
  KEY conversations_folder_id_idx (folder_id),
  KEY conversations_voice_id_idx (voice_id),
  KEY conversations_user_favorite_idx (user_id, is_favorite),  -- Añadido en migración 003
  CONSTRAINT fk_conversations_user_id FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_conversations_folder_id FOREIGN KEY (folder_id)
    REFERENCES folders(id) ON DELETE SET NULL,
  CONSTRAINT fk_conversations_voice_id FOREIGN KEY (voice_id)
    REFERENCES voices(id) ON DELETE SET NULL,
  CONSTRAINT fk_conversations_company_id FOREIGN KEY (company_id)
    REFERENCES companies(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Datos actuales:** 14 conversaciones activas

**Ejemplos:**
| id | user_id | title | status | is_favorite |
|----|---------|-------|--------|-------------|
| 3 | 1 | Funciona el limón en el lavavajillas? | active | 0 |
| 9 | 6 | Cómo se llama el lado oculto de la luna? | active | 0 |
| 13 | 1 | Hola | active | 1 |

**Notas:**
- ✅ Campo `is_favorite` presente y funcional (migración 003 aplicada)
- ✅ `folder_id` y `voice_id` NULL en todas (funcionalidad no implementada aún)
- ✅ Títulos auto-generados a partir del primer mensaje

---

### 3.2. `messages`
Mensajes dentro de conversaciones.

**Estructura:**
```sql
CREATE TABLE messages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  conversation_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  role ENUM('user','assistant','system') NOT NULL,
  content LONGTEXT NOT NULL,
  model VARCHAR(120) NULL,
  input_tokens INT NULL,
  output_tokens INT NULL,
  metadata JSON NULL,
  created_at DATETIME NOT NULL,
  KEY messages_conversation_id_idx (conversation_id),
  KEY messages_user_id_idx (user_id),
  CONSTRAINT fk_messages_conversation_id FOREIGN KEY (conversation_id)
    REFERENCES conversations(id) ON DELETE CASCADE,
  CONSTRAINT fk_messages_user_id FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Datos actuales:** 36 mensajes

**Modelo utilizado:** `gemini-2.5-flash` (todos los mensajes de assistant)

**Distribución:**
- role='user': ~18 mensajes (con user_id)
- role='assistant': ~18 mensajes (model='gemini-2.5-flash')

---

### 3.3. `folders`
Carpetas para organizar conversaciones.

**Estructura:**
```sql
CREATE TABLE folders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(150) NOT NULL,
  parent_id BIGINT UNSIGNED NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  KEY folders_user_id_idx (user_id),
  KEY folders_parent_id_idx (parent_id),
  CONSTRAINT fk_folders_user_id FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_folders_parent_id FOREIGN KEY (parent_id)
    REFERENCES folders(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Datos actuales:** ⚪ VACÍA (funcionalidad no implementada)

---

### 4.3. `gesture_executions`
Historial de ejecuciones de gestos (por ejemplo, el gesto **Escribir contenido**). Guarda tanto los parámetros de entrada como el contenido generado y permite marcar favoritos.

**Estructura:**
```sql
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
```

**Datos actuales:** ⚪ **VACÍA** (MVP recién creado)

---

### 4.4. `voice_executions`
Historial de ejecuciones de **voces especializadas** (por ejemplo, Lex). Cada registro representa un chat con una voz, incluyendo el contexto de entrada y la última respuesta generada.

**Estructura:**
```sql
CREATE TABLE voice_executions (
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
```

**Datos actuales:** ⚪ **VACÍA** (MVP Lex recién creado)

---

## 4. Tablas de Catálogos IA

### 4.1. `voices`
Asistentes/voces preconfigurados.

**Estructura:**
```sql
CREATE TABLE voices (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  description VARCHAR(300) NULL,
  provider ENUM('gemini','openai','qwen','other') NOT NULL DEFAULT 'gemini',
  model VARCHAR(120) NOT NULL,
  system_prompt TEXT NULL,
  visibility ENUM('global','company','department','user') NOT NULL DEFAULT 'global',
  scope_company_id BIGINT UNSIGNED NULL,
  scope_department_id BIGINT UNSIGNED NULL,
  scope_user_id BIGINT UNSIGNED NULL,
  temperature DECIMAL(3,2) NULL,
  top_p DECIMAL(3,2) NULL,
  max_output_tokens INT NULL,
  metadata JSON NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  KEY voices_scope_company_id_idx (scope_company_id),
  KEY voices_scope_department_id_idx (scope_department_id),
  KEY voices_scope_user_id_idx (scope_user_id),
  CONSTRAINT fk_voices_scope_company_id FOREIGN KEY (scope_company_id)
    REFERENCES companies(id) ON DELETE SET NULL,
  CONSTRAINT fk_voices_scope_department_id FOREIGN KEY (scope_department_id)
    REFERENCES departments(id) ON DELETE SET NULL,
  CONSTRAINT fk_voices_scope_user_id FOREIGN KEY (scope_user_id)
    REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Datos actuales:** ⚪ VACÍA (funcionalidad no implementada)

---

### 4.2. `gestures`
Acciones rápidas (quick actions).

**Estructura:**
```sql
CREATE TABLE gestures (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  description VARCHAR(300) NULL,
  prompt_template TEXT NOT NULL,
  config JSON NULL,
  provider ENUM('gemini','openai','qwen','other') NOT NULL DEFAULT 'gemini',
  model VARCHAR(120) NOT NULL,
  visibility ENUM('global','company','department','user') NOT NULL DEFAULT 'global',
  scope_company_id BIGINT UNSIGNED NULL,
  scope_department_id BIGINT UNSIGNED NULL,
  scope_user_id BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  KEY gestures_scope_company_id_idx (scope_company_id),
  KEY gestures_scope_department_id_idx (scope_department_id),
  KEY gestures_scope_user_id_idx (scope_user_id),
  CONSTRAINT fk_gestures_scope_company_id FOREIGN KEY (scope_company_id)
    REFERENCES companies(id) ON DELETE SET NULL,
  CONSTRAINT fk_gestures_scope_department_id FOREIGN KEY (scope_department_id)
    REFERENCES departments(id) ON DELETE SET NULL,
  CONSTRAINT fk_gestures_scope_user_id FOREIGN KEY (scope_user_id)
    REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Datos actuales:** ⚪ VACÍA (funcionalidad no implementada)

---

## 5. Diagrama de Relaciones (ERD)

```
┌─────────────┐
│  companies  │
└──────┬──────┘
       │
       ├──────────────┐
       │              │
       ▼              ▼
┌─────────────┐  ┌────────────┐
│departments  │  │   users    │◄──┐
└──────┬──────┘  └─────┬──────┘   │
       │               │           │
       └───────────────┘           │
                       │           │
       ┌───────────────┼───────────┤
       │               │           │
       ▼               ▼           │
┌─────────────┐  ┌──────────┐    │
│   voices    │  │  folders │    │
└──────┬──────┘  └────┬─────┘    │
       │              │           │
       │              │           │
       ▼              ▼           │
┌──────────────────────────┐     │
│     conversations        │     │
└───────────┬──────────────┘     │
            │                    │
            ▼                    │
┌──────────────────────┐         │
│      messages        │─────────┘
└──────────────────────┘

RBAC:
┌────────┐         ┌────────────┐         ┌──────────────┐
│ users  │◄────────│user_roles  │────────►│    roles     │
└────────┘         └────────────┘         └──────┬───────┘
                                                  │
                                                  ▼
                                          ┌───────────────┐
                                          │role_permissions│
                                          └───────┬────────┘
                                                  │
                                                  ▼
                                          ┌───────────────┐
                                          │  permissions  │
                                          └───────────────┘
```

---

## 6. Análisis de Inconsistencias

### 🔴 Críticas (requieren acción inmediata)

#### 6.1. Tablas RBAC vacías
**Problema:** `user_roles` y `role_permissions` están vacías.

**Impacto:**
- ❌ Los usuarios NO tienen roles asignados
- ❌ Los roles NO tienen permisos asignados
- ❌ El sistema RBAC no funciona (aunque `is_superadmin` lo bypasea parcialmente)

**Solución recomendada:**
```sql
-- Asignar permisos al rol 'admin'
INSERT INTO role_permissions (role_id, permission_id, created_at)
SELECT 1, id, NOW() FROM permissions;

-- Asignar permisos básicos al rol 'user'
INSERT INTO role_permissions (role_id, permission_id, created_at)
SELECT 2, id, NOW() FROM permissions 
WHERE slug IN ('chat.use', 'conversations.manage_own', 'voices.view', 'gestures.view', 'gestures.run');

-- Asignar rol admin al usuario superadmin
INSERT INTO user_roles (user_id, role_id, created_at)
VALUES (1, 1, NOW());

-- Asignar rol user a Lucía
INSERT INTO user_roles (user_id, role_id, created_at)
VALUES (6, 2, NOW());
```

---

#### 6.2. Tabla `voices` duplicada en migración
**Problema:** En `001_init.sql`, la tabla `voices` está definida dos veces:
- Líneas 124-151
- Líneas 198-225 (duplicado idéntico)

**Impacto:**
- ⚠️ La segunda definición sobreescribe la primera (no causa error por `IF NOT EXISTS`)
- ⚠️ Confusión al leer el script de migración

**Solución recomendada:**
Eliminar la definición duplicada en `001_init.sql` (líneas 198-225).

---

### 🟡 Advertencias (no críticas pero recomendables)

#### 6.3. Funcionalidades no implementadas
Las siguientes tablas están vacías porque sus funcionalidades no están implementadas:
- `folders` (organización de conversaciones)
- `voices` (asistentes personalizados)
- `gestures` (acciones rápidas)

**Acción:** Esto es esperado en MVP. Documentar el roadmap de implementación.

---

#### 6.4. Campo `company_id` siempre NULL
En `departments`, todos los registros tienen `company_id = NULL`.

**Acción:** Esto es correcto para MVP (departamentos corporativos). Documentar que en V2 podrán ser específicos de empresa.

---

## 7. Validación de Integridad Referencial

### ✅ Foreign Keys verificadas

Todas las FK definidas en las migraciones están correctamente aplicadas:

| Tabla | Columna | Referencia | ON DELETE |
|-------|---------|------------|-----------|
| departments | company_id | companies(id) | SET NULL |
| users | company_id | companies(id) | SET NULL |
| users | department_id | departments(id) | SET NULL |
| user_roles | user_id | users(id) | CASCADE |
| user_roles | role_id | roles(id) | CASCADE |
| role_permissions | role_id | roles(id) | CASCADE |
| role_permissions | permission_id | permissions(id) | CASCADE |
| folders | user_id | users(id) | CASCADE |
| folders | parent_id | folders(id) | SET NULL |
| conversations | user_id | users(id) | CASCADE |
| conversations | folder_id | folders(id) | SET NULL |
| conversations | voice_id | voices(id) | SET NULL |
| conversations | company_id | companies(id) | SET NULL |
| messages | conversation_id | conversations(id) | CASCADE |
| messages | user_id | users(id) | SET NULL |
| voices | scope_company_id | companies(id) | SET NULL |
| voices | scope_department_id | departments(id) | SET NULL |
| voices | scope_user_id | users(id) | SET NULL |
| gestures | scope_company_id | companies(id) | SET NULL |
| gestures | scope_department_id | departments(id) | SET NULL |
| gestures | scope_user_id | users(id) | SET NULL |

---

## 8. Recomendaciones

### Inmediatas
1. ✅ **Poblar RBAC** usando el script SQL del apartado 6.1
2. ✅ **Limpiar migración 001** eliminando el duplicado de `voices`
3. ✅ **Eliminar tabla `schema_migrations`** (no utilizada actualmente)

### Corto plazo
4. 📝 Implementar seeds para `voices` (Cubo, Lex, Uniges como asistentes base)
5. 📝 Implementar seeds para `gestures` (acciones rápidas del UI)
6. 📝 Añadir índices compuestos para queries frecuentes:
   ```sql
   ALTER TABLE conversations ADD INDEX idx_user_updated (user_id, updated_at DESC);
   ALTER TABLE messages ADD INDEX idx_conversation_created (conversation_id, created_at ASC);
   ```

### Medio plazo (V2)
7. 📋 Implementar funcionalidad de `folders`
8. 📋 Permitir asociar `company_id` a departamentos específicos
9. 📋 Añadir tabla `documents` para RAG
10. 📋 Implementar auditoría de cambios (opcional)

---

## 9. Conclusiones

### Estado general: ✅ Bueno

La base de datos está correctamente estructurada y las migraciones aplicadas funcionan. Las principales observaciones:

**Fortalezas:**
- ✅ Estructura sólida y escalable
- ✅ Foreign keys correctamente definidas
- ✅ Charset utf8mb4 consistente
- ✅ Sistema de chat funcionando correctamente (14 conversaciones, 36 mensajes)
- ✅ Migración de favoritos aplicada exitosamente

**Puntos de mejora críticos:**
- 🔴 RBAC no funcional (tablas de relación vacías)
- 🔴 Duplicado en migración 001

**Roadmap funcional:**
- ⚪ Folders, voices y gestures pendientes de implementar (esperado en MVP)

---

**Última actualización:** 2025-12-01  
**Próxima revisión:** Tras aplicar correcciones RBAC
