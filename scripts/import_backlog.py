#!/usr/bin/env python3
"""
import_backlog.py
Importa las 47 user stories del backlog de SecuriForm (Laravel + Filament) a Linear via GraphQL API.

Uso:
    export LINEAR_API_KEY="lin_api_XXXXXXXXXXXXXXXX"
    export LINEAR_TEAM_ID="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
    python3 scripts/import_backlog.py

Para obtener el LINEAR_TEAM_ID:
    Linear → Settings → Members → copiar el ID del team desde la URL
    O: Linear → Settings → API → ver el Team ID en la sección de identificadores

Requiere Python 3.7+ y la librería requests:
    pip install requests
"""

import os
import sys
import json
import time
import requests

# ─────────────────────────────────────────────
# Configuración
# ─────────────────────────────────────────────
API_KEY = os.environ.get("LINEAR_API_KEY", "")
TEAM_ID = os.environ.get("LINEAR_TEAM_ID", "")
API_URL = "https://api.linear.app/graphql"
DELAY_BETWEEN_REQUESTS = 0.3  # segundos, para no saturar la API

HEADERS = {
    "Authorization": API_KEY,
    "Content-Type": "application/json",
}

# Mapeo de prioridad numérica a Linear (0=None, 1=Urgent, 2=High, 3=Medium, 4=Low)
PRIORITY_MAP = {1: 1, 2: 2, 3: 3, 4: 4}

# ─────────────────────────────────────────────
# Labels a crear
# ─────────────────────────────────────────────
LABELS_TO_CREATE = [
    {"name": "chore",        "color": "#95A2B3"},
    {"name": "security",     "color": "#EB5757"},
    {"name": "backend",      "color": "#0B6E99"},
    {"name": "filament",     "color": "#FDAE4B"},
    {"name": "livewire",     "color": "#FB70A9"},
    {"name": "frontend-ui",  "color": "#BB87FC"},
    {"name": "database",     "color": "#F2994A"},
    {"name": "multitenancy", "color": "#219653"},
    {"name": "auth",         "color": "#F2C94C"},
    {"name": "email",        "color": "#2F80ED"},
    {"name": "helpdesk",     "color": "#56CCF2"},
    {"name": "pdf-excel",    "color": "#6FCF97"},
    {"name": "docs",         "color": "#828282"},
    {"name": "docker",       "color": "#2496ED"},
]

# ─────────────────────────────────────────────
# Épicas (Projects en Linear)
# ─────────────────────────────────────────────
EPICS = [
    {"key": "EP-01", "name": "EP-01: Infraestructura Laravel + Docker",  "color": "#0B6E99"},
    {"key": "EP-02", "name": "EP-02: Autenticación y usuarios",          "color": "#F2C94C"},
    {"key": "EP-03", "name": "EP-03: Multiempresa",                      "color": "#219653"},
    {"key": "EP-04", "name": "EP-04: Motor de formatos",                 "color": "#F2994A"},
    {"key": "EP-05", "name": "EP-05: Los 13 formatos",                   "color": "#EB5757"},
    {"key": "EP-06", "name": "EP-06: Exportación (PDF/Excel)",           "color": "#6FCF97"},
    {"key": "EP-07", "name": "EP-07: Helpdesk",                          "color": "#56CCF2"},
    {"key": "EP-08", "name": "EP-08: Notificaciones",                    "color": "#2F80ED"},
    {"key": "EP-09", "name": "EP-09: Dashboard y reportes",              "color": "#BB87FC"},
    {"key": "EP-10", "name": "EP-10: Seguridad y auditoría",             "color": "#EB5757"},
    {"key": "EP-11", "name": "EP-11: Deploy y documentación",            "color": "#828282"},
]

