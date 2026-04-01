# Requerimientos No Funcionales — SecuriForm

> **Proyecto:** SecuriForm — Gestion de formatos de seguridad de la informacion + helpdesk
> **Equipo Linear:** SentinelForms (SEN)
> **Fecha de elaboracion:** 2026-04-01
> **Version del documento:** 1.0

---

## Tabla Resumen

| ID | Categoria | Metrica clave | Estado |
|----|-----------|--------------|--------|
| RNF-001 | Performance | Panel Filament < 2s, Exports < 5s | Por validar |
| RNF-002 | Seguridad | Eloquent/QueryBuilder, CSRF, XSS prevenido, tenant isolation | Parcial |
| RNF-003 | Disponibilidad | 99.5% uptime, healthchecks Docker, auto-restart | Parcial |
| RNF-004 | Mantenibilidad | Convenciones Laravel, Filament patterns, >70% coverage | Parcial |
| RNF-005 | Compatibilidad | Chrome, Firefox, Safari (ultimas 2 versiones) | Por validar |
| RNF-006 | Observabilidad | Logs stdout, audit trail, monitoreo de colas | Parcial |

---

## RNF-001: Performance

- **Metrica:**
  - Tiempo de carga del panel Filament: < 2 segundos.
  - Generacion de PDF: < 5 segundos para registros individuales.
  - Generacion de Excel: < 10 segundos para exports masivos (hasta 1000 registros).
  - Queries a BD: sin N+1, usar eager loading (`with()`).
- **Criterios de aceptacion:**
  1. Las paginas de listado de Filament cargan en menos de 2 segundos con paginacion.
  2. La generacion de PDF individual se completa en menos de 5 segundos.
  3. Redis se utiliza para cache de sesiones y configuracion.

---

## RNF-002: Seguridad

- **Metrica:**
  - 100% de queries via Eloquent o Query Builder con bindings.
  - 0 vulnerabilidades criticas OWASP Top 10.
  - Aislamiento total de datos entre empresas (0 fugas cross-tenant).
- **Criterios de aceptacion:**
  1. Toda query usa Eloquent o Query Builder. Si se usa `DB::raw()`, siempre con bindings.
  2. CSRF habilitado en todos los formularios (automatico con Blade/Filament).
  3. XSS prevenido: `{{ }}` en Blade, nunca `{!! !!}` con datos de usuario.
  4. Policies + middleware `auth` + Filament gates para autorizacion.
  5. Global scope `EmpresaScope` filtra automaticamente por `empresa_id`.
  6. Uploads validados por MIME type via Filament FileUpload.
  7. Passwords con bcrypt (costo >= 12).

---

## RNF-003: Disponibilidad

- **Metrica:**
  - Uptime mensual: >= 99.5%.
  - Tiempo de recuperacion ante fallo de contenedor: < 30 segundos.
- **Criterios de aceptacion:**
  1. Todos los servicios Docker tienen healthchecks configurados.
  2. Docker Compose reinicia contenedores automaticamente (`restart: unless-stopped`).
  3. Redis persiste datos para recovery (RDB o AOF).

---

## RNF-004: Mantenibilidad

- **Metrica:**
  - 100% adherencia a convenciones de `CLAUDE.md`.
  - Cobertura de tests >= 70% en servicios criticos.
- **Criterios de aceptacion:**
  1. Logica de negocio en Services, no en Controllers ni Resources.
  2. Migraciones con rollback funcional (`down()`).
  3. Makefile con shortcuts para todas las operaciones comunes.
  4. Codigo sigue convenciones de nombres de `CLAUDE.md`.

---

## RNF-005: Compatibilidad

- **Metrica:**
  - Navegadores: Chrome, Firefox, Safari (ultimas 2 versiones).
  - Responsive: panel Filament funcional desde 1024px.
- **Criterios de aceptacion:**
  1. El panel Filament funciona correctamente en Chrome, Firefox y Safari.
  2. Los exports PDF se generan correctamente en todos los navegadores.
  3. Los formularios dinamicos renderizan correctamente en pantallas >= 1024px.

---

## RNF-006: Observabilidad

- **Metrica:**
  - 100% de logs a stdout (capturados por Docker).
  - 100% de acciones criticas registradas en audit_logs.
- **Criterios de aceptacion:**
  1. Canal de log configurado como `stderr` en produccion.
  2. `make logs` muestra logs de todos los contenedores en tiempo real.
  3. Tabla `audit_logs` registra creacion, edicion y eliminacion de registros.
  4. Jobs fallidos quedan en `failed_jobs` con stack trace.
