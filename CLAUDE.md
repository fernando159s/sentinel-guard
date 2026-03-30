# CLAUDE.md — Contexto del proyecto SecuriForm

> Este archivo es leído automáticamente por Claude Code al inicio de cada sesión.
> Contiene todo el contexto necesario para trabajar en el proyecto sin explicaciones adicionales.

---

## ¿Qué es SecuriForm?

Aplicación web **Laravel + Filament multiempresa** para gestionar formatos de seguridad de la información
(según políticas PSC del Estudio Palacios Abogados S.A.C.) y un **helpdesk centralizado** donde
un equipo de agentes atiende tickets de todas las empresas registradas.

- **Entorno de desarrollo:** Docker (PHP 8.2, MariaDB, Redis, MailHog)
- **Hosting objetivo:** VPS o servidor con Docker, o hosting compartido con PHP 8.2+
- **Framework:** Laravel 11+ con Filament 3 para backoffice

---

## Stack técnico

| Capa | Tecnología | Notas |
|------|-----------|-------|
| Framework | Laravel 11+ | Eloquent ORM, Blade, Artisan |
| Admin/Backoffice | Filament 3 | Panels, Resources, Forms, Tables, Widgets |
| Reactividad | Livewire 3 | Incluido con Filament, sin escribir JS |
| Frontend | Tailwind CSS + Alpine.js | Incluidos con Filament/Livewire |
| Base de datos | MariaDB 10.11 | Eloquent ORM con migraciones |
| Cache/Sesiones/Colas | Redis 7 | Driver para cache, sessions y queue |
| PDF | mPDF 8.x | Vía composer |
| Excel | PhpSpreadsheet 1.x | Vía composer |
| Email | Laravel Mail (SMTP) | MailHog en desarrollo |
| Permisos/Roles | spatie/laravel-permission | Roles y permisos granulares |
| Multi-tenancy | Filament multi-tenancy | Tenant isolation por empresa |
| Auth | Laravel Breeze/Filament Auth | Login, registro, reset password |
| Assets | Vite | Bundler incluido con Laravel |
| Tests | Pest/PHPUnit | Vía `php artisan test` |

---

## Estructura de carpetas (Laravel + Filament)

```
securiform/
├── CLAUDE.md                   ← este archivo
├── README.md                   ← guía de instalación
├── Dockerfile                  ← imagen Docker PHP 8.2 + Apache
├── docker-compose.yml          ← servicios: app, db, redis, mailhog, phpmyadmin
├── Makefile                    ← comandos de desarrollo (make init, make up, etc.)
├── composer.json               ← dependencias PHP
├── package.json                ← dependencias Node (Vite, Tailwind)
├── vite.config.js              ← configuración Vite
│
├── docker/                     ← configuración Docker
│   ├── php.ini                 ← overrides PHP para desarrollo
│   ├── vhost.conf              ← Apache virtual host
│   └── entrypoint.sh           ← script de arranque del contenedor
│
├── app/
│   ├── Filament/               ← ★ Panel admin (Filament)
│   │   ├── Resources/          ← CRUDs auto-generados
│   │   │   ├── EmpresaResource.php
│   │   │   ├── UserResource.php
│   │   │   ├── RegistroResource.php
│   │   │   ├── TicketResource.php
│   │   │   └── AuditLogResource.php
│   │   ├── Pages/              ← Dashboard y páginas custom
│   │   └── Widgets/            ← Widgets del dashboard
│   │
│   ├── Models/                 ← Modelos Eloquent
│   │   ├── User.php
│   │   ├── Empresa.php
│   │   ├── Registro.php
│   │   ├── Ticket.php
│   │   ├── TicketMensaje.php
│   │   └── AuditLog.php
│   │
│   ├── Policies/               ← Autorización por modelo
│   ├── Providers/              ← Service providers
│   ├── Http/
│   │   ├── Controllers/        ← Controladores web (si se necesitan fuera de Filament)
│   │   └── Middleware/         ← Middleware custom (tenant, etc.)
│   ├── Mail/                   ← Mailables (notificaciones)
│   ├── Jobs/                   ← Jobs para colas (emails, exports)
│   └── Enums/                  ← Enums PHP 8.1 (roles, estados, formatos)
│
├── database/
│   ├── migrations/             ← Migraciones Laravel (reemplazan install.sql)
│   ├── seeders/                ← Seeders (reemplazan seed.sql)
│   └── factories/              ← Factories para tests
│
├── resources/
│   ├── views/                  ← Blade templates (Filament maneja la mayoría)
│   ├── css/                    ← Tailwind CSS custom
│   └── js/                     ← Alpine.js custom
│
├── routes/
│   ├── web.php                 ← Rutas web públicas
│   └── api.php                 ← API REST (si se necesita)
│
├── public/                     ← webroot
│   └── index.php               ← front controller Laravel
│
├── storage/                    ← logs, cache, uploads
│   └── app/
│       ├── logos/              ← logos de empresas
│       └── tickets/            ← adjuntos de tickets
│
├── config/                     ← config Laravel (app.php, database.php, etc.)
├── tests/                      ← Tests Pest/PHPUnit
│
├── install.sql                 ← referencia del esquema original (no se usa en Laravel)
├── seed.sql                    ← referencia de datos originales (no se usa en Laravel)
│
└── scripts/
    ├── linear_get_tasks.sh
    ├── linear_update_status.sh
    └── import_backlog.py
```