# ─────────────────────────────────────────────
# Backlog completo — 47 user stories
# Stack: Laravel 11 + Filament 3 + Docker
# ─────────────────────────────────────────────
BACKLOG = [
    # ── EP-01: Infraestructura Laravel + Docker ──
    {
        "epic": "EP-01",
        "title": "[EP-01] US-0101 — Docker: entorno de desarrollo completo",
        "priority": 1, "estimate": 5,
        "labels": ["chore", "docker"],
        "description": (
            "Dockerfile (PHP 8.2 + Apache + Composer + Node 20), docker-compose.yml con "
            "5 servicios (app, MariaDB, Redis, phpMyAdmin, MailHog), Makefile con comandos dev.\n\n"
            "**Entregable:** `make init` levanta todo el entorno desde cero."
        ),
    },
    {
        "epic": "EP-01",
        "title": "[EP-01] US-0102 — Laravel: scaffolding + Filament 3 instalado",
        "priority": 1, "estimate": 3,
        "labels": ["chore", "backend", "filament"],
        "description": (
            "Crear proyecto Laravel 11 via `composer create-project`. Instalar Filament 3, "
            "spatie/laravel-permission, y dependencias de exportación (mPDF, PhpSpreadsheet).\n\n"
            "**Entregable:** Panel admin accesible en `/admin` con login."
        ),
    },
    {
        "epic": "EP-01",
        "title": "[EP-01] US-0103 — Migraciones de base de datos",
        "priority": 1, "estimate": 8,
        "labels": ["chore", "database"],
        "description": (
            "Crear migraciones Laravel para todas las tablas del sistema basándose en install.sql "
            "como referencia.\n\n"
            "**Tablas:** empresas, users (extendida), registros, secuencias_registro, tickets, "
            "ticket_mensajes, ticket_adjuntos, notificaciones_email, audit_logs.\n"
            "**Nota:** Las tablas de spatie/permission se crean con su propia migración."
        ),
    },
    {
        "epic": "EP-01",
        "title": "[EP-01] US-0104 — Seeders: roles, permisos y datos de prueba",
        "priority": 1, "estimate": 5,
        "labels": ["chore", "database", "auth"],
        "description": (
            "Seeders para:\n"
            "- 5 roles (super_admin, admin_empresa, usuario, agente_helpdesk, solo_lectura)\n"
            "- Permisos granulares por recurso (crear, ver, editar, eliminar)\n"
            "- 2 empresas de prueba\n"
            "- 5 usuarios de prueba (uno por rol)\n"
            "- Contraseña de prueba: Test2024!"
        ),
    },
    {
        "epic": "EP-01",
        "title": "[EP-01] US-0105 — Modelos Eloquent base con relaciones",
        "priority": 1, "estimate": 5,
        "labels": ["backend", "database"],
        "description": (
            "Crear modelos Eloquent: Empresa, User (extender), Registro, Ticket, TicketMensaje, "
            "TicketAdjunto, AuditLog, NotificacionEmail.\n\n"
            "Definir relaciones (belongsTo, hasMany), casts (JSON, datetime, enums), y $fillable/$guarded."
        ),
    },
    {
        "epic": "EP-01",
        "title": "[EP-01] US-0106 — Tenant isolation con Global Scope",
        "priority": 1, "estimate": 5,
        "labels": ["backend", "multitenancy", "security"],
        "description": (
            "Implementar EmpresaScope (Global Scope) que filtra automáticamente por empresa_id "
            "del usuario autenticado.\n\n"
            "Aplicar en: Registro, Ticket, TicketMensaje, User (para admin_empresa). "
            "Super Admin y agentes bypasean el scope.\n\n"
            "**Test:** Un usuario de empresa A no puede ver datos de empresa B bajo ninguna circunstancia."
        ),
    },

    # ── EP-02: Autenticación y usuarios ──
    {
        "epic": "EP-02",
        "title": "[EP-02] US-0201 — Login Filament con selector de empresa",
        "priority": 1, "estimate": 5,
        "labels": ["filament", "auth", "frontend-ui"],
        "description": (
            "Personalizar la página de login de Filament para incluir un dropdown de empresa activa.\n\n"
            "Validar pertenencia usuario-empresa. Bloqueo tras 5 intentos (15 min). "
            "Redirigir al dashboard según rol.\n\n"
            "**Nota:** Filament ya incluye CSRF automáticamente."
        ),
    },
    {
        "epic": "EP-02",
        "title": "[EP-02] US-0202 — Recuperación de contraseña",
        "priority": 2, "estimate": 3,
        "labels": ["auth", "email"],
        "description": (
            "Usar Laravel Password Reset built-in. Personalizar el email con template corporativo.\n\n"
            "Token SHA-256 con TTL de 1 hora. No revelar si el email existe (mensaje genérico). "
            "MailHog captura el email en desarrollo."
        ),
    },
    {
        "epic": "EP-02",
        "title": "[EP-02] US-0203 — Filament UserResource (CRUD usuarios)",
        "priority": 1, "estimate": 8,
        "labels": ["filament", "backend", "auth"],
        "description": (
            "Filament Resource para gestión de usuarios:\n"
            "- Super Admin: ve todos los usuarios de todas las empresas\n"
            "- Admin Empresa: solo ve y gestiona usuarios de SU empresa\n\n"
            "**Form:** nombre, email, empresa (auto si admin), rol (select filtrado), estado activo/inactivo.\n"
            "**Table:** nombre, email, empresa, rol, último acceso, estado.\n\n"
            "Al crear: generar contraseña temporal, enviar email de bienvenida."
        ),
    },
    {
        "epic": "EP-02",
        "title": "[EP-02] US-0204 — Página de perfil de usuario",
        "priority": 3, "estimate": 3,
        "labels": ["filament", "frontend-ui"],
        "description": (
            "Filament custom page: ver y editar nombre, email. "
            "Mostrar empresa, rol, último acceso, registros del mes.\n\n"
            "Formulario de cambio de contraseña: actual + nueva (mín 8 chars, 1 número) + confirmación."
        ),
    },

    # ── EP-03: Multiempresa ──
    {
        "epic": "EP-03",
        "title": "[EP-03] US-0301 — Filament EmpresaResource (CRUD empresas)",
        "priority": 1, "estimate": 5,
        "labels": ["filament", "backend", "multitenancy"],
        "description": (
            "Filament Resource solo visible para Super Admin.\n\n"
            "**Form:** RUC (11 dígitos, único), razón social, logo (FileUpload), "
            "dirección, email, teléfono, estado activo/inactivo.\n"
            "**Table:** RUC, razón social, usuarios activos, registros del mes, estado."
        ),
    },
    {
        "epic": "EP-03",
        "title": "[EP-03] US-0302 — Detalle de empresa con métricas",
        "priority": 2, "estimate": 5,
        "labels": ["filament", "frontend-ui", "multitenancy"],
        "description": (
            "Filament ViewPage para empresa: datos completos, listado de usuarios, "
            "métricas (registros mes, tickets abiertos, último acceso).\n\n"
            "Relation managers para usuarios y registros de la empresa."
        ),
    },
    {
        "epic": "EP-03",
        "title": "[EP-03] US-0303 — Filament multi-tenancy panel config",
        "priority": 1, "estimate": 5,
        "labels": ["filament", "multitenancy"],
        "description": (
            "Configurar Filament Panel para multi-tenancy nativo:\n"
            "- Tenant model = Empresa\n"
            "- Tenant switcher en sidebar (para Super Admin)\n"
            "- Tenant registration deshabilitado\n"
            "- Middleware de tenant en todas las resources"
        ),
    },

    # ── EP-04: Motor de formatos ──
    {
        "epic": "EP-04",
        "title": "[EP-04] US-0401 — Filament RegistroResource (base)",
        "priority": 1, "estimate": 8,
        "labels": ["filament", "backend", "frontend-ui"],
        "description": (
            "Filament Resource base para registros de seguridad.\n\n"
            "**Table:** número, tipo formato, datos resumen, creado por, fecha, acciones.\n"
            "Filtros: tipo formato (select), fecha desde/hasta, creador. Búsqueda por número.\n\n"
            "Solo muestra registros de la empresa en sesión (Global Scope)."
        ),
    },
    {
        "epic": "EP-04",
        "title": "[EP-04] US-0402 — Formulario dinámico por tipo de formato",
        "priority": 1, "estimate": 8,
        "labels": ["filament", "livewire", "backend"],
        "description": (
            "El formulario de creación/edición cambia según el tipo de formato seleccionado.\n\n"
            "Usar Filament Form Builder con campos condicionales (->visible()). Los campos de cada "
            "formato se definen en un Enum o config PHP.\n\n"
            "Los datos específicos se almacenan en el campo JSON `datos` de la tabla registros."
        ),
    },
    {
        "epic": "EP-04",
        "title": "[EP-04] US-0403 — Generación automática de número de registro",
        "priority": 1, "estimate": 5,
        "labels": ["backend", "database"],
        "description": (
            "Formato: `[PREFIJO]-[AÑO]-[SEC 3 dígitos]`. Ej: INC-2026-004.\n\n"
            "Secuencial por empresa + tipo + año. Usar `DB::transaction()` con `lockForUpdate()` "
            "en tabla secuencias_registro para atomicidad.\n\n"
            "Generado automáticamente en el evento `creating` del modelo Registro."
        ),
    },
    {
        "epic": "EP-04",
        "title": "[EP-04] US-0404 — Soft delete y auditoría de registros",
        "priority": 2, "estimate": 3,
        "labels": ["backend", "security"],
        "description": (
            "Soft delete en modelo Registro (SoftDeletes trait). Solo Admin puede desactivar.\n\n"
            "Filament: botón Desactivar con confirmación, filtro 'Ver inactivos', acción Restaurar.\n\n"
            "Evento de auditoría al desactivar/restaurar."
        ),
    },

    # ── EP-05: Los 13 formatos ──
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0501 — F01: Registro de auditorías realizadas",
        "priority": 2, "estimate": 2,
        "labels": ["filament", "backend"], "psc": "PSC000001",
        "description": (
            "Campos Filament Form: Tipo (Select), Fecha (DatePicker), "
            "Responsable/Proveedor (TextInput), Resultado (Select), "
            "Acciones correctivas (Select Sí/No), Observaciones (Textarea)."
        ),
    },
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0502 — F02: Registro de banco de datos inscritos",
        "priority": 2, "estimate": 2,
        "labels": ["filament", "backend"], "psc": "PSC000001",
        "description": (
            "Campos Filament Form: Nombre BD (TextInput), Código/Registro (TextInput), "
            "Descripción (Textarea), Unidades/Áreas con acceso (TagsInput), "
            "Categoría/Nivel (Select: Pública/Interna/Confidencial/Sensible)."
        ),
    },
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0503 — F03: Prestadores con acceso a datos personales",
        "priority": 2, "estimate": 2,
        "labels": ["filament", "backend"], "psc": "PSC000001 / PSC000002",
        "description": (
            "Campos Filament Form: Prestador (TextInput), Finalidad (Textarea), "
            "Datos facilitados (TagsInput), Fecha contrato (DatePicker), "
            "Vigencia (DatePicker), Observaciones (Textarea)."
        ),
    },
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0504 — F04: Registro para datos sensibles",
        "priority": 2, "estimate": 2,
        "labels": ["filament", "backend"], "psc": "PSC000001 / PSC000-46",
        "description": (
            "Campos Filament Form: Nombre (TextInput), Descripción (Textarea), "
            "Ubicación tipo (Select), Ubicación detalle (TextInput), "
            "Usuarios/Áreas con acceso (TagsInput), Categoría/Nivel (Select)."
        ),
    },
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0505 — F05: Personal autorizado al banco de datos",
        "priority": 2, "estimate": 2,
        "labels": ["filament", "backend"], "psc": "PSC000001",
        "description": (
            "Campos Filament Form: Usuario (TextInput), "
            "Fecha/hora asignación (DateTimePicker), "
            "Banco de datos (Select, cargado desde F02 de la empresa)."
        ),
    },
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0506 — F06: Acceso de soporte no autorizado",
        "priority": 2, "estimate": 2,
        "labels": ["filament", "backend"], "psc": "PSC000001",
        "description": (
            "Campos Filament Form: Descripción del soporte (Textarea), "
            "Persona que accede (TextInput), Fecha de acceso (DatePicker), "
            "Hora de acceso (TimePicker)."
        ),
    },
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0507 — F07: Inventario de soportes",
        "priority": 2, "estimate": 2,
        "labels": ["filament", "backend"], "psc": "PSC000003 / PSC000004",
        "description": (
            "Campos Filament Form: Tipo soporte (Select: HDD interno/externo/USB/Servidor/"
            "Nube/DVD/Expediente físico/Otro), Ubicación (TextInput), "
            "Contenido (Textarea), Fecha inventariado (DatePicker)."
        ),
    },
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0508 — F08: Ingreso y salida de soportes",
        "priority": 2, "estimate": 3,
        "labels": ["filament", "backend"], "psc": "PSC000003",
        "description": (
            "Campos Filament Form: Código soporte (TextInput), Tipo movimiento "
            "(Select: Ingreso/Salida/Devolución), Fecha/hora (DateTimePicker), "
            "ID/Serie (TextInput), Estado (Select), Banco de datos (TextInput), "
            "Contenido (Textarea), Origen/Remitente (TextInput), Destinatario (TextInput), "
            "Finalidad (Textarea), Medio transporte (TextInput), Precauciones (Textarea), "
            "Autoriza (TextInput), Recibe (TextInput).\n\n"
            "Formulario más extenso — usar Filament Sections para organizar visualmente."
        ),
    },
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0509 — F09: Notificación de incidencias",
        "priority": 1, "estimate": 5,
        "labels": ["filament", "backend", "security", "email"], "psc": "PSC000001 / PSC000-25",
        "description": (
            "Campos Filament Form: N° incidencia (auto), Fecha/hora notificación (auto), "
            "Fecha/hora evento (DateTimePicker), Tipo (Select), Sistema/equipo/lugar (TextInput), "
            "Banco de datos (TextInput), Descripción (RichEditor), Medidas inmediatas (Textarea), "
            "Personas notificadas (TagsInput), Impacto potencial (Textarea), "
            "Comunica nombre y cargo (TextInput), Severidad (Select: Alta/Media/Baja).\n\n"
            "**Al guardar:** disparar job de email al Admin Empresa. "
            "Si severidad=Alta: asunto con [URGENTE]."
        ),
    },
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0510 — F10: Resolución de incidencias",
        "priority": 1, "estimate": 5,
        "labels": ["filament", "backend", "security"], "psc": "PSC000-25",
        "description": (
            "Campos Filament Form: N° incidencia (Select, referencia a F09 abiertos), "
            "Fecha/hora cierre (DateTimePicker), Clasificación (Select: Baja/Media/Alta), "
            "Requirió recuperación (Toggle), Medidas adoptadas (Textarea), "
            "Resultado/verificación (Textarea), Ejecutó nombre y cargo (TextInput), "
            "Firma responsable seguridad (TextInput), Acciones preventivas (Textarea)."
        ),
    },
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0511 — F11: Recuperación de datos",
        "priority": 2, "estimate": 3,
        "labels": ["filament", "backend"], "psc": "PSC000001 / PSC000-25",
        "description": (
            "Campos Filament Form: N° recuperación (auto), Incidencia relacionada (TextInput), "
            "Fecha/hora realización (DateTimePicker), Autorización por escrito (Toggle), "
            "Responsable BD que autoriza (TextInput), Proceso realizado (Textarea), "
            "Persona ejecutora (TextInput), Observaciones (Textarea)."
        ),
    },
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0512 — F12: Registro de copias de seguridad",
        "priority": 2, "estimate": 2,
        "labels": ["filament", "backend"], "psc": "PSC000003 / PSC000-15",
        "description": (
            "Campos Filament Form: Nombre backup (TextInput), "
            "Descripción contenido (Textarea), Fecha copia (DatePicker), "
            "Periodicidad (Select: Diaria/Semanal/Quincenal/Mensual/Trimestral/Puntual)."
        ),
    },
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0513 — F13: Registro de destrucción de activos",
        "priority": 2, "estimate": 3,
        "labels": ["filament", "backend"], "psc": "PSC000003 / PSC000004",
        "description": (
            "Campos Filament Form: N° (auto), Fecha destrucción (DatePicker), "
            "Descripción activo (Textarea), Método (Select: Borrado seguro/Destrucción física/"
            "Desmagnetización/Incineración/Trituración/Proveedor certificado), "
            "Responsable (TextInput), Autoriza (TextInput), Próxima revisión (DatePicker)."
        ),
    },

    # ── EP-06: Exportación ──
    {
        "epic": "EP-06",
        "title": "[EP-06] US-0601 — Exportar registro individual a PDF",
        "priority": 2, "estimate": 5,
        "labels": ["pdf-excel", "backend"],
        "description": (
            "Filament Action en RegistroResource → genera PDF A4 con mPDF.\n\n"
            "Contenido: logo empresa, razón social, nombre formato, política PSC, "
            "N° registro, todos los campos, pie con creador + fecha + fecha exportación.\n\n"
            "Implementar como Filament Action o BulkAction. Tiempo máx: 5 segundos."
        ),
    },
    {
        "epic": "EP-06",
        "title": "[EP-06] US-0602 — Exportar listado filtrado a Excel",
        "priority": 3, "estimate": 5,
        "labels": ["pdf-excel", "backend"],
        "description": (
            "Filament Action en la tabla de RegistroResource → exporta .xlsx con PhpSpreadsheet.\n\n"
            "Exportar listado con filtros aplicados. Cabecera con empresa y formato. "
            "Máx 5,000 registros. Usar Laravel Job si supera 1,000 registros."
        ),
    },
    {
        "epic": "EP-06",
        "title": "[EP-06] US-0603 — Exportar ticket completo a PDF",
        "priority": 4, "estimate": 3,
        "labels": ["pdf-excel", "helpdesk"],
        "description": (
            "PDF con hilo completo del ticket. Notas internas visibles solo si descarga un agente. "
            "Implementar como Filament Action en TicketResource."
        ),
    },
    {
        "epic": "EP-06",
        "title": "[EP-06] US-0604 — Reporte mensual PDF de incidencias",
        "priority": 3, "estimate": 3,
        "labels": ["pdf-excel", "backend"],
        "description": (
            "Filament custom page: selección de mes/año → genera PDF con portada, "
            "tabla F09 (incidencias), tabla F10 (resoluciones), resumen estadístico. "
            "Firma al pie."
        ),
    },

    # ── EP-07: Helpdesk ──
    {
        "epic": "EP-07",
        "title": "[EP-07] US-0701 — Filament TicketResource (crear ticket)",
        "priority": 1, "estimate": 5,
        "labels": ["filament", "helpdesk", "frontend-ui"],
        "description": (
            "Filament Resource para tickets.\n\n"
            "**Form creación:** asunto (TextInput), descripción (RichEditor), "
            "prioridad (Select: Baja/Media/Alta/Urgente), categoría (Select: Consulta/"
            "Problema técnico/Error en registro/Solicitud acceso/Otro).\n\n"
            "Estado inicial: nuevo. Dispatch job: email confirmación al usuario + notificación a agentes."
        ),
    },
    {
        "epic": "EP-07",
        "title": "[EP-07] US-0702 — Listado de tickets (usuario/admin empresa)",
        "priority": 2, "estimate": 3,
        "labels": ["filament", "helpdesk"],
        "description": (
            "Filament Table con filtros: estado, prioridad, fecha. "
            "Badge en navigation de Filament con count de tickets abiertos.\n\n"
            "Usuario ve solo sus tickets. Admin empresa ve todos los de su empresa."
        ),
    },
    {
        "epic": "EP-07",
        "title": "[EP-07] US-0703 — Hilo de conversación del ticket",
        "priority": 1, "estimate": 8,
        "labels": ["filament", "livewire", "helpdesk"],
        "description": (
            "Filament custom ViewPage o RelationManager para mensajes del ticket.\n\n"
            "Mensajes cronológicos con diferencia visual usuario/agente. "
            "Usuarios NO ven notas internas. Caja de respuesta Livewire al pie con envío reactivo.\n\n"
            "Dispatch job de email al responder."
        ),
    },
    {
        "epic": "EP-07",
        "title": "[EP-07] US-0704 — Panel de agente helpdesk",
        "priority": 1, "estimate": 8,
        "labels": ["filament", "helpdesk", "frontend-ui"],
        "description": (
            "Filament custom page con layout split: lista tickets (izq) + detalle (der) "
            "usando Livewire.\n\n"
            "Filtros: empresa, estado, prioridad, agente asignado. Counters: sin asignar, "
            "nuevos hoy. Agentes ven tickets de TODAS las empresas."
        ),
    },
    {
        "epic": "EP-07",
        "title": "[EP-07] US-0705 — Gestión del ticket por el agente",
        "priority": 1, "estimate": 5,
        "labels": ["filament", "helpdesk", "backend"],
        "description": (
            "Filament Actions en TicketResource:\n"
            "- Asignar/reasignar agente (Select)\n"
            "- Cambiar estado (nuevo → en_revisión → esperando_usuario → resuelto → cerrado)\n"
            "- Agregar nota interna (solo visible para agentes)\n"
            "- Cambiar prioridad\n"
            "- Cerrar con comentario obligatorio\n\n"
            "Cada acción dispara Filament Notification + evento de auditoría."
        ),
    },
    {
        "epic": "EP-07",
        "title": "[EP-07] US-0706 — Adjuntos en tickets",
        "priority": 3, "estimate": 3,
        "labels": ["filament", "helpdesk", "security"],
        "description": (
            "Filament FileUpload en el formulario de respuesta.\n\n"
            "Tipos permitidos: jpg, png, pdf, docx. Máx 5 MB. "
            "Storage en `storage/app/tickets/`. Máx 3 por mensaje. "
            "Filament valida MIME automáticamente, agregar validación server-side adicional."
        ),
    },
    {
        "epic": "EP-07",
        "title": "[EP-07] US-0707 — Reapertura de ticket resuelto",
        "priority": 3, "estimate": 2,
        "labels": ["helpdesk", "backend"],
        "description": (
            "Filament Action 'Reabrir' visible solo dentro de ventana de 7 días post-cierre.\n\n"
            "Al reabrir: estado → en_revisión, dispatch job email al agente. "
            "Historial de reaperturas visible en el hilo."
        ),
    },

    # ── EP-08: Notificaciones ──
    {
        "epic": "EP-08",
        "title": "[EP-08] US-0801 — Laravel Mail + Queue de emails",
        "priority": 2, "estimate": 3,
        "labels": ["email", "backend"],
        "description": (
            "Configurar Laravel Mail con SMTP (MailHog en dev). Queue driver: Redis.\n\n"
            "Crear Mailable base con template corporativo. Job de envío con retry (3 intentos). "
            "Failed jobs logueados sin interrumpir flujo."
        ),
    },
    {
        "epic": "EP-08",
        "title": "[EP-08] US-0802 — Templates Blade de email",
        "priority": 3, "estimate": 3,
        "labels": ["email", "frontend-ui"],
        "description": (
            "Mailables Laravel con Blade templates para:\n"
            "- Bienvenida (nuevo usuario)\n"
            "- Recuperar contraseña\n"
            "- Nuevo ticket creado\n"
            "- Respuesta en ticket\n"
            "- Ticket resuelto\n"
            "- Nueva incidencia (F09)\n\n"
            "Todos con enlace directo al elemento en la app."
        ),
    },
    {
        "epic": "EP-08",
        "title": "[EP-08] US-0803 — Preferencias de notificación por usuario",
        "priority": 4, "estimate": 2,
        "labels": ["email", "filament"],
        "description": (
            "Campo JSON `preferencias_notificacion` en tabla users.\n\n"
            "Filament Form en perfil: toggles para activar/desactivar "
            "notif. tickets, notif. incidencias. "
            "Notificaciones de cuenta (contraseña, bienvenida) no desactivables."
        ),
    },
    {
        "epic": "EP-08",
        "title": "[EP-08] US-0804 — Email de incidencia al Admin Empresa",
        "priority": 2, "estimate": 2,
        "labels": ["email", "security"],
        "description": (
            "Observer en modelo Registro: al crear F09, dispatch IncidenciaNotification al Admin Empresa.\n\n"
            "Contenido: N° incidencia, tipo, severidad, enlace. "
            "Si severidad=Alta: asunto con [URGENTE]."
        ),
    },

    # ── EP-09: Dashboard y reportes ──
    {
        "epic": "EP-09",
        "title": "[EP-09] US-0901 — Dashboard empresa (Filament widgets)",
        "priority": 2, "estimate": 5,
        "labels": ["filament", "frontend-ui"],
        "description": (
            "Filament StatsOverviewWidget: 4 cards con métricas del mes "
            "(registros creados, tickets abiertos, incidencias, usuarios activos).\n\n"
            "Filament TableWidget: últimos 5 registros. Accesos rápidos a formatos más usados.\n\n"
            "Visible para usuario y admin_empresa."
        ),
    },
    {
        "epic": "EP-09",
        "title": "[EP-09] US-0902 — Gráficos de registros (Filament Charts)",
        "priority": 3, "estimate": 3,
        "labels": ["filament", "frontend-ui"],
        "description": (
            "Filament ChartWidget: gráfico de barras con registros por tipo, últimos 6 meses.\n\n"
            "X=mes, Y=cantidad, colores por tipo de formato. Filtrable por rango de fechas.\n\n"
            "**Nota:** Filament incluye soporte para Chart.js nativo, no necesita CDN externo."
        ),
    },
    {
        "epic": "EP-09",
        "title": "[EP-09] US-0903 — Dashboard agente helpdesk",
        "priority": 3, "estimate": 3,
        "labels": ["filament", "helpdesk", "frontend-ui"],
        "description": (
            "Filament dashboard condicional para rol agente_helpdesk.\n\n"
            "Widgets: tickets por estado (StatsOverview), tiempo promedio de respuesta del mes, "
            "mis tickets vs sin asignar, últimas 5 actualizaciones globales (TableWidget)."
        ),
    },

    # ── EP-10: Seguridad y auditoría ──
    {
        "epic": "EP-10",
        "title": "[EP-10] US-1001 — Middleware de seguridad HTTP",
        "priority": 1, "estimate": 2,
        "labels": ["security", "backend"],
        "description": (
            "Laravel Middleware que agrega headers:\n"
            "- X-Frame-Options: DENY\n"
            "- X-Content-Type-Options: nosniff\n"
            "- Referrer-Policy: strict-origin-when-cross-origin\n"
            "- Content-Security-Policy (adaptado a Filament + Livewire)\n"
            "- Permissions-Policy\n\n"
            "Registrar como middleware global.\n\n"
            "**Nota:** CSRF y XSS ya los maneja Laravel/Blade automáticamente."
        ),
    },
    {
        "epic": "EP-10",
        "title": "[EP-10] US-1002 — Sistema de auditoría (AuditLog)",
        "priority": 1, "estimate": 5,
        "labels": ["security", "backend", "filament"],
        "description": (
            "Modelo AuditLog con Observer pattern en modelos principales.\n\n"
            "Registra: login/logout, CRUD registros, cambios estado ticket, gestión usuarios. "
            "Campos: acción, entidad, datos anteriores/nuevos (JSON), IP, user_agent.\n\n"
            "Filament Resource AuditLogResource (solo lectura, solo Super Admin) "
            "con filtros por entidad, acción, usuario y fecha."
        ),
    },
    {
        "epic": "EP-10",
        "title": "[EP-10] US-1003 — Seguridad de uploads",
        "priority": 2, "estimate": 2,
        "labels": ["security", "backend"],
        "description": (
            "Configurar Filament FileUpload con:\n"
            "- Validación MIME type en servidor (no solo extensión)\n"
            "- Rename a UUID (Filament lo hace por defecto)\n"
            "- Storage en `storage/app/` (fuera de webroot)\n"
            "- Disco privado para archivos sensibles\n\n"
            "Agregar middleware que bloquea ejecución PHP en storage."
        ),
    },

    # ── EP-11: Deploy y documentación ──
    {
        "epic": "EP-11",
        "title": "[EP-11] US-1101 — Docker production config",
        "priority": 2, "estimate": 5,
        "labels": ["docker", "chore"],
        "description": (
            "Dockerfile.prod optimizado: multi-stage build, OPcache habilitado, "
            "assets pre-compilados, sin dev dependencies.\n\n"
            "docker-compose.prod.yml: sin phpMyAdmin ni MailHog, con HTTPS/reverse proxy config, "
            "environment production.\n\n"
            "Healthchecks y restart policies."
        ),
    },
    {
        "epic": "EP-11",
        "title": "[EP-11] US-1102 — README y documentación de despliegue",
        "priority": 2, "estimate": 3,
        "labels": ["docs"],
        "description": (
            "README actualizado: requisitos (Docker o PHP 8.2+), pasos de instalación (make init), "
            "config email, estructura Laravel + Filament, roles, formatos.\n\n"
            "Guía de deployment: Docker production, hosting compartido con PHP, "
            "checklist seguridad producción."
        ),
    },
    {
        "epic": "EP-11",
        "title": "[EP-11] US-1103 — Guía de usuario final",
        "priority": 3, "estimate": 3,
        "labels": ["docs"],
        "description": (
            "Guía en PDF/MD con capturas del panel Filament: login, crear registros, "
            "consultar registros, exportar, abrir ticket, seguimiento ticket.\n\n"
            "Una sección por rol. Incluir FAQ y solución de problemas comunes."
        ),
    },
]


