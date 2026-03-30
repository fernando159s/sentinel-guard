#!/usr/bin/env python3
"""
import_backlog.py
Importa las 54 user stories del backlog de SecuriForm a Linear via GraphQL API.

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
# Backlog completo — 54 user stories
# ─────────────────────────────────────────────
BACKLOG = [
    # ── EP-01: Infraestructura base ──────────────────────────────────────
    {
        "epic": "EP-01",
        "title": "[EP-01] US-0101 — Estructura de directorios del proyecto",
        "priority": 1, "estimate": 3,
        "labels": ["chore", "backend-php"],
        "description": (
            "Crear el árbol de carpetas del proyecto según la arquitectura definida en CLAUDE.md.\n\n"
            "**Entregable:** Repositorio con estructura `/config`, `/app`, `/public`, `/uploads`, `/vendor`, `/scripts`."
        ),
    },
    {
        "epic": "EP-01",
        "title": "[EP-01] US-0102 — Script SQL de instalación (install.sql)",
        "priority": 1, "estimate": 5,
        "labels": ["chore", "database"],
        "description": (
            "Script idempotente que crea las 10 tablas del sistema con índices y datos iniciales.\n\n"
            "**Tablas:** empresas, usuarios, sesiones, tokens_recuperacion, registros, "
            "secuencias_registro, tickets, ticket_mensajes, ticket_adjuntos, "
            "secuencias_ticket, notificaciones_email, logs_auditoria."
        ),
    },
    {
        "epic": "EP-01",
        "title": "[EP-01] US-0103 — Capa de acceso a datos (Database.php)",
        "priority": 1, "estimate": 5,
        "labels": ["chore", "backend-php", "database"],
        "description": (
            "Clase Singleton que encapsula la conexión PDO.\n\n"
            "**Métodos:** `query($sql, $params)`, `execute($sql, $params)`, `lastInsertId()`.\n"
            "**Regla:** 100% prepared statements, nunca concatenación de variables."
        ),
    },
    {
        "epic": "EP-01",
        "title": "[EP-01] US-0104 — Middleware de tenant isolation (empresa_id)",
        "priority": 1, "estimate": 5,
        "labels": ["chore", "backend-php", "multitenancy", "security"],
        "description": (
            "Garantizar que ningún usuario pueda acceder a datos de otra empresa.\n\n"
            "**Implementación:** El `empresa_id` de la sesión se inyecta como parámetro "
            "obligatorio en todos los modelos. Test: acceso cross-tenant retorna 403."
        ),
    },
    {
        "epic": "EP-01",
        "title": "[EP-01] US-0105 — Router y controlador base",
        "priority": 1, "estimate": 3,
        "labels": ["chore", "backend-php"],
        "description": (
            "Router PHP que mapea URLs a controladores. Clase Controller base con "
            "métodos `view()`, `redirect()`, `json()`, `authorize()`.\n\n"
            "El `.htaccess` redirige todo a `public/index.php`."
        ),
    },

    # ── EP-02: Autenticación y usuarios ─────────────────────────────────
    {
        "epic": "EP-02",
        "title": "[EP-02] US-0201 — Pantalla de login con selector de empresa",
        "priority": 1, "estimate": 5,
        "labels": ["feature", "auth", "frontend-ui"],
        "description": (
            "Formulario de login: dropdown de empresa → email → contraseña.\n\n"
            "Validar pertenencia usuario-empresa. Bloqueo tras 5 intentos (15 min). "
            "Token CSRF. Redirigir al dashboard según rol tras login exitoso."
        ),
    },
    {
        "epic": "EP-02",
        "title": "[EP-02] US-0202 — Gestión de sesión y logout",
        "priority": 1, "estimate": 3,
        "labels": ["feature", "auth", "backend-php"],
        "description": (
            "Sesión PHP con expiración a 8 horas de inactividad. "
            "Logout destruye sesión completamente. "
            "Log de cada login/logout con IP y timestamp."
        ),
    },
    {
        "epic": "EP-02",
        "title": "[EP-02] US-0203 — Recuperación de contraseña por email",
        "priority": 2, "estimate": 5,
        "labels": ["feature", "auth", "email"],
        "description": (
            "Flujo: formulario → generar token SHA-256 (TTL 1h) → email con enlace → "
            "formulario nueva contraseña → invalidar token.\n\n"
            "No revelar si el email existe (mensaje genérico)."
        ),
    },
    {
        "epic": "EP-02",
        "title": "[EP-02] US-0204 — CRUD de usuarios (Admin Empresa)",
        "priority": 2, "estimate": 8,
        "labels": ["feature", "backend-php", "frontend-ui"],
        "description": (
            "Admin Empresa puede crear, editar y desactivar usuarios de SU empresa.\n\n"
            "Al crear: generar contraseña temporal, enviar email de bienvenida. "
            "No puede crear usuarios `super_admin` ni `agente_helpdesk`."
        ),
    },
    {
        "epic": "EP-02",
        "title": "[EP-02] US-0205 — Cambio de contraseña propio",
        "priority": 3, "estimate": 2,
        "labels": ["feature", "auth"],
        "description": "Formulario: contraseña actual + nueva (mín. 8 chars, 1 número) + confirmación.",
    },
    {
        "epic": "EP-02",
        "title": "[EP-02] US-0206 — Perfil de usuario",
        "priority": 4, "estimate": 3,
        "labels": ["feature", "frontend-ui"],
        "description": "Ver y editar perfil. Mostrar: nombre, email, empresa, rol, último acceso, registros del mes.",
    },

    # ── EP-03: Multiempresa ──────────────────────────────────────────────
    {
        "epic": "EP-03",
        "title": "[EP-03] US-0301 — CRUD de empresas (Super Admin)",
        "priority": 1, "estimate": 8,
        "labels": ["feature", "backend-php", "multitenancy"],
        "description": (
            "Super Admin gestiona empresas: crear (RUC 11 dígitos, razón social, logo, dirección, email, tel), "
            "editar, activar/desactivar.\n\n"
            "Logo almacenado como `uploads/logos/[empresa_id].[ext]`."
        ),
    },
    {
        "epic": "EP-03",
        "title": "[EP-03] US-0302 — Vista de empresa para Super Admin",
        "priority": 2, "estimate": 5,
        "labels": ["feature", "frontend-ui"],
        "description": "Detalle de empresa: datos, listado de usuarios, métricas (registros mes, tickets abiertos).",
    },
    {
        "epic": "EP-03",
        "title": "[EP-03] US-0303 — Panel Super Admin global",
        "priority": 3, "estimate": 5,
        "labels": ["feature", "frontend-ui"],
        "description": "Dashboard global: empresas activas/inactivas, usuarios activos, registros hoy, tickets abiertos.",
    },

    # ── EP-04: Motor de formatos ─────────────────────────────────────────
    {
        "epic": "EP-04",
        "title": "[EP-04] US-0401 — Listado de registros por formato",
        "priority": 1, "estimate": 8,
        "labels": ["feature", "backend-php", "frontend-ui"],
        "description": (
            "Listado paginado (20/página) de registros por tipo de formato. "
            "Filtros: fecha desde/hasta, usuario creador, estado. "
            "Búsqueda por número o texto. Solo muestra registros de la empresa en sesión."
        ),
    },
    {
        "epic": "EP-04",
        "title": "[EP-04] US-0402 — Crear nuevo registro",
        "priority": 1, "estimate": 8,
        "labels": ["feature", "backend-php", "frontend-ui"],
        "description": (
            "Formulario dinámico según tipo de formato (config en FormatsConfig.php). "
            "Número de registro generado automáticamente. Validación en servidor. CSRF token."
        ),
    },
    {
        "epic": "EP-04",
        "title": "[EP-04] US-0403 — Ver detalle de registro",
        "priority": 2, "estimate": 5,
        "labels": ["feature", "frontend-ui"],
        "description": "Vista de detalle: todos los campos, creado por, última modificación, botones Editar y Exportar PDF.",
    },
    {
        "epic": "EP-04",
        "title": "[EP-04] US-0404 — Editar registro existente",
        "priority": 2, "estimate": 5,
        "labels": ["feature", "backend-php"],
        "description": (
            "Solo puede editar el creador o Admin. Precargar valores actuales. "
            "Actualizar `modificado_por` y `fecha_modificacion`. Log de auditoría."
        ),
    },
    {
        "epic": "EP-04",
        "title": "[EP-04] US-0405 — Desactivar registro (soft delete)",
        "priority": 3, "estimate": 3,
        "labels": ["feature", "backend-php"],
        "description": "Solo Admin puede desactivar. Requiere confirmación. Aparece en 'Ver inactivos'. Log de auditoría.",
    },
    {
        "epic": "EP-04",
        "title": "[EP-04] US-0406 — Generación automática de número de registro",
        "priority": 2, "estimate": 5,
        "labels": ["chore", "backend-php", "database"],
        "description": (
            "Formato: `[PREFIJO]-[AÑO]-[SEC 3 dígitos]`. Ej: INC-2026-004.\n\n"
            "Secuencial por empresa + tipo + año. Atómico con `SELECT ... FOR UPDATE` "
            "en tabla `secuencias_registro` para evitar race conditions."
        ),
    },

    # ── EP-05: Los 13 formatos ───────────────────────────────────────────
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0501 — F01: Registro de auditorías realizadas",
        "priority": 2, "estimate": 3,
        "labels": ["feature", "backend-php"],
        "psc": "PSC000001",
        "description": "Campos: Tipo (select), Fecha, Responsable/Proveedor, Resultado (select), Acciones correctivas (select Sí/No), Observaciones.",
    },
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0502 — F02: Registro de banco de datos inscritos",
        "priority": 2, "estimate": 3,
        "labels": ["feature", "backend-php"],
        "psc": "PSC000001",
        "description": "Campos: Nombre BD, Código/Registro, Descripción, Unidades/Áreas con acceso, Categoría/Nivel (select: Pública/Interna/Confidencial/Sensible).",
    },
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0503 — F03: Prestadores con acceso a datos personales",
        "priority": 2, "estimate": 3,
        "labels": ["feature", "backend-php"],
        "psc": "PSC000001 / PSC000002",
        "description": "Campos: Prestador del servicio, Finalidad, Datos facilitados (tipo), Fecha de contrato, Vigencia, Observaciones.",
    },
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0504 — F04: Registro para datos sensibles",
        "priority": 2, "estimate": 3,
        "labels": ["feature", "backend-php"],
        "psc": "PSC000001 / PSC000-46",
        "description": "Campos: Nombre, Descripción, Ubicación tipo (select), Ubicación detalle, Usuarios/Áreas con acceso, Categoría/Nivel (select).",
    },
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0505 — F05: Personal autorizado al banco de datos",
        "priority": 2, "estimate": 2,
        "labels": ["feature", "backend-php"],
        "psc": "PSC000001",
        "description": "Campos: Usuario, Fecha y hora de asignación, Banco de datos.",
    },
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0506 — F06: Acceso de soporte no autorizado",
        "priority": 2, "estimate": 2,
        "labels": ["feature", "backend-php"],
        "psc": "PSC000001",
        "description": "Campos: Descripción del soporte, Persona que accede, Fecha de acceso, Hora de acceso.",
    },
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0507 — F07: Inventario de soportes",
        "priority": 2, "estimate": 3,
        "labels": ["feature", "backend-php"],
        "psc": "PSC000003 / PSC000004",
        "description": "Campos: Tipo de soporte (select: HDD interno/externo/USB/Servidor/Nube/DVD/Expediente físico/Otro), Ubicación, Contenido, Fecha de inventariado.",
    },
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0508 — F08: Ingreso y salida de soportes",
        "priority": 2, "estimate": 5,
        "labels": ["feature", "backend-php"],
        "psc": "PSC000003",
        "description": (
            "Campos: Código soporte, Tipo movimiento (Ingreso/Salida/Devolución), Fecha/hora, "
            "ID/Serie, Estado (select), Banco de datos, Contenido, Origen/Remitente, "
            "Destinatario, Finalidad, Medio de transporte, Precauciones, Autoriza, Recibe."
        ),
    },
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0509 — F09: Notificación de incidencias",
        "priority": 1, "estimate": 8,
        "labels": ["feature", "backend-php", "security"],
        "psc": "PSC000001 / PSC000-25",
        "description": (
            "Campos: N° incidencia (auto), Fecha/hora notificación (auto), Fecha/hora evento, "
            "Tipo (select: Acceso no autorizado/Pérdida de datos/Malware/Phishing/Fallo sistema/Fuga info/Ransomware/Otro), "
            "Sistema/equipo/lugar, Banco de datos, Descripción, Medidas inmediatas, "
            "Personas notificadas, Impacto potencial, Comunica (nombre y cargo), "
            "Severidad (select: Alta/Media/Baja).\n\n"
            "**Al guardar:** disparar email de notificación al Admin Empresa."
        ),
    },
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0510 — F10: Resolución de incidencias",
        "priority": 1, "estimate": 8,
        "labels": ["feature", "backend-php", "security"],
        "psc": "PSC000-25",
        "description": (
            "Campos: N° incidencia (referencia a F09), Fecha/hora cierre, "
            "Clasificación (select: Baja/Media/Alta), Requirió recuperación (radio: Sí/No), "
            "Medidas adoptadas, Resultado/verificación, Ejecutó (nombre y cargo), "
            "Firma responsable de seguridad, Acciones preventivas."
        ),
    },
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0511 — F11: Recuperación de datos",
        "priority": 2, "estimate": 5,
        "labels": ["feature", "backend-php"],
        "psc": "PSC000001 / PSC000-25",
        "description": (
            "Campos: N° recuperación (auto), Incidencia relacionada (texto), "
            "Fecha/hora realización, Autorización por escrito (radio: Sí/No), "
            "Responsable BD que autoriza, Proceso realizado, Persona ejecutora, Observaciones."
        ),
    },
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0512 — F12: Registro de copias de seguridad",
        "priority": 2, "estimate": 3,
        "labels": ["feature", "backend-php"],
        "psc": "PSC000003 / PSC000-15",
        "description": "Campos: Nombre backup, Descripción del contenido, Fecha de copia, Periodicidad (select: Diaria/Semanal/Quincenal/Mensual/Trimestral/Puntual).",
    },
    {
        "epic": "EP-05",
        "title": "[EP-05] US-0513 — F13: Registro de destrucción de activos",
        "priority": 2, "estimate": 5,
        "labels": ["feature", "backend-php"],
        "psc": "PSC000003 / PSC000004",
        "description": (
            "Campos: N° (auto), Fecha destrucción, Descripción activo, "
            "Método (select: Borrado seguro/Destrucción física/Desmagnetización/Incineración/Trituración/Proveedor certificado), "
            "Responsable, Autoriza, Próxima revisión."
        ),
    },

    # ── EP-06: Exportación ───────────────────────────────────────────────
    {
        "epic": "EP-06",
        "title": "[EP-06] US-0601 — Exportar registro individual a PDF",
        "priority": 2, "estimate": 8,
        "labels": ["feature", "pdf-excel"],
        "description": (
            "PDF A4 con: logo empresa, razón social, nombre formato, política PSC, "
            "N° registro, todos los campos, pie con creador + fecha + fecha exportación.\n\n"
            "Librería: mPDF. Tiempo máx: 5 segundos."
        ),
    },
    {
        "epic": "EP-06",
        "title": "[EP-06] US-0602 — Exportar listado filtrado a Excel (.xlsx)",
        "priority": 3, "estimate": 5,
        "labels": ["feature", "pdf-excel"],
        "description": "Exportar listado activo con filtros aplicados. Cabecera con empresa y formato. Máx 5,000 registros. Librería: PhpSpreadsheet.",
    },
    {
        "epic": "EP-06",
        "title": "[EP-06] US-0603 — Exportar ticket completo a PDF",
        "priority": 4, "estimate": 5,
        "labels": ["feature", "pdf-excel", "helpdesk"],
        "description": "PDF con hilo completo del ticket. Notas internas visibles solo si descarga el agente.",
    },
    {
        "epic": "EP-06",
        "title": "[EP-06] US-0604 — Reporte mensual PDF de incidencias",
        "priority": 3, "estimate": 3,
        "labels": ["feature", "pdf-excel"],
        "description": "Selección de mes/año → PDF con portada, tabla F09, tabla F10, resumen estadístico. Firma al pie: Admin + fecha.",
    },

    # ── EP-07: Helpdesk ──────────────────────────────────────────────────
    {
        "epic": "EP-07",
        "title": "[EP-07] US-0701 — Crear ticket de soporte",
        "priority": 1, "estimate": 5,
        "labels": ["feature", "helpdesk", "frontend-ui"],
        "description": (
            "Formulario: asunto, descripción, prioridad (Baja/Media/Alta/Urgente), "
            "categoría (Consulta/Problema técnico/Error en registro/Solicitud acceso/Otro).\n\n"
            "Estado inicial: nuevo. Email de confirmación al usuario + notificación a todos los agentes."
        ),
    },
    {
        "epic": "EP-07",
        "title": "[EP-07] US-0702 — Listado de tickets (vista usuario/admin empresa)",
        "priority": 2, "estimate": 5,
        "labels": ["feature", "helpdesk", "frontend-ui"],
        "description": "Lista tickets de la empresa con filtros (estado, prioridad, fecha). Badge de tickets abiertos en sidebar.",
    },
    {
        "epic": "EP-07",
        "title": "[EP-07] US-0703 — Hilo de conversación del ticket",
        "priority": 1, "estimate": 8,
        "labels": ["feature", "helpdesk", "frontend-ui"],
        "description": (
            "Mensajes cronológicos con diferencia visual usuario/agente. "
            "Usuario NO ve notas internas. "
            "Caja de respuesta al pie. Email al responder."
        ),
    },
    {
        "epic": "EP-07",
        "title": "[EP-07] US-0704 — Panel de agente helpdesk (bandeja unificada)",
        "priority": 1, "estimate": 8,
        "labels": ["feature", "helpdesk", "frontend-ui"],
        "description": (
            "Vista split: lista de tickets (izq) + detalle (der). "
            "Filtros: empresa, estado, prioridad, agente asignado. "
            "Counters de sin asignar y nuevos. Todas las empresas visibles."
        ),
    },
    {
        "epic": "EP-07",
        "title": "[EP-07] US-0705 — Gestión del ticket por el agente",
        "priority": 1, "estimate": 8,
        "labels": ["feature", "helpdesk", "backend-php"],
        "description": (
            "Acciones: asignar/reasignar agente, cambiar estado "
            "(nuevo→en_revision→esperando_usuario→resuelto→cerrado), "
            "agregar nota interna, cambiar prioridad, cerrar con comentario."
        ),
    },
    {
        "epic": "EP-07",
        "title": "[EP-07] US-0706 — Adjuntos en tickets",
        "priority": 3, "estimate": 5,
        "labels": ["feature", "helpdesk", "security"],
        "description": "Tipos permitidos: .jpg, .png, .pdf, .docx. Máx 5 MB. Almacenar como UUID. Máx 3 por mensaje. Validar MIME en servidor.",
    },
    {
        "epic": "EP-07",
        "title": "[EP-07] US-0707 — Reapertura de ticket resuelto",
        "priority": 3, "estimate": 3,
        "labels": ["feature", "helpdesk"],
        "description": "Ventana de 7 días post-cierre para reabrir. Al reabrir: estado → en_revision, email al agente. Historial de reaperturas en el hilo.",
    },

    # ── EP-08: Notificaciones ────────────────────────────────────────────
    {
        "epic": "EP-08",
        "title": "[EP-08] US-0801 — Configuración de PHPMailer y cola de emails",
        "priority": 2, "estimate": 5,
        "labels": ["chore", "email"],
        "description": "Clase Mailer wrapper. Cola en tabla notificaciones_email. Si falla SMTP: loguear sin interrumpir. Template base HTML.",
    },
    {
        "epic": "EP-08",
        "title": "[EP-08] US-0802 — Templates HTML de email",
        "priority": 3, "estimate": 5,
        "labels": ["feature", "email", "frontend-ui"],
        "description": "Templates: bienvenida, recuperar contraseña, nuevo ticket, respuesta en ticket, ticket resuelto, nueva incidencia. Todos con enlace directo al elemento.",
    },
    {
        "epic": "EP-08",
        "title": "[EP-08] US-0803 — Preferencias de notificación por usuario",
        "priority": 4, "estimate": 3,
        "labels": ["feature", "email"],
        "description": "Cada usuario activa/desactiva: notif. tickets, notif. incidencias. Las notificaciones de cuenta (contraseña, bienvenida) no se pueden desactivar.",
    },
    {
        "epic": "EP-08",
        "title": "[EP-08] US-0804 — Email de nueva incidencia al Admin Empresa",
        "priority": 2, "estimate": 3,
        "labels": ["feature", "email", "security"],
        "description": "Al crear F09: email al Admin Empresa con N°, tipo, severidad y enlace. Si severidad=Alta: asunto con URGENTE.",
    },

    # ── EP-09: Dashboard ─────────────────────────────────────────────────
    {
        "epic": "EP-09",
        "title": "[EP-09] US-0901 — Dashboard empresa (usuario/admin)",
        "priority": 2, "estimate": 8,
        "labels": ["feature", "frontend-ui"],
        "description": "4 métricas del mes, últimos 5 registros, tickets abiertos/pendientes, accesos rápidos a formatos más usados.",
    },
    {
        "epic": "EP-09",
        "title": "[EP-09] US-0902 — Gráfico de registros por tipo (últimos 6 meses)",
        "priority": 3, "estimate": 5,
        "labels": ["feature", "frontend-ui"],
        "description": "Gráfico de barras con Chart.js (CDN). X=mes, Y=cantidad, colores=tipo de formato. Filtrable por rango de fechas.",
    },
    {
        "epic": "EP-09",
        "title": "[EP-09] US-0903 — Dashboard agente helpdesk",
        "priority": 3, "estimate": 5,
        "labels": ["feature", "frontend-ui", "helpdesk"],
        "description": "Tickets por estado, tiempo promedio de respuesta del mes, mis tickets vs sin asignar, últimas 5 actualizaciones globales.",
    },

    # ── EP-10: Seguridad ─────────────────────────────────────────────────
    {
        "epic": "EP-10",
        "title": "[EP-10] US-1001 — Protección CSRF en todos los formularios",
        "priority": 1, "estimate": 3,
        "labels": ["security", "backend-php"],
        "description": "CsrfHelper con `generate()` y `verify()`. Campo oculto `_csrf_token` en todos los formularios. Requests sin token válido → 403.",
    },
    {
        "epic": "EP-10",
        "title": "[EP-10] US-1002 — Headers de seguridad HTTP",
        "priority": 2, "estimate": 2,
        "labels": ["security"],
        "description": "X-Frame-Options: DENY, X-Content-Type-Options: nosniff, Referrer-Policy: strict-origin-when-cross-origin, CSP básico.",
    },
    {
        "epic": "EP-10",
        "title": "[EP-10] US-1003 — Protección de archivos subidos (uploads)",
        "priority": 2, "estimate": 3,
        "labels": ["security"],
        "description": "Validar MIME type + extensión en servidor. Renombrar a UUID. Almacenar fuera del webroot o con .htaccess que bloquea ejecución.",
    },
    {
        "epic": "EP-10",
        "title": "[EP-10] US-1004 — Log de auditoría del sistema",
        "priority": 2, "estimate": 5,
        "labels": ["feature", "security", "backend-php"],
        "description": (
            "Clase AuditLogger. Registra: login/logout, crear/editar/desactivar registro, "
            "cambios de estado ticket, gestión de usuarios. "
            "Vista de consulta para Super Admin con filtros."
        ),
    },
    {
        "epic": "EP-10",
        "title": "[EP-10] US-1005 — Validación y sanitización de entradas",
        "priority": 1, "estimate": 5,
        "labels": ["security", "backend-php"],
        "description": "htmlspecialchars() en toda salida. Validar fechas, castear numéricos, validar selects contra lista de valores permitidos en servidor.",
    },
    {
        "epic": "EP-10",
        "title": "[EP-10] US-1006 — Gestión de contraseñas seguras",
        "priority": 1, "estimate": 2,
        "labels": ["security", "auth"],
        "description": "password_hash() con PASSWORD_BCRYPT cost 12. Contraseñas temporales de 12 chars. Nunca almacenar ni loguear contraseñas.",
    },

    # ── EP-11: Deploy ────────────────────────────────────────────────────
    {
        "epic": "EP-11",
        "title": "[EP-11] US-1101 — Script de instalación guiado (install.php)",
        "priority": 2, "estimate": 5,
        "labels": ["chore"],
        "description": "install.php: detecta requisitos PHP, crea tablas, crea super_admin inicial. Se elimina o bloquea tras instalación exitosa.",
    },
    {
        "epic": "EP-11",
        "title": "[EP-11] US-1102 — README.md y documentación de despliegue",
        "priority": 2, "estimate": 3,
        "labels": ["docs"],
        "description": "README con: requisitos, pasos instalación, config email, estructura carpetas, descripción roles, guía de actualización, checklist seguridad producción.",
    },
    {
        "epic": "EP-11",
        "title": "[EP-11] US-1103 — Guía de usuario final",
        "priority": 3, "estimate": 5,
        "labels": ["docs"],
        "description": "Guía en PDF/MD con capturas: login, crear registros, consultar registros, exportar, abrir ticket, seguimiento ticket. Una sección por rol.",
    },
]


def graphql(query: str, variables: dict = None) -> dict:
    """Ejecuta una query o mutation en la API de Linear."""
    payload = {"query": query}
    if variables:
        payload["variables"] = variables
    response = requests.post(API_URL, headers=HEADERS, json=payload, timeout=15)
    response.raise_for_status()
    return response.json()


def create_issue(issue_data: dict) -> str | None:
    """Crea un issue en Linear y retorna su ID interno."""
    psc = issue_data.get("psc", "")
    desc_parts = []
    if psc:
        desc_parts.append(f"**Política PSC:** {psc}")
    if issue_data.get("description"):
        desc_parts.append(issue_data["description"])
    
    description = "\n\n".join(desc_parts)
    priority = PRIORITY_MAP.get(issue_data.get("priority", 3), 3)

    mutation = """
    mutation CreateIssue($input: IssueCreateInput!) {
      issueCreate(input: $input) {
        success
        issue { id identifier title url }
      }
    }
    """

    variables = {
        "input": {
            "teamId": TEAM_ID,
            "title": issue_data["title"],
            "priority": priority,
            "estimate": issue_data.get("estimate", 3),
            "description": description,
        }
    }

    result = graphql(mutation, variables)
    issue_create = result.get("data", {}).get("issueCreate", {})

    if issue_create.get("success"):
        issue = issue_create["issue"]
        print(f"  ✅ {issue['identifier']} — {issue_data['title'][:55]}...")
        return issue["id"]
    else:
        errors = result.get("errors", [])
        err_msg = errors[0].get("message", "Error desconocido") if errors else "Error desconocido"
        print(f"  ❌ Error: {err_msg} — {issue_data['title'][:40]}...")
        return None


def main():
    # Validaciones
    if not API_KEY:
        print("❌ Falta LINEAR_API_KEY")
        print("   export LINEAR_API_KEY='lin_api_XXXXXXXXXXXXXXXX'")
        sys.exit(1)

    if not TEAM_ID:
        print("❌ Falta LINEAR_TEAM_ID")
        print("   export LINEAR_TEAM_ID='xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'")
        print("   (Linear → Settings → Members → copiar Team ID)")
        sys.exit(1)

    # Verificar conexión
    print("🔗 Verificando conexión con Linear API...")
    test = graphql("query { viewer { id name } }")
    if "errors" in test:
        print(f"❌ Error de autenticación: {test['errors'][0]['message']}")
        sys.exit(1)
    
    viewer = test.get("data", {}).get("viewer", {})
    print(f"✅ Conectado como: {viewer.get('name', 'Desconocido')}")
    print(f"📋 Importando {len(BACKLOG)} user stories...")
    print()

    created = 0
    errors = 0
    current_epic = ""

    for issue_data in BACKLOG:
        epic = issue_data["epic"]
        if epic != current_epic:
            print(f"\n── {epic} {'─' * 50}")
            current_epic = epic

        issue_id = create_issue(issue_data)
        if issue_id:
            created += 1
        else:
            errors += 1

        time.sleep(DELAY_BETWEEN_REQUESTS)  # Rate limiting

    print(f"\n{'=' * 60}")
    print(f"Importación completada:")
    print(f"  ✅ Creados: {created}")
    print(f"  ❌ Errores: {errors}")
    print(f"  📊 Total:   {len(BACKLOG)}")

    if errors > 0:
        print(f"\n⚠️  Hay {errors} issue(s) que no se crearon.")
        print("   Revisa los mensajes de error arriba y reintenta.")


if __name__ == "__main__":
    main()
