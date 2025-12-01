# Background and Motivation

Ebonia: plataforma interna de inteligencia corporativa (Grupo Ebone) basada en PHP, JS, MySQL. MVP: escritorio con chat central, sidebar con historiales por usuario, login propio y roles básicos. Proveedor LLM inicial: Gemini (1.5 Flash). Conversations en MySQL. Sin streaming (request→response). Preparado para multi-empresa a futuro.

# Key Challenges and Analysis

- Abstracción de proveedor LLM (arranque con Gemini 1.5 Flash, extensible a otros modelos).
- Modelo de datos escalable: users/departments/companies, conversations/messages, folders, roles/permissions.
- Seguridad: sesiones PHP, hashing Argon2id, HTTPS/HSTS/CSP, saneamiento inputs/CSRF.
- UI mínima con Tailwind CDN y JS vanilla manteniendo escalabilidad.
- Documentación de tablas en repo (única fuente de verdad de la BD).

# High-level Task Breakdown

1. Definir y acordar esquema BD (tablas, claves, índices) y documentarlo.
2. Definir estructura de proyecto (public/, api/, src/, config/, docs/, assets/...).
3. Preparar configuración: `.env.example`, `.gitignore`, configuración sesiones seguras.
4. Implementar autenticación (login/logout, registro admin inicial, RBAC mínimo: admin/user).
5. UI MVP: escritorio (chat central + sidebar), Tailwind CDN, layout base.
6. Endpoint `/api/chat` con Gemini 1.5 Flash (request→response), capa proveedor.
7. Persistencia de conversaciones/mensajes y CRUD básico (renombrar, archivar, folders, mover).
8. Semillas iniciales: empresas y departamentos proporcionados.
9. README con setup (PHP 8.2+, MySQL, variables entorno) y decisiones.

# Project Status Board

- [x] Crear `index.php` de placeholder.
- [x] Inicializar Git con rama `main` y primer commit.
- [x] Conectar `origin` y hacer `git push -u origin main`.
- [ ] Acordar esquema BD y registrarlo en `docs/db_schema.md`.
- [ ] Acordar estructura de proyecto y scaffolding inicial.
- [ ] Implementar autenticación básica (admin/user).
- [ ] Implementar `/api/chat` con Gemini 1.5 Flash.
- [ ] UI MVP: escritorio y sidebar con historiales.
- [x] Scaffolding MVP (public/api/src) y utilidades base.
- [x] Endpoints mínimos auth/login, auth/logout y chat.
- [x] `.env` local configurado.

# Current Status / Progress Tracking

- 2025-11-03: `index.php` creado. Repo inicializado en `main` y push a remoto realizado.
- 2025-11-03: Borrador de `docs/db_schema.md` creado para revisión.
- 2025-11-03: Scaffolding y endpoints mínimos creados. `.env` configurado con credenciales locales.
- Listo para pruebas locales con `php -S -t public`.
- 2025-11-26: **SEGURIDAD**: Corregido problema de autenticación en `index.php`. Se agregó verificación de sesión en PHP antes de renderizar HTML. Antes solo se verificaba con JavaScript, permitiendo que usuarios no autenticados vieran la interfaz brevemente.
- 2025-11-27: **ARQUITECTURA MULTI-PROVEEDOR**: Implementada capa de abstracción LLM (LlmProvider, GeminiProvider, LlmProviderFactory). Preparado para soportar múltiples proveedores (Gemini, ChatGPT, etc.) mediante configuración.
- 2025-12-01: **CONTEXTO CORPORATIVO**: Implementado sistema de contexto unificado con ContextBuilder. Ebonia ahora recibe conocimiento base del Grupo Ebone mediante systemInstruction en todas las conversaciones. Carpeta `docs/context/` creada con `system_prompt.md` y `grupo_ebone_overview.md`.
- 2025-12-01: **FOLDERS**: Implementada funcionalidad completa de carpetas para organizar conversaciones. Usuarios pueden crear, renombrar, eliminar carpetas y mover conversaciones entre ellas. Incluye FoldersRepo, 6 endpoints API (/folders/list, create, rename, delete, move, reorder) y UI completa en sidebar.

# Executor's Feedback or Assistance Requests

- Proveedor LLM: Gemini 1.5 Flash confirmado. API Key recibida (se gestionará vía `.env`, no se registrará en repo ni logs).
- **URGENTE - RBAC no funcional**: Las tablas `user_roles` y `role_permissions` están vacías. El sistema de permisos no funciona. Script de corrección creado en `docs/migrations/004_fix_rbac.sql`. Aplicar para activar el RBAC.
- **Limpieza de migraciones**: Eliminar duplicado de tabla `voices` en `001_init.sql` (líneas 198-225). Eliminar tabla `schema_migrations` si no se usa.
- **FOLDERS IMPLEMENTADOS**: Sistema completo de carpetas privadas por usuario funcionando. Falta aplicar `004_fix_rbac.sql` y probar todo end-to-end.

# Lessons

- Mantener comandos idempotentes para poder re-ejecutar sin fallos (p.ej. `git remote set-url` si `origin` ya existe).
- Documentar primero: BD y contratos de API, para evitar divergencias futuras.
- **Folders privadas por usuario**: Implementado sistema completo de carpetas jerárquicas con parent_id. Prevención de ciclos en FoldersRepo::move(). Carpetas se eliminan en cascada pero conversaciones quedan sin carpeta (ON DELETE SET NULL). UI incluye filtrado por carpeta "Todas", "Sin carpeta" y carpetas personalizadas.
- **Seguridad**: Siempre verificar autenticación en PHP ANTES de renderizar HTML. La verificación solo en JavaScript es insegura porque el HTML se envía al navegador antes de ejecutarse el script, permitiendo que usuarios no autenticados vean contenido protegido brevemente. Patrón correcto:
  ```php
  Session::start();
  $user = Session::user();
  if (!$user) {
      header('Location: /login.php');
      exit;
  }
  ```
- **Contexto corporativo desacoplado de proveedores**: El conocimiento base (docs/context/*.md) se mantiene independiente del LLM usado. ContextBuilder lo compila una vez y cada proveedor lo inyecta en su formato nativo (systemInstruction para Gemini, mensaje 'system' para OpenAI). Esto permite cambiar de proveedor sin perder el contexto corporativo.
- **System instructions > mensajes de contexto**: Usar systemInstruction (Gemini) o rol 'system' (OpenAI) es más eficiente que insertar el contexto como mensajes normales, porque no cuenta contra el límite de tokens de historial y tiene mayor peso en las respuestas del modelo.
