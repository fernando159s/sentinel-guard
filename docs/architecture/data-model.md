# Modelo de Datos — SecuriForm

**Proyecto:** SecuriForm (SentinelGuard)
**Empresa:** co.de (co-de.com.pe)
**Fecha:** 2026-04-01

---

## 1. Vision General

SecuriForm usa una **sola base de datos** (MariaDB) con aislamiento por `empresa_id`
mediante Global Scopes de Eloquent. Los datos de cada empresa se filtran automaticamente
en todas las queries.

---

## 2. Tablas Principales

```
empresas              -- tenants del sistema
users                 -- todos los usuarios (extendida con empresa_id)
registros             -- los 13 formatos (campo JSON 'datos')
secuencias_registro   -- auto-incremento por empresa/formato/anio
tickets               -- tickets del helpdesk
ticket_mensajes       -- hilo de conversacion
ticket_adjuntos       -- archivos adjuntos
notificaciones_email  -- cola de emails
audit_logs            -- log inmutable de acciones
roles                 -- spatie/permission roles
permissions           -- spatie/permission permisos
model_has_roles       -- relacion user-role
model_has_permissions -- relacion user-permission
```

---

## 3. Modelos Principales

### 3.1 Empresa

**Archivo:** `app/Models/Empresa.php`
**Tabla:** `empresas`

| Campo | Tipo | Nullable | Descripcion |
|-------|------|----------|-------------|
| `id` | bigint PK | No | Auto-incremental |
| `nombre` | string | No | Nombre de la empresa |
| `ruc` | string | Si | RUC de la empresa |
| `direccion` | string | Si | Direccion |
| `telefono` | string | Si | Telefono |
| `email` | string | Si | Email de contacto |
| `logo` | string | Si | Ruta del logo en storage |
| `activa` | boolean | No | Si la empresa esta activa |

### 3.2 User

**Archivo:** `app/Models/User.php`
**Tabla:** `users`

| Campo | Tipo | Nullable | Descripcion |
|-------|------|----------|-------------|
| `id` | bigint PK | No | Auto-incremental |
| `name` | string | No | Nombre completo |
| `email` | string UK | No | Email unico |
| `password` | string | No | Password hasheado |
| `empresa_id` | bigint FK | Si | Empresa a la que pertenece |

**Traits:** `HasRoles` (spatie)

### 3.3 Registro

**Archivo:** `app/Models/Registro.php`
**Tabla:** `registros`

| Campo | Tipo | Nullable | Descripcion |
|-------|------|----------|-------------|
| `id` | bigint PK | No | Auto-incremental |
| `empresa_id` | bigint FK | No | Empresa propietaria |
| `user_id` | bigint FK | No | Usuario que creo el registro |
| `tipo_formato` | string | No | Codigo del formato (F01-F13) |
| `numero_registro` | string UK | No | Ej: INC-2026-004 |
| `datos` | json | No | Datos especificos del formato |
| `created_at` | timestamp | - | - |
| `updated_at` | timestamp | - | - |

### 3.4 Ticket

**Archivo:** `app/Models/Ticket.php`
**Tabla:** `tickets`

| Campo | Tipo | Nullable | Descripcion |
|-------|------|----------|-------------|
| `id` | bigint PK | No | Auto-incremental |
| `empresa_id` | bigint FK | No | Empresa del ticket |
| `user_id` | bigint FK | No | Usuario que creo el ticket |
| `asunto` | string | No | Asunto del ticket |
| `descripcion` | text | No | Descripcion detallada |
| `estado` | enum | No | abierto, en_proceso, cerrado |
| `prioridad` | enum | No | baja, media, alta, critica |
| `agente_id` | bigint FK | Si | Agente asignado |

---

## 4. Los 13 Formatos de Seguridad

| ID | Prefijo | Nombre | Politica |
|----|---------|--------|----------|
| F01 | `AUD` | Auditorias realizadas | PSC000001 |
| F02 | `BD` | Banco de datos inscritos | PSC000001 |
| F03 | `PRES` | Prestadores con acceso a datos | PSC000001/PSC000002 |
| F04 | `DS` | Datos sensibles | PSC000001/PSC000-46 |
| F05 | `PA` | Personal autorizado al banco de datos | PSC000001 |
| F06 | `AS` | Acceso de soporte no autorizado | PSC000001 |
| F07 | `INV` | Inventario de soportes | PSC000003/PSC000004 |
| F08 | `IS` | Ingreso y salida de soportes | PSC000003 |
| F09 | `INC` | Notificacion de incidencias | PSC000001/PSC000-25 |
| F10 | `RES` | Resolucion de incidencias | PSC000-25 |
| F11 | `REC` | Recuperacion de datos | PSC000001/PSC000-25 |
| F12 | `BAK` | Copias de seguridad | PSC000003/PSC000-15 |
| F13 | `DEST` | Destruccion de activos | PSC000003/PSC000004 |

Numero de registro generado: `[PREFIJO]-[ANO]-[SEC 3 digitos]` (ej: `INC-2026-004`)

Cada formato se almacena como un registro con un campo JSON `datos` que contiene
los campos especificos del formato.

---

## 5. Relaciones Principales

```
Empresa (1) ---< (*) User
Empresa (1) ---< (*) Registro
Empresa (1) ---< (*) Ticket
User (1) ---< (*) Registro
User (1) ---< (*) Ticket (como creador)
User (1) ---< (*) Ticket (como agente)
Ticket (1) ---< (*) TicketMensaje
Ticket (1) ---< (*) TicketAdjunto
```

---

## 6. Notas de Implementacion

### Tenant Isolation

Todos los modelos que pertenecen a una empresa usan el `EmpresaScope`:

```php
protected static function booted(): void
{
    static::addGlobalScope(new EmpresaScope);
}
```

### Campo JSON `datos`

Los datos especificos de cada formato se almacenan como JSON. Esto permite
que cada formato tenga campos diferentes sin necesidad de tablas separadas.
El schema de campos se define en la configuracion del motor de formatos.

### Secuencias de Registro

La tabla `secuencias_registro` mantiene contadores por empresa/formato/anio
para generar numeros de registro unicos: `[PREFIJO]-[ANO]-[SEC]`.