# ─────────────────────────────────────────────
# Funciones auxiliares
# ─────────────────────────────────────────────

def graphql(query: str, variables: dict = None) -> dict:
    payload = {"query": query}
    if variables:
        payload["variables"] = variables
    response = requests.post(API_URL, headers=HEADERS, json=payload, timeout=15)
    response.raise_for_status()
    return response.json()


def get_existing_labels() -> dict:
    """Retorna dict {name_lower: id} de labels existentes."""
    result = graphql('{ team(id: "%s") { labels { nodes { id name } } } }' % TEAM_ID)
    nodes = result.get("data", {}).get("team", {}).get("labels", {}).get("nodes", [])
    return {l["name"].lower(): l["id"] for l in nodes}


def create_labels(existing: dict) -> dict:
    """Crea labels que faltan. Retorna dict completo {name_lower: id}."""
    label_map = dict(existing)
    for label in LABELS_TO_CREATE:
        if label["name"].lower() in label_map:
            print(f"  ⏭  Label '{label['name']}' ya existe")
            continue
        mutation = """
        mutation CreateLabel($input: IssueLabelCreateInput!) {
            issueLabelCreate(input: $input) {
                success
                issueLabel { id name }
            }
        }
        """
        variables = {
            "input": {
                "teamId": TEAM_ID,
                "name": label["name"],
                "color": label["color"],
            }
        }
        result = graphql(mutation, variables)
        created = result.get("data", {}).get("issueLabelCreate", {})
        if created.get("success"):
            lbl = created["issueLabel"]
            label_map[lbl["name"].lower()] = lbl["id"]
            print(f"  ✅ Label '{lbl['name']}' creado")
        else:
            print(f"  ❌ Error creando label '{label['name']}'")
        time.sleep(DELAY_BETWEEN_REQUESTS)
    return label_map


