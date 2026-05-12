# Inventario funcional — SecuriForm

> Estado actual de la aplicación: módulos, funcionalidades, gestiones y artefactos técnicos.
> Generado: 2026-05-11. Rama: `feature/sen-120-reportes-quitar-filtro-fecha-descarga-zip-de-ndas-firmados`.

---

## 1. Visión general

SecuriForm es una aplicación web **Laravel 11 + Filament 3 multiempresa (multi-tenant)** que cubre:

- **Gestión de formatos de seguridad de la información** (13 formatos PSC).
- **Helpdesk centralizado** de tickets transversal a todas las empresas.
- **Gestión de activos** (equipos, asignaciones, movimientos, backups).
- **Cumplimiento normativo** (políticas, NDAs firmados, capacitaciones obligatorias).
- **Reportería operativa** con exportación a Excel/PDF y descarga ZIP de NDAs firmados.

El panel admin de Filament expone navegación agrupada en: **General · Soporte · Reportes · Activos · Seguridad · Capacitaciones · Administración**.

---

## 2. Módulos funcionales

### 2.1. Administración (multi-tenant)

| Funcionalidad | Recurso/Página | Notas |
|---|---|---|
| Gestión de empresas (tenants) | `EmpresaResource` | CRUD + branding (logo, colores) por empresa. Relation managers: usuarios y registros. |
| Gestión de usuarios | `UserResource` | Asignación a empresa, roles (spatie/permission), datos personales. |
| Plantillas de email | `EmailTemplateResource` | Edición de plantillas transaccionales. |
| Registro público de empresa | `Auth/RegisterEmpresa` | Self-onboarding de nueva empresa. |
| Perfil de usuario | `Auth/EditProfile`, `MiPerfil` | Datos personales + firma manuscrita. |
| Selección de empresa (super admin) | `select-empresa` (ruta) + middleware `RedirectSuperAdminToSelectEmpresa` | Acceso cross-tenant para super admin. |
| Branding por tenant | Middleware `ApplyTenantBranding` | Aplica logo/colores de la empresa activa. |

**Roles definidos** (vía `RolePermissionSeeder`): `super_admin`, `admin_empresa`, `usuario`, `agente_helpdesk`, `solo_lectura`.

---

### 2.2. Seguridad — formatos PSC y políticas

| Funcionalidad | Recurso/Página | Notas |
|---|---|---|
| Registros de los 13 formatos | `RegistroResource` | F01–F13 (AUD, BD, PRES, DS, PA, AS, INV, IS, INC, RES, REC, BAK, DEST). Campos dinámicos vía `FormatoFieldsService`. Numeración auto por empresa/formato/año (`RegistroNumberService`). |
| Catálogo de políticas/NDAs | `PoliticaResource` | Versionado (`PoliticaVersion`), archivo PDF, firma admin, marcador de obligatoriedad, vigencia de aceptación. |
| Aceptación de políticas (usuario final) | `AceptarPoliticas` | Flujo obligatorio gateado por middleware `EnsurePoliciesAccepted`. |
| Wiki de políticas | `WikiPoliticas` | Vista de consulta para todo el personal. |
| Completar datos NDA | `CompletarDatosNda` | Captura de datos faltantes previo a firma. |
| Firmantes NDA | `ViewNdaFirmantes` (página dentro de PoliticaResource) | Listado y descarga ZIP de NDAs firmados. |
| Generación PDF de política | `PoliticaPdfController` (ruta `/politicas/{politica}/pdf`) | Render PDF con mPDF. |
| Enums | `TipoFormato` | Define los 13 tipos de formato. |

---

### 2.3. Activos

| Funcionalidad | Recurso/Página | Notas |
|---|---|---|
| Inventario de equipos | `EquipoResource` | CRUD con campos extendidos de activos. |
| Asignaciones y movimientos | Modelos `EquipoAsignacion` (+ tabla `ticket_equipo`) | Tracking de movimientos por equipo. Relación a tickets. |
| Plantillas de checklist | `ChecklistPlantillaResource` | Definición de checklists reutilizables. |
| Ejecución de checklists | `EjecutarChecklist` (página) + modelo `ChecklistEjecucion` | Llenado operativo por usuario. |
| Programación de backups | `BackupProgramacionResource` | Calendario de respaldos por activo/sistema. |
| Ejecuciones de backup | Modelo `BackupEjecucion` | Bitácora de corridas. |
| Alarmas de backup | Comando `CheckBackupAlarms` | Job programable para alertar incumplimientos. |

