# Changelog

All notable changes to SecuriForm will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2026-04-07

First tracked release. Includes all features built during the initial development phase.

### Added

**Infraestructura**
- Docker development environment (PHP 8.2 + Apache, MariaDB 10.11, Redis 7, MailHog, phpMyAdmin)
- Makefile with development shortcuts (init, up, down, migrate, seed, test, etc.)
- Filament 5.4 admin panel with ShadCN theme
- Vite + Tailwind CSS for frontend assets
- Security headers middleware
- Audit log system with observer pattern
- Upload security with private storage disks

**Multi-tenancy y autenticacion**
- Multi-tenancy by empresa_id with EmpresaScope global scope
- 5 roles via spatie/laravel-permission: super_admin, admin_empresa, usuario, agente_helpdesk, solo_lectura
- Filament login and password reset
- Profile editing page
- Portal branding per empresa (logo, colors, sidebar)

**Formatos de seguridad (F01-F13)**
- Motor de formatos with dynamic JSON forms for 13 security formats
- RegistroResource with automatic numbering (PREFIX-YEAR-SEQ)
- Visual format catalog for registro creation
- PDF and Excel export for registros

**Helpdesk**
- TicketResource with full ticket lifecycle
- Ticket messages with chat bubbles UI
- File attachments for tickets
- Panel de Agente for helpdesk agents
- Ticket reopen within 7-day window
- Ticket-to-registro creation from agent panel

**Politicas y compliance**
- PoliticaResource with versioning, file upload and version history
- NDA support with markdown editor, placeholders and vigencia
- Policy acceptance flow with digital signature canvas
- CompletarDatosNda page for NDA data collection
- NDA PDF generation per firmante
- Wiki de Politicas page (2-column reader for all users)
- EnsurePoliciesAccepted middleware

**Capacitaciones**
- CapacitacionResource CRUD with auto-attendance
- ViewCapacitacion with attendance management
- MisCapacitaciones page for worker training history
- ReporteCapacitaciones with monthly PDF (one page per training)

**Activos (Equipos)**
- EquipoResource CRUD with technical specs
- Equipment assignment, transfer and return actions
- Equipment baja with automatic F13 registro generation
- ChecklistPlantilla management
- Checklist execution per equipo

**Reportes**
- ReporteCapacitaciones (monthly PDF for auditors)
- ReporteIncidencias (monthly PDF with detailed fichas per incident/resolution)

**Email y notificaciones**
- Laravel Mail + Redis queue with corporate email template
- Editable email templates via Filament admin
- Email notification to admin_empresa on F09 incidencia creation
- Notification preferences per user

**Dashboard**
- Role-specific dashboard widgets
- Chart widgets for registros and tickets trends
- Dashboard empresa with quick access grid
- Dashboard agente helpdesk with conditional widgets
- Usuario dashboard with equipos, documentos firmados and quick actions

**Administracion**
- EmpresaResource (CRUD) for super_admin
- UserResource (CRUD) with role-based filtering
- Personal fields: dni, direccion, telefono, puesto