---

## Reglas críticas — NUNCA violar

### Seguridad (obligatorio en cada PR)

```php
// ✅ CORRECTO — Eloquent se encarga de prepared statements
$registros = Registro::where('empresa_id', $empresaId)
    ->where('tipo_formato', $tipo)
    ->get();

// ✅ CORRECTO — Query builder también es seguro
DB::table('registros')->where('empresa_id', '=', $empresaId)->get();

// ❌ JAMÁS — raw queries sin bindings
DB::select("SELECT * FROM registros WHERE id = $id");
```

1. **SQL:** Usar Eloquent o Query Builder. Si se usa `DB::raw()`, SIEMPRE con bindings.
2. **CSRF:** Laravel lo maneja automáticamente con `@csrf` en Blade. Filament lo incluye.
3. **XSS:** Blade escapa automáticamente con `{{ }}`. NUNCA usar `{!! !!}` con datos de usuario.
4. **Auth:** Usar Policies + middleware `auth` + Filament gates. Nunca verificar roles manualmente.
5. **Tenant:** Usar global scopes de Eloquent para filtrar por `empresa_id` automáticamente.
6. **Uploads:** Usar Filament FileUpload con validación MIME. Storage en `storage/app/`.

### Patrón de tenant isolation (Global Scope en Eloquent)

```php
// app/Models/Scopes/EmpresaScope.php
class EmpresaScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check() && auth()->user()->empresa_id) {
            $builder->where($model->getTable() . '.empresa_id', auth()->user()->empresa_id);
        }
    }
}

// En cada modelo que pertenece a una empresa:
protected static function booted(): void
{
    static::addGlobalScope(new EmpresaScope);
}
```

---

## Roles del sistema

| Rol | Slug | Puede hacer |
|-----|------|-------------|
| Super administrador | `super_admin` | Todo, incluyendo gestionar empresas |
| Admin de empresa | `admin_empresa` | Gestionar usuarios y registros de SU empresa |
| Usuario | `usuario` | Crear y ver registros de su empresa |
| Agente helpdesk | `agente_helpdesk` | Gestionar tickets de todas las empresas |
| Solo lectura | `solo_lectura` | Solo ver registros, no crear ni editar |

Gestionados via `spatie/laravel-permission`. Definidos en el seeder.

---

## Los 13 formatos de seguridad

| ID | Prefijo | Nombre | Política |
|----|---------|--------|----------|
| F01 | `AUD` | Auditorías realizadas | PSC000001 |
| F02 | `BD` | Banco de datos inscritos | PSC000001 |
| F03 | `PRES` | Prestadores con acceso a datos | PSC000001 / PSC000002 |
| F04 | `DS` | Datos sensibles | PSC000001 / PSC000-46 |
| F05 | `PA` | Personal autorizado al banco de datos | PSC000001 |
| F06 | `AS` | Acceso de soporte no autorizado | PSC000001 |
| F07 | `INV` | Inventario de soportes | PSC000003 / PSC000004 |
| F08 | `IS` | Ingreso y salida de soportes | PSC000003 |
| F09 | `INC` | Notificación de incidencias | PSC000001 / PSC000-25 |
| F10 | `RES` | Resolución de incidencias | PSC000-25 |
| F11 | `REC` | Recuperación de datos | PSC000001 / PSC000-25 |
| F12 | `BAK` | Copias de seguridad | PSC000003 / PSC000-15 |
| F13 | `DEST` | Destrucción de activos | PSC000003 / PSC000004 |

Número de registro generado: `[PREFIJO]-[AÑO]-[SEC 3 dígitos]` → Ejemplo: `INC-2026-004`

Cada formato se modela como un Filament Resource con formularios dinámicos.
Los datos específicos de cada formato se almacenan en un campo JSON `datos` en la tabla `registros`.