def create_projects() -> dict:
    """Crea Projects (épicas) en Linear. Retorna dict {epic_key: project_id}."""
    project_map = {}
    for epic in EPICS:
        mutation = """
        mutation CreateProject($input: ProjectCreateInput!) {
            projectCreate(input: $input) {
                success
                project { id name }
            }
        }
        """
        variables = {
            "input": {
                "teamIds": [TEAM_ID],
                "name": epic["name"],
                "color": epic["color"],
            }
        }
        result = graphql(mutation, variables)
        created = result.get("data", {}).get("projectCreate", {})
        if created.get("success"):
            proj = created["project"]
            project_map[epic["key"]] = proj["id"]
            print(f"  ✅ Épica '{proj['name']}' creada")
        else:
            errors = result.get("errors", [])
            err = errors[0].get("message", "Error") if errors else "Error"
            print(f"  ❌ Error épica '{epic['name']}': {err}")
        time.sleep(DELAY_BETWEEN_REQUESTS)
    return project_map


def get_workflow_state_id(state_name: str) -> str | None:
    """Obtiene el ID de un workflow state por nombre."""
    result = graphql('{ team(id: "%s") { states { nodes { id name } } } }' % TEAM_ID)
    states = result.get("data", {}).get("team", {}).get("states", {}).get("nodes", [])
    for s in states:
        if s["name"].lower() == state_name.lower():
            return s["id"]
    return None


