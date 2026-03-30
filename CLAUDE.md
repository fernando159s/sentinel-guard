# CLAUDE.md — Contexto del proyecto SecuriForm

> Este archivo es leído automáticamente por Claude Code al inicio de cada sesión.
> Contiene todo el contexto necesario para trabajar en el proyecto sin explicaciones adicionales.

---

## ¿Qué es SecuriForm?

Aplicación web **PHP/MySQL multiempresa** para gestionar formatos de seguridad de la información
(según políticas PSC del Estudio Palacios Abogados S.A.C.) y un **helpdesk centralizado** donde
un equipo de agentes atiende tickets de todas las empresas registradas.

- **Hosting objetivo:** PHP compartido estándar (cPanel/DirectAdmin)
- **Sin:** Node.js, npm, frameworks PHP, build tools
- **Con:** PHP 8.1+ puro, MySQL/MariaDB, Bootstrap 5 CDN, Chart.js CDN

---

## Stack técnico

| Capa | Tecnología | Notas |
|------|-----------|-------|
| Backend | PHP 8.1+ puro | Sin Laravel, Symfony, etc. |
| Base de datos | MySQL 5.7+ / MariaDB 10.4+ | PDO con prepared statements |
| Frontend | Bootstrap 5.3 + JS vanilla | Cargado desde CDN, sin npm |
| PDF | mPDF 8.x | Vía composer o inclusión manual |
| Excel | PhpSpreadsheet 1.x | Vía composer |
| Email | PHPMailer 6.x | Vía composer o manual |
| Gráficos | Chart.js 4.x | CDN |
| Auth | Sesiones PHP nativas | Sin JWT |

---

## Estructura de carpetas

```
securiform/
├── CLAUDE.md                  ← este archivo
├── README.md                  ← guía de instalación
├── .env.example               ← plantilla de configuración
├── composer.json              ← dependencias PHP
├── install.sql                ← script de BD (crear tablas)
├── seed.sql                   ← datos iniciales (super admin)
│
├── config/
│   └── config.php             ← carga .env, define constantes globales
│
├── app/
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── CompanyController.php
│   │   ├── UserController.php
│   │   ├── RecordController.php   ← motor genérico para los 13 formatos
│   │   ├── TicketController.php
│   │   ├── AgentController.php
│   │   └── ExportController.php
│   │
│   ├── models/
│   │   ├── User.php
│   │   ├── Company.php
│   │   ├── Record.php             ← modelo genérico multiformat
│   │   ├── Ticket.php
│   │   ├── TicketMessage.php
│   │   └── AuditLog.php
│   │
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── main.php           ← layout con sidebar + topbar
│   │   │   └── auth.php           ← layout limpio para login
│   │   ├── auth/
│   │   │   ├── login.php
│   │   │   └── forgot_password.php
│   │   ├── dashboard/
│   │   │   └── index.php
│   │   ├── companies/
│   │   ├── users/
│   │   ├── records/               ← vistas compartidas para los 13 formatos
│   │   ├── tickets/               ← vista usuario
│   │   ├── helpdesk/              ← vista agente
│   │   └── emails/                ← templates HTML de email
│   │
│   └── helpers/
│       ├── Database.php           ← Singleton PDO
│       ├── Auth.php               ← sesión, roles, middleware
│       ├── Router.php             ← mapeo URL → controlador
│       ├── Mailer.php             ← wrapper PHPMailer
│       ├── AuditLogger.php        ← log de auditoría
│       ├── ExportHelper.php       ← PDF y Excel
│       ├── CsrfHelper.php         ← generación y validación CSRF
│       └── FormatsConfig.php      ← definición de los 13 formatos
│
├── public/                    ← webroot (apunta aquí el hosting)
│   ├── index.php              ← front controller
│   ├── .htaccess              ← rewrite rules
│   ├── css/
│   │   └── app.css            ← estilos personalizados
│   └── js/
│       └── app.js             ← JS compartido
│
├── uploads/                   ← fuera del webroot o protegido
│   ├── logos/                 ← logos de empresas
│   └── tickets/               ← adjuntos de tickets
│
├── vendor/                    ← dependencias composer
│
└── scripts/
    ├── linear_get_tasks.sh    ← consultar tareas activas en Linear
    ├── linear_update_status.sh← marcar tarea como Done en Linear
    └── import_backlog.py      ← importar backlog completo a Linear
```

---

## Reglas críticas — NUNCA violar

### Seguridad (obligatorio en cada PR)

```php
// ✅ CORRECTO — siempre así
$stmt = $db->prepare("SELECT * FROM registros WHERE id = ? AND empresa_id = ?");
$stmt->execute([$id, $_SESSION['empresa_id']]);

// ❌ JAMÁS — sin prepared statements
$result = $db->query("SELECT * FROM registros WHERE id = $id");
```

1. **SQL:** PDO prepared statements en el 100% de las queries. Zero concatenación de variables en SQL.
2. **CSRF:** Token en cada formulario POST. Validar en servidor antes de procesar.
3. **Salida:** `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')` en TODAS las variables que se renderizan en HTML.
4. **Auth:** Verificar sesión activa Y rol autorizado al inicio de CADA método de controlador.
5. **Tenant:** Incluir `empresa_id = ?` en TODA query que toque datos de empresa. Sin excepción.
6. **Uploads:** Validar tipo MIME + extensión en servidor (no solo en cliente). Renombrar a UUID.

### Patrón de tenant isolation (copiar en cada modelo)

