# EDT / WBS - Estructura de Desglose del Trabajo

**Proyecto:** SecuriForm (SentinelGuard)
**Empresa:** co.de (co-de.com.pe)
**Fecha:** 2026-04-01

Leyenda de estados:
- [x] Completado
- [~] En progreso
- [ ] Pendiente

---

## 1. Plataforma SecuriForm

### 1.1 Infraestructura y DevOps (EP-01)

```
1.1 Infraestructura y DevOps (EP-01)
+-- 1.1.1 Docker environment                                     [x]
|   +-- 1.1.1.1 Dockerfile PHP 8.2 + Apache                     [x]
|   +-- 1.1.1.2 docker-compose (app, db, redis, mailhog, pma)   [x]
|   +-- 1.1.1.3 Configuracion PHP (php.ini)                     [x]
|   +-- 1.1.1.4 Apache virtual host                              [x]
|   +-- 1.1.1.5 Entrypoint script                               [x]
+-- 1.1.2 Makefile shortcuts                                     [x]
|   +-- 1.1.2.1 Targets Docker (up, down, build, restart, logs) [x]
|   +-- 1.1.2.2 Targets Laravel (migrate, seed, test, tinker)   [x]
|   +-- 1.1.2.3 Target init (setup primera vez)                 [x]
+-- 1.1.3 CI/CD Pipeline                                        [ ]
|   +-- 1.1.3.1 GitHub Actions: lint + tests                    [ ]
|   +-- 1.1.3.2 GitHub Actions: deploy staging                  [ ]
+-- 1.1.4 Deploy produccion                                      [ ]
    +-- 1.1.4.1 Docker Compose prod                              [ ]
    +-- 1.1.4.2 SSL/TLS                                         [ ]
```

### 1.2 Autenticacion y Multi-Tenancy (EP-02)

```
1.2 Autenticacion y Multi-Tenancy (EP-02)
+-- 1.2.1 Filament Auth                                          [x]
|   +-- 1.2.1.1 Login page                                      [x]
|   +-- 1.2.1.2 Password reset                                  [x]
+-- 1.2.2 Roles y permisos (Spatie)                              [x]
|   +-- 1.2.2.1 Instalacion spatie/laravel-permission           [x]
|   +-- 1.2.2.2 Definicion de 5 roles                           [x]
|   +-- 1.2.2.3 Permisos granulares                              [x]
+-- 1.2.3 Multi-tenancy por empresa                              [x]
|   +-- 1.2.3.1 EmpresaScope global                             [x]
|   +-- 1.2.3.2 Filament tenant configuration                   [x]
+-- 1.2.4 UserResource (CRUD)                                    [x]
+-- 1.2.5 Profile page                                           [x]
+-- 1.2.6 Empresa detail page                                    [x]
```

### 1.3 Modelos y Migraciones (EP-03)

```
1.3 Modelos y Migraciones (EP-03)
+-- 1.3.1 Tabla empresas + modelo                               [x]
+-- 1.3.2 Tabla users (extendida con empresa_id)                [x]
+-- 1.3.3 Tabla registros (con campo JSON datos)                [x]
+-- 1.3.4 Tabla secuencias_registro                              [x]
+-- 1.3.5 Tabla tickets                                          [ ]
+-- 1.3.6 Tabla ticket_mensajes                                  [ ]
+-- 1.3.7 Tabla ticket_adjuntos                                  [ ]
+-- 1.3.8 Tabla audit_logs                                       [ ]
+-- 1.3.9 Seeders iniciales                                      [x]
```

### 1.4 Motor de Formatos (EP-04)

```
1.4 Motor de Formatos (EP-04)
+-- 1.4.1 Enum TipoFormato (13 formatos)                        [x]
+-- 1.4.2 Configuracion de campos por formato                   [x]
+-- 1.4.3 RegistroResource con formulario dinamico              [x]
+-- 1.4.4 Generacion automatica de numero de registro           [x]
+-- 1.4.5 Filtrado por tipo de formato                          [x]
```

### 1.5 Panel Filament Admin (EP-05)