def import_stories(label_map: dict, project_map: dict, backlog_state_id: str):
    """Importa las user stories con labels y proyecto asignado."""
    created = 0
    errors = 0
    current_epic = ""

    for story in BACKLOG:
        epic = story["epic"]
        if epic != current_epic:
            print(f"\n── {epic} {'─' * 50}")
            current_epic = epic

        # Resolver label IDs
        label_ids = []
        for lname in story.get("labels", []):
            lid = label_map.get(lname.lower())
            if lid:
                label_ids.append(lid)

        # Construir descripción
        desc_parts = []
        psc = story.get("psc", "")
        if psc:
            desc_parts.append(f"**Política PSC:** {psc}")
        if story.get("description"):
            desc_parts.append(story["description"])
        description = "\n\n".join(desc_parts)

        mutation = """
        mutation CreateIssue($input: IssueCreateInput!) {
            issueCreate(input: $input) {
                success
                issue { id identifier title url }
            }
        }
        """
        input_data = {
            "teamId": TEAM_ID,
            "title": story["title"],
            "priority": PRIORITY_MAP.get(story.get("priority", 3), 3),
            "estimate": story.get("estimate", 3),
            "description": description,
            "stateId": backlog_state_id,
        }

        if label_ids:
            input_data["labelIds"] = label_ids

        project_id = project_map.get(epic)
        if project_id:
            input_data["projectId"] = project_id

        result = graphql(mutation, {"input": input_data})
        issue_create = result.get("data", {}).get("issueCreate", {})

        if issue_create.get("success"):
            issue = issue_create["issue"]
            print(f"  ✅ {issue['identifier']} — {story['title'][:55]}")
            created += 1
        else:
            errs = result.get("errors", [])
            err_msg = errs[0].get("message", "Error") if errs else "Error"
            print(f"  ❌ {err_msg} — {story['title'][:40]}")
            errors += 1

        time.sleep(DELAY_BETWEEN_REQUESTS)

    return created, errors


