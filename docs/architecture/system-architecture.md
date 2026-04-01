# Arquitectura del Sistema — SecuriForm

**Proyecto:** SecuriForm (SentinelGuard)
**Empresa:** co.de (co-de.com.pe)
**Fecha:** 2026-04-01

---

## 1. Vision General

SecuriForm es una aplicacion web Laravel + Filament multiempresa para gestionar formatos
de seguridad de la informacion (segun politicas PSC del Estudio Palacios Abogados S.A.C.)
y un helpdesk centralizado donde un equipo de agentes atiende tickets de todas las empresas.

---

## 2. Diagrama de Servicios Docker

```
+-----------------------------------------------+
|          Docker Network: securiform            |
|                                                |
|  +----------+    +-----------+                 |
|  |   app    |    |    db     |                 |
|  | PHP 8.2  |    | MariaDB   |                 |
|  | Apache   |    | 10.11     |                 |
|  | :8080    |    | :3306     |                 |
|  +----------+    +-----------+                 |
|                                                |
|  +----------+    +-----------+                 |
|  |  redis   |    | mailhog   |                 |
|  | Redis 7  |    | MailHog   |                 |
|  | :6379    |    | :8025     |                 |
|  +----------+    +-----------+                 |
|                                                |
|  +----------+                                  |
|  |phpmyadmin|                                  |
|  | :8081    |                                  |
|  +----------+                                  |
+-----------------------------------------------+
```

### Detalle de cada servicio

| Servicio | Imagen | Puertos | Funcion |
|----------|--------|---------|---------|
| **app** | Custom (PHP 8.2 + Apache) | 8080 | Laravel + Filament |
| **db** | MariaDB 10.11 | 3306 | Base de datos principal |
| **redis** | Redis 7 | 6379 | Cache, sesiones, colas |
| **mailhog** | MailHog | 8025 (UI), 1025 (SMTP) | Captura emails en desarrollo |
| **phpmyadmin** | phpMyAdmin | 8081 | Gestion visual de BD |

---

## 3. Flujo de un Request

```
Cliente (Browser)
    |
    v
Apache (:8080)
    |
    v
Laravel (PHP 8.2)
    |
    +-- Middleware Auth (Filament)
    |
    +-- Middleware Tenant (EmpresaScope)
    |
    v
Filament Resource / Page
    |
    +-- Eloquent Model (con Global Scope)
    |
    v
MariaDB (filtrado por empresa_id)
```

### Middleware stack

1. `auth` — verifica que el usuario esta autenticado
2. `EmpresaScope` — filtra automaticamente por `empresa_id` del usuario autenticado
3. Filament gates — verifica roles y permisos (spatie/laravel-permission)

---

## 4. Arquitectura Multi-Empresa

### Estrategia: Global Scope por empresa_id

A diferencia de base de datos separada por tenant, SecuriForm usa una **sola base de datos**
con aislamiento por `empresa_id` mediante Global Scopes de Eloquent.

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
```

### Filament Multi-Tenancy

Filament 3 gestiona la multi-tenancy a nivel de panel, permitiendo que cada usuario
vea solo los datos de su empresa.

---

## 5. Panel Filament

```
Panel Admin (localhost:8080/admin)
|
+-- Dashboard (Widgets)
|
+-- EmpresaResource (Solo super_admin)
+-- UserResource (CRUD usuarios)
+-- RegistroResource (Los 13 formatos)
+-- TicketResource (Helpdesk)
+-- AuditLogResource (Solo lectura)
|
+-- Pages
    +-- Profile
    +-- Empresa Detail
```

### Roles y acceso

| Rol | Acceso |
|-----|--------|
| `super_admin` | Todo, incluyendo gestionar empresas |
| `admin_empresa` | Gestionar usuarios y registros de SU empresa |
| `usuario` | Crear y ver registros de su empresa |
| `agente_helpdesk` | Gestionar tickets de todas las empresas |
| `solo_lectura` | Solo ver registros, no crear ni editar |

---

## 6. Estrategia de Cache

| Dato | Store | TTL | Invalidacion |
|------|-------|-----|-------------|
| Sesiones | Redis | Config Laravel | Automatica |
| Cache general | Redis | Variable | Automatica |
| Colas de jobs | Redis | - | Al procesar |

---

## 7. Generacion de Documentos

### PDF (mPDF)

- Cada formato de seguridad puede exportarse a PDF
- Incluye: logo empresa, encabezados, tabla de datos, pie de pagina
- Generado en memoria, descarga directa

### Excel (PhpSpreadsheet)

- Export masivo de registros filtrados
- Headers con formato, datos tipeados
- Generado en memoria, descarga directa