```php
// En cada método que lee datos de empresa:
private function getEmpresaId(): int {
    return (int) ($_SESSION['empresa_id'] ?? 0);
}

// Toda query de datos lleva este filtro:
WHERE empresa_id = ?   -- siempre como parámetro, nunca interpolado
```

---

## Roles del sistema

| Rol | Constante | Puede hacer |
|-----|-----------|-------------|
| Super administrador | `ROLE_SUPER_ADMIN` | Todo, incluyendo gestionar empresas |
| Admin de empresa | `ROLE_ADMIN` | Gestionar usuarios y registros de SU empresa |
| Usuario | `ROLE_USER` | Crear y ver registros de su empresa |
| Agente helpdesk | `ROLE_AGENT` | Gestionar tickets de todas las empresas |
| Solo lectura | `ROLE_READONLY` | Solo ver registros, no crear ni editar |

Constantes definidas en `config/config.php`:
```php
define('ROLE_SUPER_ADMIN', 'super_admin');
define('ROLE_ADMIN',       'admin_empresa');
define('ROLE_USER',        'usuario');
define('ROLE_AGENT',       'agente_helpdesk');
define('ROLE_READONLY',    'solo_lectura');
```

---

## Los 13 formatos de seguridad

Cada formato tiene: prefijo de número, nombre, política PSC de referencia.

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

Número de registro generado automáticamente: `[PREFIJO]-[AÑO]-[SEC 3 dígitos]`
Ejemplo: `INC-2026-004`

Los campos de cada formato están definidos en `app/helpers/FormatsConfig.php`.

---

## Convenciones de código

```
Controladores:    StudlyCase + "Controller"   → RecordController.php
Modelos:          StudlyCase                  → User.php, Ticket.php
Vistas:           snake_case                  → ticket_detail.php, record_list.php
Helpers:          StudlyCase                  → Database.php, CsrfHelper.php
Variables PHP:    camelCase                   → $empresaId, $userId
Columnas BD:      snake_case                  → empresa_id, fecha_creacion
Constantes PHP:   UPPER_SNAKE_CASE            → ROLE_ADMIN, MAX_UPLOAD_SIZE
URLs:             kebab-case                  → /mis-tickets, /nuevo-registro
```

---

## Convención de respuesta en controladores

```php
class RecordController extends Controller {

    public function index(): void {
        // 1. Verificar auth
        Auth::requireLogin();
        Auth::requireRole([ROLE_USER, ROLE_ADMIN, ROLE_SUPER_ADMIN]);

        // 2. Obtener empresa_id de la sesión (SIEMPRE)
        $empresaId = (int) $_SESSION['empresa_id'];

        // 3. Lógica de negocio
        $records = Record::getAllByEmpresa($empresaId, $_GET['tipo'] ?? null);

        // 4. Renderizar vista
        $this->view('records/list', [
            'records' => $records,
            'title'   => 'Registros de seguridad',
        ]);
    }
}
```

---

## Base de datos — tablas principales

```sql
-- Las 10 tablas del sistema (detalle completo en install.sql)
empresas            -- tenants del sistema
usuarios            -- todos los usuarios de todas las empresas
sesiones            -- control de sesiones activas
tokens_recuperacion -- recuperación de contraseña
registros           -- los 13 formatos (campos en JSON)
tickets             -- tickets del helpdesk
ticket_mensajes     -- hilo de conversación por ticket
ticket_adjuntos     -- archivos adjuntos de mensajes
notificaciones_email-- cola de emails a enviar
logs_auditoria      -- log inmutable de todas las acciones
```

Los registros de los 13 formatos se almacenan en una sola tabla `registros`
con el campo `datos` (JSON) que contiene todos los campos del formulario.
Esto permite agregar nuevos formatos sin migraciones de BD.

---

## Integración con Linear

Ver `scripts/linear_get_tasks.sh` para consultar tareas activas.

**Flujo diario:**
```bash
# 1. Ver tareas activas
export LINEAR_API_KEY="lin_api_xxxx"
./scripts/linear_get_tasks.sh "In Progress"

# 2. Trabajar en la tarea con Claude Code
# (pegar la salida del script en el prompt)

# 3. Marcar como terminada
./scripts/linear_update_status.sh SEC-42 "Done"
```

**ID de issues en Linear:** `[EP-XX] US-XXXX — Título`
Ejemplo: `[EP-05] US-0509 — F09: Notificación de incidencias`

---

## Comandos útiles de desarrollo

```bash
# Instalar dependencias PHP (si hay composer)
composer install

# Crear la base de datos desde cero
mysql -u root -p securiform < install.sql
mysql -u root -p securiform < seed.sql

# Ver logs de PHP en tiempo real (desarrollo local)
tail -f /var/log/php_errors.log

# Verificar sintaxis PHP antes de subir
php -l app/controllers/RecordController.php

# Importar backlog completo a Linear (primera vez)
python3 scripts/import_backlog.py
```

---

## Estado actual del proyecto

> Actualizar esta sección al inicio de cada sesión de trabajo.

- [x] SRD completado (`docs/01_SRD_SecuriForm.md`)
- [x] Backlog creado (`docs/02_BACKLOG_SecuriForm.md`)
- [x] EDT completado (`docs/03_EDT_SecuriForm.md`)
- [x] Integración Linear documentada (`docs/04_LINEAR_INTEGRATION.md`)
- [x] Mockups UI aprobados
- [ ] Linear configurado con las 54 user stories
- [ ] Sprint 1 iniciado (infraestructura base)
- [ ] `install.sql` probado en hosting
- [ ] Primer deploy en hosting
