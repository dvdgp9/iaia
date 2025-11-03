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

# Executor's Feedback or Assistance Requests

- Proveedor LLM: Gemini 1.5 Flash confirmado. API Key recibida (se gestionará vía `.env`, no se registrará en repo ni logs).
- Confirmar aceptación del esquema BD y estructura propuesta para proceder con migraciones y persistencia de conversaciones/login real en BD.

# Lessons

- Mantener comandos idempotentes para poder re-ejecutar sin fallos (p.ej. `git remote set-url` si `origin` ya existe).
- Documentar primero: BD y contratos de API, para evitar divergencias futuras.
