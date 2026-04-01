# SecuriForm

Sistema web multiempresa para gestionar formatos de seguridad de la informacion (segun politicas PSC del Estudio Palacios Abogados S.A.C.) con helpdesk centralizado.

Construido con **Laravel 11 + Filament 5 + Docker**.

---

## Requisitos

### Con Docker (recomendado)
- Docker Desktop 4.x+
- Docker Compose v2+
- Git

### Sin Docker
- PHP 8.2+ con extensiones: pdo, pdo_mysql, mbstring, json, fileinfo, gd, openssl, curl, redis
- MariaDB 10.11+ o MySQL 8.0+
- Redis 7+
- Composer 2.x
- Node.js 18+ con npm

---

## Instalacion rapida (Docker)

```bash
# 1. Clonar el repositorio
git clone https://github.com/fernando159s/sentinel-guard.git
cd sentinel-guard

# 2. Copiar variables de entorno
cp .env.example .env

# 3. Levantar todo con un comando
make init
```

Esto crea los contenedores, instala dependencias, migra la BD, ejecuta seeders y compila assets.

### URLs de desarrollo

| Servicio | URL |
|----------|-----|
| Aplicacion | http://localhost:8082 |
| Panel admin | http://localhost:8082/admin |
| phpMyAdmin | http://localhost:8081 |
| MailHog (emails) | http://localhost:8025 |

### Credenciales iniciales

| Usuario | Email | Contrasena | Rol |
|---------|-------|------------|-----|
| Super Admin | admin@securiform.local | Admin2024! | super_admin |
| Carlos Palacios | carlos@palacios.pe | Test2024! | admin_empresa |
| Ana Torres | ana@palacios.pe | Test2024! | usuario |

---

## Comandos de desarrollo

```bash
make up              # Levantar servicios
make down            # Parar servicios
make shell           # Entrar al contenedor (bash)
make logs            # Ver logs en tiempo real
make tinker          # Laravel REPL

make migrate         # Ejecutar migraciones
make migrate-fresh   # Reset BD + seeders
make seed            # Solo seeders

make npm-dev         # Vite en modo watch
make npm-build       # Build para produccion

make test            # Ejecutar tests
make cache-clear     # Limpiar caches Laravel
```

---

## Stack tecnico

| Capa | Tecnologia |
|------|-----------|
| Framework | Laravel 11 |
| Admin panel | Filament 5 |
| Reactividad | Livewire 3 |
| Frontend | Tailwind CSS + Alpine.js |
| Base de datos | MariaDB 10.11 |
| Cache/Colas | Redis 7 |
| PDF | mPDF 8.x |
| Excel | PhpSpreadsheet |
| Email | Laravel Mail (SMTP) + colas Redis |
| Permisos | spatie/laravel-permission |
| Multi-tenancy | Filament tenant isolation por empresa |

---

## Arquitectura

```
app/
  Enums/              Enums PHP (TipoFormato, etc.)
  Filament/
    Pages/            Paginas custom (PanelAgente, ReporteIncidencias)
    Resources/        CRUDs (Registros, Tickets, Users, Empresas, EmailTemplates)
    Widgets/          Widgets del dashboard (Stats, Charts, Tables)
  Jobs/               Jobs para colas (SendEmailJob)
  Mail/               Mailables (SecuriformMail)
  Models/             Modelos Eloquent
  Observers/          Observers (RegistroObserver para notif. F09)
  Policies/           Autorizacion por modelo
  Services/           Servicios (FormatoFields, RegistroNumber, PdfExport, ExcelExport)

database/
  migrations/         Migraciones Laravel
  seeders/            Seeders (Users, Roles, Empresas, EmailTemplates)

resources/
  views/
    filament/         Vistas custom Filament (panel-agente, view-ticket, widgets)
    emails/           Templates de email
```

---

## Multi-tenancy