---

## Convenciones de código (Laravel)

```
Modelos:          StudlyCase singular       → User.php, Empresa.php, Registro.php
Controladores:    StudlyCase + Controller   → TicketController.php
Filament Resources: StudlyCase + Resource   → EmpresaResource.php
Migraciones:      snake_case con timestamp  → 2026_01_01_000001_create_empresas_table.php
Policies:         StudlyCase + Policy       → RegistroPolicy.php
Enums:            StudlyCase                → TipoFormato.php, EstadoTicket.php
Variables PHP:    camelCase                 → $empresaId, $userId
Columnas BD:      snake_case               → empresa_id, fecha_creacion
Rutas web:        kebab-case               → /mis-tickets, /nuevo-registro
Config keys:      snake_case con dots      → securiform.formatos.f01
```

---

## Filament: cómo crear un CRUD

```bash
# Generar un Resource completo (form + table + pages)
php artisan make:filament-resource Empresa --generate

# Generar un Resource con soft deletes
php artisan make:filament-resource Registro --generate --soft-deletes

# Crear una página custom
php artisan make:filament-page Dashboard

# Crear un widget
php artisan make:filament-widget StatsOverview --stats-overview
```

Cada Resource genera automáticamente:
- Formulario de creación/edición (con validación)
- Tabla con filtros, búsqueda y ordenamiento
- Páginas de listar, crear, editar y ver detalle
- Todo reactivo via Livewire (sin escribir JS)

---

## Base de datos — tablas principales

```
empresas              -- tenants del sistema
users                 -- todos los usuarios (tabla Laravel estándar extendida)
registros             -- los 13 formatos (campo JSON 'datos')
secuencias_registro   -- auto-incremento por empresa/formato/año
tickets               -- tickets del helpdesk
ticket_mensajes       -- hilo de conversación
ticket_adjuntos       -- archivos adjuntos
notificaciones_email  -- cola de emails
audit_logs            -- log inmutable de acciones
roles                 -- spatie/permission roles
permissions           -- spatie/permission permisos
model_has_roles       -- relación user-role
model_has_permissions -- relación user-permission
```

---

## Comandos de desarrollo (Docker)

```bash
# ── Primera vez ──
make init              # Crea proyecto Laravel, instala Filament, migra BD

# ── Día a día ──
make up                # Levantar servicios
make down              # Parar servicios
make shell             # Entrar al contenedor (bash)
make logs              # Ver logs en tiempo real
make tinker            # Laravel REPL

# ── Base de datos ──
make migrate           # Ejecutar migraciones
make migrate-fresh     # Reset BD + seeders
make seed              # Solo seeders
make db-shell          # Consola MySQL

# ── Filament ──
make filament-user     # Crear usuario admin

# ── Assets ──
make npm-dev           # Vite en modo watch
make npm-build         # Build para producción

# ── Tests ──
make test              # Ejecutar tests

# ── Limpieza ──
make clean             # Borrar volúmenes Docker (reset total)
make cache-clear       # Limpiar caches Laravel
```

### URLs de desarrollo

| Servicio | URL |
|----------|-----|
| App (frontend) | http://localhost:8080 |
| Panel admin Filament | http://localhost:8080/admin |
| phpMyAdmin | http://localhost:8081 |
| MailHog (emails) | http://localhost:8025 |

---

## Integración con Linear

Ver `scripts/linear_get_tasks.sh` para consultar tareas activas.

**Flujo diario:**
```bash
export LINEAR_API_KEY="lin_api_xxxx"
./scripts/linear_get_tasks.sh "In Progress"
# Trabajar con Claude Code...
./scripts/linear_update_status.sh SEC-42 "Done"
```

---

## Estado actual del proyecto

> Actualizar esta sección al inicio de cada sesión de trabajo.

- [x] SRD completado (`docs/01_SRD_SecuriForm.md`)
- [x] Backlog creado (`docs/02_BACKLOG_SecuriForm.md`)
- [x] EDT completado (`docs/03_EDT_SecuriForm.md`)
- [x] Integración Linear documentada (`docs/04_LINEAR_INTEGRATION.md`)
- [x] Mockups UI aprobados
- [x] Docker configurado (Laravel + Filament + MariaDB + Redis + MailHog)
- [ ] Ejecutar `make init` para crear proyecto Laravel
- [ ] Crear migraciones Laravel (basadas en install.sql)
- [ ] Crear seeders Laravel (basados en seed.sql)
- [ ] Crear Filament Resources para cada entidad
- [ ] Configurar multi-tenancy con Filament
- [ ] Primer deploy