def main():
    if not API_KEY:
        print("❌ Falta LINEAR_API_KEY. Exporta la variable de entorno:")
        print('   export LINEAR_API_KEY="lin_api_XXXXXXXXXXXXXXXX"')
        sys.exit(1)

    if not TEAM_ID:
        print("❌ Falta LINEAR_TEAM_ID. Exporta la variable de entorno:")
        print('   export LINEAR_TEAM_ID="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"')
        sys.exit(1)

    # Verificar conexión
    print("🔗 Verificando conexión con Linear...")
    test = graphql("query { viewer { id name } }")
    if "errors" in test:
        print(f"❌ Error: {test['errors'][0]['message']}")
        sys.exit(1)
    viewer = test.get("data", {}).get("viewer", {})
    print(f"✅ Conectado como: {viewer.get('name', 'Desconocido')}")
    print(f"📋 Team ID: {TEAM_ID}\n")

    # Paso 1: Labels
    print("=" * 60)
    print("📌 PASO 1: Creando labels...")
    print("=" * 60)
    existing_labels = get_existing_labels()
    label_map = create_labels(existing_labels)

    # Paso 2: Épicas (Projects)
    print(f"\n{'=' * 60}")
    print("📂 PASO 2: Creando épicas (Projects)...")
    print("=" * 60)
    project_map = create_projects()

    # Paso 3: Obtener state "Backlog"
    backlog_state_id = get_workflow_state_id("Backlog")
    if not backlog_state_id:
        print("❌ No se encontró el estado 'Backlog'")
        sys.exit(1)
    print(f"\n📋 Estado Backlog: {backlog_state_id}")

    # Paso 4: Importar stories
    print(f"\n{'=' * 60}")
    print(f"📥 PASO 3: Importando {len(BACKLOG)} user stories...")
    print("=" * 60)
    created, errors = import_stories(label_map, project_map, backlog_state_id)

    # Resumen
    print(f"\n{'=' * 60}")
    print("🏁 IMPORTACIÓN COMPLETADA")
    print(f"  ✅ Creadas:  {created}")
    print(f"  ❌ Errores:  {errors}")
    print(f"  📊 Total:    {len(BACKLOG)}")
    print(f"\n📌 Labels:   {len(label_map)}")
    print(f"📂 Épicas:   {len(project_map)}")
    print("=" * 60)


if __name__ == "__main__":
    main()