Cada empresa es un **tenant** en Filament. Los usuarios solo ven datos de su empresa gracias a un **Global Scope** (`EmpresaScope`) que filtra automaticamente por `empresa_id`.

Los roles `super_admin` y `agente_helpdesk` ven datos de todas las empresas.

---

## Roles de usuario

| Rol | Que puede hacer |
|-----|-----------------|
| super_admin | Todo: gestionar empresas, usuarios, registros, tickets, plantillas email |
| admin_empresa | Gestionar usuarios y registros de SU empresa |
| usuario | Crear y ver registros de su empresa, abrir tickets |
| agente_helpdesk | Atender tickets de TODAS las empresas, crear registros desde tickets |
| solo_lectura | Solo ver registros y tickets, sin crear ni editar |

---

## Los 13 formatos de seguridad

| ID | Nombre | Para que sirve |
|----|--------|---------------|
| F01 | Auditorias realizadas | Control de revisiones de seguridad internas y externas |
| F02 | Banco de datos inscritos | Inventario de bases de datos con informacion personal |
| F03 | Prestadores con acceso | Registro de proveedores externos que ven datos personales |
| F04 | Datos sensibles | Control de datos delicados (salud, religion, etc.) |
| F05 | Personal autorizado | Lista de quien tiene permiso para ver las bases de datos |
| F06 | Acceso no autorizado | Documentar accesos sospechosos de soporte tecnico |
| F07 | Inventario de soportes | Lista de dispositivos donde se guardan datos |
| F08 | Ingreso/salida soportes | Control de entrada y salida de dispositivos con datos |
| F09 | Notificacion incidencias | Reporte inicial cuando ocurre un problema de seguridad |
| F10 | Resolucion incidencias | Como se resolvio un problema reportado en F09 |
| F11 | Recuperacion de datos | Registro de recuperacion de datos tras una incidencia |
| F12 | Copias de seguridad | Control de backups realizados |
| F13 | Destruccion de activos | Registro de destruccion segura de informacion |

---

## Sistema de notificaciones

- **Plantillas editables** desde el panel admin (Plantillas De Email)
- **Cola Redis** para envio asincrono con reintentos (3 intentos)
- **Notificacion automatica** al crear incidencia F09 → email al admin de empresa
- **Preferencias por usuario**: cada usuario puede activar/desactivar notificaciones de tickets e incidencias desde su perfil

---

## Configuracion de email

En `.env`:

```ini
# Desarrollo (MailHog incluido en Docker)
MAIL_MAILER=smtp
MAIL_HOST=mail
MAIL_PORT=1025

# Produccion (ejemplo con Gmail)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tuemail@gmail.com
MAIL_PASSWORD=xxxx-xxxx-xxxx-xxxx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tudominio.com
```

---

## Checklist de seguridad para produccion

- [ ] `APP_ENV=production` y `APP_DEBUG=false`
- [ ] `.env` no accesible desde el navegador
- [ ] HTTPS activado (SSL)
- [ ] Contrasena del super_admin cambiada
- [ ] Redis protegido con contrasena
- [ ] `storage/` y `bootstrap/cache/` con permisos de escritura
- [ ] Un usuario de empresa A no puede ver datos de empresa B
- [ ] Headers de seguridad activos (CSP, X-Frame-Options, etc.)
- [ ] Queue worker corriendo: `php artisan queue:work redis`

---

## Desarrollo

```bash
# Crear migracion
php artisan make:migration create_tabla_table

# Crear Filament Resource
php artisan make:filament-resource NombreModelo --generate

# Crear widget
php artisan make:filament-widget NombreWidget

# Ejecutar tests
php artisan test
```

### Integracion con Linear

```bash
export LINEAR_API_KEY="lin_api_xxxx"
./scripts/linear_get_tasks.sh "In Progress"
./scripts/linear_update_status.sh SEN-42 "Done"
```

---

## Licencia

Proyecto privado — Estudio Palacios Abogados S.A.C.
