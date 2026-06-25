# Plan de migración — Filament → Laravel API + Angular SPA

> Estado: aprobado (decisiones cerradas 2026-06-25). Rewrite big-bang; el Filament actual sigue en prod hasta el cutover.

## Decisiones de arquitectura (cerradas)
- **Estrategia:** big-bang / rewrite. Cutover único al final. Prod (Filament) intacto durante toda la construcción → riesgo cero a prod.
- **Repos separados:**
  - `sentinel-guard-api` — Laravel API-only.
  - `sentinel-guard-web` — Angular 21 SPA.
  - `sentinel-ui` — librería de design system en SCSS (reutilizable en otros proyectos).
- **Auth:** Laravel Sanctum, **cookie SPA**, **same-origin** (web en raíz + API en `/api`) → sin CORS, cookies triviales.
- **API:** REST + Laravel API Resources, `/api/v1`. **OpenAPI** del backend → **tipos/cliente TypeScript** generados para Angular.
- **Angular 21:** standalone + signals, **sin NgRx** al inicio. i18n con **Transloco** (solo es-PE ahora, listo para más).
- **sentinel-ui:** SCSS **agnóstica** (solo clases, no componentes Angular en la lib). **BEM con prefijo `sg-`**. Tokens Sass → **CSS custom properties** (`--sg-*`) para theming en runtime (dark mode / acento por tenant). Identidad **Sentinel Teal** (SEC-7). **Storybook** como showcase. Dart Sass, paquete npm (GitHub Packages), semver. WCAG AA.
- **Testing:** API PHPUnit; Angular **Vitest** (unit) + **Playwright** (e2e).
- **Datos:** sin migración (misma MariaDB; solo cambia el frontend).
- **Validación:** local hasta el cutover (sin staging dedicado por ahora).
- **CI/CD:** pipeline por repo. Observabilidad (Sentry) en fase posterior.
- Multi-tenancy: `EmpresaScope` resuelto por request en la API. RBAC spatie expuesto + aplicado por endpoint. PDFs (mPDF) y el asistente: backend.

## Arquitectura objetivo
```
sentinel-guard-web (Angular 21 SPA)
   │ usa sentinel-ui (SCSS, clases sg-*, tokens Sentinel Teal)
   │ cookie Sanctum · /api/v1 REST JSON · tipos generados de OpenAPI
   ▼
sentinel-guard-api (Laravel API-only)  ── MariaDB / Redis (sin cambios de datos)
   tenancy (EmpresaScope) · RBAC (spatie) · policies · PDFs · asistente
```

## Streams y tareas

### STREAM 0 — Fundaciones
- T0.1 Este doc de arquitectura/plan en `docs/architecture/`.
- T0.2 Crear los 3 repos (api/web/ui) + convenciones de commit/CI base.
- T0.3 Convenciones de API: `/api/v1`, envelope de error, paginación/filtros/orden, OpenAPI base.

### STREAM A — Backend API (`sentinel-guard-api`)
- A.1 Scaffold Laravel API-only + Sanctum stateful (same-origin) + rutas `/api/v1`.
- A.2 Auth: login/logout, CSRF cookie, `me`, reset de contraseña.
- A.3 Multi-tenancy por request (EmpresaScope) + aislamiento verificado.
- A.4 RBAC (spatie) expuesto + abilities/middleware por endpoint.
- A.5 OpenAPI spec + pipeline de generación de tipos TS para el web.
- A.6 Empresas + Users + Roles.
- A.7 Registros (13 formatos PSC, `datos` dinámico + secuencias).
- A.8 Activos Digitales + Baúl + Credenciales (Policy de acceso).
- A.9 Equipos + Checklists.
- A.10 Helpdesk (tickets, mensajes, adjuntos).
- A.11 Políticas + Panel de cumplimiento.
- A.12 Reportes (PDF mPDF + ZIP).
- A.13 Dashboard / métricas.
- A.14 Auditoría (lectura) + Asistente (endpoint chat).
- A.15 Tests de API + seguridad (authz por endpoint).

### STREAM B — Design system (`sentinel-ui`)
- B.1 Scaffold lib SCSS (capas ITCSS-lite) + build Dart Sass + paquete npm + Storybook.
- B.2 Tokens Sentinel Teal (Sass maps → CSS vars `--sg-*`), claro/oscuro.
- B.3 Base (reset, tipografía) + layout (container, grid, stack) + utilidades mínimas.
- B.4 Componentes v1 (BEM `sg-`): button, field/form, card, table, badge/estados, nav (topbar/sidebar), alert/toast, spinner.
- B.5 Stories + guía de uso (API de clases) + checks WCAG AA.
- B.6 Publicación versionada + consumo desde el web.

### STREAM C — Angular SPA (`sentinel-guard-web`)
- C.1 Scaffold Angular 21 (standalone, signals, routing, lint, Vitest, Playwright).
- C.2 Integrar sentinel-ui + tema Sentinel Teal + Transloco.
- C.3 Core: HTTP (interceptores cookie/XSRF/errores), auth Sanctum (login/guard/me/logout), contexto de tenant, directivas RBAC, cliente generado de OpenAPI.
- C.4 Layout shell (topbar/sidebar/selector de empresa).
- **C.5 PILOTO: Auth + Dashboard end-to-end** (valida todo el patrón antes de replicar).
- C.6 Registros (13 formatos, forms dinámicos).
- C.7 Activos + Baúl (+ búsqueda global).
- C.8 Equipos + Checklists.
- C.9 Helpdesk.
- C.10 Políticas + Cumplimiento.
- C.11 Centro de Reportes.
- C.12 Empresas/Usuarios/Roles.
- C.13 Auditoría.
- C.14 Asistente (chat).
- C.15 e2e + build prod.

### STREAM D — Infra & Cutover
- D.1 CI por repo (build/test api, web, ui).
- D.2 Despliegue same-origin en Hostinger (web estático en raíz + API en `/api`).
- D.3 Checklist de paridad Filament → Angular.
- D.4 Cutover: validación local → switch webroot a Angular → Filament como fallback → remover Filament.
- D.5 Revisión de seguridad de la superficie nueva (authz API, CSRF, cookies, exposición de datos).

## Ejecución (org Paperclip)
- **Tech Lead** lidera arquitectura/decisiones y reparte. **Backend Developer** (Stream A), **Frontend Developer** + **UX/Designer** (Streams B/C), **Code Reviewer** + **QA** (calidad). El usuario lidera/valida Angular.
- Orden: 0 → (A + B en paralelo) → C (con C.5 como piloto) → D.