---

### 2.4. Soporte (helpdesk)

| Funcionalidad | Recurso/Página | Notas |
|---|---|---|
| Tickets | `TicketResource` | CRUD con numeración auto (`TicketNumberService`). |
| Mensajes del ticket | Modelo `TicketMensaje` | Hilo de conversación. |
| Adjuntos | Modelo `TicketAdjunto` + ruta `/tickets/adjuntos/{adjunto}` | Descarga segura de archivos. |
| Relación con equipos | Tabla `ticket_equipo` | Vincula ticket a uno o más activos. |
| Panel del agente | `PanelAgente` (página) | Vista operativa cross-tenant para agentes de helpdesk. |

---

### 2.5. Capacitaciones

| Funcionalidad | Recurso/Página | Notas |
|---|---|---|
| Catálogo de capacitaciones | `CapacitacionResource` | Modalidades vía enum `ModalidadCapacitacion`. |
| Asistencia | Modelo `CapacitacionAsistencia` | Registro de participantes. |
| Vista de usuario | `MisCapacitaciones` (página) | Capacitaciones asignadas al usuario logueado. |

---

### 2.6. Reportes

Centro de reportes y reportes individuales (los individuales ya no aparecen en sidebar, según commits recientes; quedan accesibles desde el centro).

| Página | Contenido |
|---|---|
| `CentroReportes` | Hub central. Descarga ZIP de NDAs firmados. |
| `ReporteIncidencias` | Formatos F09/F10 (incidencias y resolución). |
| `ReporteCapacitaciones` | Cumplimiento de capacitaciones. |
| `ReporteMovimientosSoportes` | Formato F08 (ingreso y salida de soportes). |
| `ReporteDestruccionActivos` | Formato F13 (destrucción de activos). |
| `ReporteInventarioSoportes` | Formato F07 (inventario de soportes). |
| `ReporteChecklists` | Ejecuciones de checklists. |
| `PanelCumplimiento` | Vista agregada de cumplimiento normativo. |

Exportación: `ExcelExportService` (PhpSpreadsheet) y `PdfExportService` (mPDF).

---

### 2.7. Dashboards y widgets

| Widget | Función |
|---|---|
| `Dashboard` (página) | Página principal del panel. |
| `DashboardStats` | KPIs generales. |
| `LatestRegistros` | Últimos registros creados. |
| `QuickAccessFormatos` | Accesos rápidos a los 13 formatos. |
| `RegistrosPorTipoChart` | Gráfico distribución por tipo. |
| `TicketsTendenciaChart` | Tendencia de tickets. |
| `ChecklistsVencidos` | Alerta de checklists pendientes. |
| `MiEstado` | Estado del usuario actual. |
| `UsuarioDashboard` | Dashboard contextual para rol usuario. |
| `PoliticasCumplimiento` | Estado de aceptación de políticas. |
| `PersonalOverview` | Vista resumen de personal. |

---

## 3. Servicios transversales (`app/Services/`)

| Servicio | Responsabilidad |
|---|---|
| `AuditService` | Escritura inmutable a `audit_logs`. |
| `RegistroNumberService` | Genera correlativo `[PREFIJO]-[AÑO]-[SEC]` por empresa/formato/año. |
| `TicketNumberService` | Genera correlativo de ticket por empresa. |
| `FormatoFieldsService` | Define campos dinámicos por tipo de formato (F01–F13). |
| `ExcelExportService` | Exportación XLSX (PhpSpreadsheet). |
| `PdfExportService` | Exportación PDF (mPDF). |

---

## 4. Infraestructura de aplicación

### 4.1. Middleware custom

| Middleware | Función |
|---|---|
| `ApplyTenantBranding` | Inyecta branding (logo/colores) del tenant activo. |
| `EnsurePoliciesAccepted` | Bloquea acceso hasta que el usuario acepte políticas obligatorias vigentes. |
| `RedirectSuperAdminToSelectEmpresa` | Fuerza al super admin a elegir tenant antes de operar. |
| `SecurityHeaders` | Headers HTTP de seguridad (CSP, etc.). |
| `BlockStoragePhpExecution` | Impide ejecución de PHP servido desde `storage/`. |