```
1.5 Panel Filament Admin (EP-05)
+-- 1.5.1 EmpresaResource                                       [x]
+-- 1.5.2 UserResource                                           [x]
+-- 1.5.3 RegistroResource                                       [x]
+-- 1.5.4 TicketResource                                         [ ]
+-- 1.5.5 AuditLogResource                                       [ ]
+-- 1.5.6 Dashboard widgets                                      [~]
+-- 1.5.7 Personalizacion de portal por empresa                  [ ]
    +-- 1.5.7.1 Migracion: campos de branding en empresas        [ ]
    +-- 1.5.7.2 Formulario de personalizacion en EmpresaResource [ ]
    +-- 1.5.7.3 Theming dinamico por tenant                      [ ]
    +-- 1.5.7.4 Tests de personalizacion                         [ ]
```

### 1.6 Helpdesk (EP-06)

```
1.6 Helpdesk (EP-06)
+-- 1.6.1 Modelo Ticket + migracion                             [ ]
+-- 1.6.2 Modelo TicketMensaje + migracion                      [ ]
+-- 1.6.3 Modelo TicketAdjunto + migracion                      [ ]
+-- 1.6.4 TicketResource (Filament)                              [ ]
+-- 1.6.5 Hilo de conversacion UI                               [ ]
+-- 1.6.6 Upload de adjuntos                                    [ ]
+-- 1.6.7 Asignacion de agentes                                 [ ]
+-- 1.6.8 Notificaciones de ticket                              [ ]
```

### 1.7 Exportaciones (EP-07)

```
1.7 Exportaciones (EP-07)
+-- 1.7.1 Export PDF individual (mPDF)                           [x]
+-- 1.7.2 Export Excel individual (PhpSpreadsheet)               [x]
+-- 1.7.3 Export masivo a Excel                                  [x]
+-- 1.7.4 Personalizacion con logo de empresa                   [x]
```

### 1.8 Auditoria y Logs (EP-08)

```
1.8 Auditoria y Logs (EP-08)
+-- 1.8.1 Modelo AuditLog + migracion                           [ ]
+-- 1.8.2 Observer para registrar acciones                      [ ]
+-- 1.8.3 AuditLogResource (solo lectura)                       [ ]
+-- 1.8.4 Filtros por usuario, accion, fecha                    [ ]
```

### 1.9 Notificaciones (EP-09)

```
1.9 Notificaciones (EP-09)
+-- 1.9.1 Configuracion Laravel Mail (SMTP)                     [ ]
+-- 1.9.2 Notificacion de nuevo ticket                          [ ]
+-- 1.9.3 Notificacion de respuesta a ticket                    [ ]
+-- 1.9.4 Cola de emails (Redis)                                [ ]
```

### 1.10 Testing y Calidad (EP-10)

```
1.10 Testing y Calidad (EP-10)
+-- 1.10.1 Tests unitarios                                      [ ]
+-- 1.10.2 Tests de feature                                      [ ]
+-- 1.10.3 Factories                                             [ ]
+-- 1.10.4 Linting (Pint)                                       [ ]
```

---

## Resumen de Progreso

| Epic | Descripcion | Completados | Total | % |
|------|------------|:-----------:|:-----:|:-:|
| EP-01 | Infraestructura y DevOps | 8 | 12 | 67% |
| EP-02 | Autenticacion y Multi-Tenancy | 8 | 8 | 100% |
| EP-03 | Modelos y Migraciones | 5 | 9 | 56% |
| EP-04 | Motor de Formatos | 5 | 5 | 100% |
| EP-05 | Panel Filament Admin | 3 | 10 | 30% |
| EP-06 | Helpdesk | 0 | 8 | 0% |
| EP-07 | Exportaciones | 4 | 4 | 100% |
| EP-08 | Auditoria y Logs | 0 | 4 | 0% |
| EP-09 | Notificaciones | 0 | 4 | 0% |
| EP-10 | Testing y Calidad | 0 | 4 | 0% |
| **Total** | | **33** | **68** | **49%** |