### 4.2. Email

- Mailable único `SecuriformMail` parametrizado por `EmailTemplate`.
- Job `SendEmailJob` para envío asíncrono.
- Modelo `NotificacionEmail` como bitácora.
- MailHog en desarrollo (`http://localhost:8025`).

### 4.3. Auditoría

- Modelo `AuditLog` + `AuditService` para registro inmutable de acciones.

### 4.4. Comandos Artisan

| Comando | Uso |
|---|---|
| `CheckBackupAlarms` | Verifica programaciones vencidas y dispara alertas. |
| `EmpresaSetupCommand` | Setup inicial de una empresa (datos base, plantillas, etc.). |

### 4.5. Seeders relevantes

`EmpresaSeeder`, `UserSeeder`, `RolePermissionSeeder`, `EmailTemplateSeeder`, `BackupEmailTemplatesSeeder`, `ChecklistPlantillaSeeder`, `PoliticaSeeder`, `EmpresaSetupSeeder`, `DemoDataSeeder`, `ProductionSeeder`.

---

## 5. Esquema de base de datos (tablas principales)

| Dominio | Tablas |
|---|---|
| Tenants y usuarios | `empresas`, `users`, `model_has_roles`, `model_has_permissions`, `roles`, `permissions` |
| Formatos PSC | `registros`, `secuencias_registro` |
| Helpdesk | `tickets`, `ticket_mensajes`, `ticket_adjuntos`, `ticket_equipo`, `secuencias_ticket` |
| Activos | `equipos`, `equipo_asignaciones` |
| Backups | `backup_programaciones`, `backup_ejecuciones` |
| Políticas / NDAs | `politicas`, `politica_versiones`, `aceptaciones_politica` |
| Checklists | `checklist_plantillas`, `checklist_ejecuciones` |
| Capacitaciones | `capacitaciones`, `capacitacion_asistencias` |
| Email / auditoría | `email_templates`, `notificaciones_email`, `audit_logs` |
| Laravel core | `cache`, `jobs`, `migrations` |

---

## 6. Rutas web públicas/operativas

```
GET  /                            Landing
GET  /docs                        Índice de docs
GET  /docs/{role}                 Docs por rol (super-admin, admin-empresa, usuario, agente-helpdesk, solo-lectura)
GET  /select-empresa              Selector de empresa (super admin)
GET  /tickets/adjuntos/{adjunto}  Descarga adjunto de ticket
GET  /logos/{path}                Logo de tenant
GET  /politicas/{politica}/pdf    PDF de política / NDA
/admin/*                          Panel Filament (multi-tenant)
```

> No existe `routes/api.php` activo — toda la operación es Livewire/Filament server-side.

---

## 7. Stack y entorno

| Capa | Tecnología |
|---|---|
| Backend | Laravel 11 + PHP 8.2 |
| Admin UI | Filament 3 (Livewire 3, Alpine.js, Tailwind) |
| BD | MariaDB 10.11 |
| Cache/Sesiones/Cola | Redis 7 |
| PDF | mPDF 8 |
| Excel | PhpSpreadsheet 1.x |
| Permisos | spatie/laravel-permission |
| Multi-tenancy | Filament tenancy nativo (tenant = `Empresa`) |
| Dev | Docker (PHP/Apache, MariaDB, Redis, MailHog, phpMyAdmin) |
| CI | (pendiente) |
| Tests | Pest/PHPUnit |

---

## 8. Brechas / lo que NO existe aún

- `app/Policies/` vacío — autorización depende de roles spatie + gates Filament; no hay policies por modelo.
- `app/Notifications/` vacío — notificaciones solo vía `SecuriformMail`.
- `routes/api.php` ausente — no hay API REST pública.
- Sin `app/Actions/`.
- Solo 2 enums (`TipoFormato`, `ModalidadCapacitacion`); estados de ticket/registro siguen como strings en BD.
- Solo 2 comandos artisan custom; sin scheduler explícito documentado.

---

## 9. Referencias rápidas

- Stack y reglas: [CLAUDE.md](../CLAUDE.md)
- Workflow: [WORKFLOW.md](../WORKFLOW.md)
- Changelog: [CHANGELOG.md](../CHANGELOG.md)
- Brand guidelines: [docs/brand-guidelines.md](brand-guidelines.md)
