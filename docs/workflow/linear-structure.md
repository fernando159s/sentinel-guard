# Estructura de Linear — SecuriForm

## Resumen

Linear es la herramienta de gestion de proyecto de SecuriForm. Este documento
describe la organizacion del workspace, proyectos, labels, estados y convenciones
para crear y gestionar issues.

---

## Equipo (Team)

| Campo         | Valor              |
|---------------|--------------------|
| Nombre        | **SentinelForms**  |
| Identificador | **SEN**            |
| Miembros      | Fernando, Claude AI |

Todos los issues se identifican como `SEN-{numero}` (asignado automaticamente por Linear).

---

## Proyectos (Epics)

Los proyectos agrupan issues relacionados por area funcional.

| Codigo | Nombre                             | Descripcion                                                              |
|--------|------------------------------------|--------------------------------------------------------------------------|
| EP-01  | Entorno de Desarrollo              | Docker Compose, Makefiles, configuracion de servicios                    |
| EP-02  | Multi-Tenancy y Auth               | Filament multi-tenancy por empresa, login, roles, permisos              |
| EP-03  | Modelos y Migraciones              | Esquema de BD, modelos Eloquent, factories y relaciones                 |
| EP-04  | Motor de Formatos                  | Los 13 formatos de seguridad con formularios dinamicos                   |
| EP-05  | Panel Filament Admin               | Resources, Pages, Widgets del backoffice                                |
| EP-06  | Helpdesk                           | Sistema de tickets centralizado, mensajes, adjuntos                     |
| EP-07  | Exportaciones                      | Generacion de PDF y Excel para registros y reportes                     |
| EP-08  | Auditoria y Logs                   | Log inmutable de acciones, audit trail                                  |
| EP-09  | Notificaciones                     | Emails transaccionales, notificaciones del helpdesk                     |
| EP-10  | Testing y Calidad                  | Tests unitarios, feature, factories, linting                            |

---

## Labels

### Labels de area tecnica (Team labels)

Estos labels identifican la capa o tecnologia involucrada en el issue.

| Label           | Uso                                                    |
|-----------------|--------------------------------------------------------|
| `docker`        | Dockerfiles, docker-compose, configuracion de contenedores |
| `filament`      | Paneles Filament (Resources, Pages, Widgets)           |
| `backend`       | Logica de Laravel no cubierta por otros labels         |
| `docs`          | Documentacion del proyecto                             |
| `email`         | MailHog, templates de email                            |
| `auth`          | Login, registro, tokens, permisos, roles               |
| `multitenancy`  | Tenant isolation por empresa, global scopes            |
| `database`      | Migraciones, seeders, queries, optimizacion de BD      |
| `security`      | Vulnerabilidades, hardening, validacion de input       |
| `chore`         | Mantenimiento, actualizacion de dependencias, config   |
| `helpdesk`      | Tickets, mensajes, adjuntos, agentes                   |
| `formatos`      | Los 13 formatos de seguridad, formularios dinamicos    |
| `export`        | Generacion PDF (mPDF) y Excel (PhpSpreadsheet)         |
| `audit`         | Logs de auditoria, trail de acciones                   |
| `testing`       | Tests unitarios, feature, factories                    |

### Labels de tipo (Workspace labels)

| Label           | Uso                                               |
|-----------------|---------------------------------------------------|
| `Feature`       | Nueva funcionalidad                               |
| `Improvement`   | Mejora a funcionalidad existente                  |
| `Bug`           | Correccion de un defecto                          |

### Combinacion de labels

Cada issue debe tener:
1. **Al menos un label de area tecnica** (puede tener varios si cruza areas).
2. **Exactamente un label de tipo** (Feature, Improvement, o Bug).

**Ejemplo:**
```
SEN-12: Agregar CRUD de registros en panel Filament
Labels: filament, database, formatos, Feature

SEN-45: Corregir formato de fecha en exportacion PDF
Labels: export, backend, Bug

SEN-08: Actualizar dependencias de Composer
Labels: chore, backend, Improvement
```

---

## Cycles (Sprints)

| Parametro        | Valor              |
|------------------|--------------------|
| Duracion         | 2 semanas          |
| Inicio           | Lunes              |
| Nomenclatura     | Sprint N (ej: Sprint 1, Sprint 2) |
| Velocidad target | ~40-50 puntos      |

### Como se gestionan los Cycles

1. Durante el **Sprint Planning**, se seleccionan issues del Backlog y se agregan al Cycle activo.
2. Los issues se mueven a **Todo** al entrar al Cycle.
3. Al finalizar el Cycle, los issues no completados se mueven al siguiente Cycle o regresan al Backlog.
4. Linear calcula automaticamente la velocidad y muestra el burndown del Cycle.

---

## Workflow States

Los estados en Linear estan configurados de la siguiente manera:

```
+---------------------------------------------------+
|                    BACKLOG                         |
|  (No iniciado, sin sprint asignado)               |
+-------------------------+-------------------------+
                          | Priorizado en Sprint Planning
                          v
+---------------------------------------------------+
|                     TODO                           |
|  (Asignado a un sprint, listo para empezar)       |
+-------------------------+-------------------------+
                          | Desarrollador empieza a trabajar
                          v
+---------------------------------------------------+
|                 IN PROGRESS                        |
|  (Rama creada, desarrollo activo)                 |
+-------------------------+-------------------------+
                          | PR creado en GitHub
                          v
+---------------------------------------------------+
|                  IN REVIEW                         |
|  (PR abierto, esperando code review)              |
+-------------------------+-------------------------+
                          | PR aprobado y mergeado
                          v
+---------------------------------------------------+
|                     DONE                           |
|  (Completado, mergeado a develop)                 |
+---------------------------------------------------+

Estados terminales alternativos:
+--------------+  +--------------+
|   CANCELED   |  |  DUPLICATE   |
| (Descartado) |  | (Duplicado)  |
+--------------+  +--------------+
```

### State IDs (Team SentinelForms)

| Estado      | ID                                     |
|-------------|----------------------------------------|
| Backlog     | `b6437bd4-dcc0-4155-b676-5fa743fb2cd4` |
| Todo        | `e253deab-c9c0-4b2b-8097-b24d8f0dd48b` |
| In Progress | `d7ff2d00-6ac0-4af1-b1a9-0fb0f4137207` |
| In Review   | `61aa257c-e964-4149-9405-9a0994c66a1c` |
| Done        | `e83c7aa3-0ee5-474d-831d-e96ba295292b` |
| Canceled    | `9b291f3f-28d1-4b1f-962f-66e2409d7b6d` |
| Duplicate   | `657b7ba0-f44c-4f56-b432-1be7cfabc595` |

---

## Formato de Issues

### Estructura estandar

Cada issue debe seguir este formato:

```markdown
**Titulo:** Verbo en infinitivo + descripcion concisa
Ejemplo: "Crear RegistroResource con formulario dinamico"

**Descripcion (User Story):**
Como [rol], quiero [funcionalidad], para [beneficio].

**Criterios de aceptacion:**
- [ ] Criterio 1
- [ ] Criterio 2
- [ ] Criterio 3

**Notas tecnicas:** (opcional)
Detalles de implementacion, decisiones tecnicas, enlaces a documentacion.
```

### Ejemplo completo

```
Titulo: Crear RegistroResource con formularios dinamicos

Descripcion:
Como admin de empresa, quiero poder crear registros de los 13 formatos
de seguridad, para cumplir con las politicas PSC del estudio.

Criterios de aceptacion:
- [ ] RegistroResource creado con tabla y formulario
- [ ] Formulario dinamico segun tipo_formato seleccionado
- [ ] Los datos especificos se almacenan en campo JSON 'datos'
- [ ] Numero de registro auto-generado: [PREFIJO]-[ANO]-[SEC]
- [ ] Filtrado automatico por empresa_id (tenant isolation)
- [ ] Test de feature que verifica creacion y listado

Notas tecnicas:
- Los 13 formatos estan definidos en TipoFormato enum
- Cada formato tiene su schema de campos en FormatoConfig
- Usar Filament Form Builder para campos dinamicos

Proyecto: EP-04 Motor de Formatos
Labels: filament, formatos, database, Feature
Estimacion: 5 puntos
```

---

## Convenciones adicionales

### Vinculacion con GitHub

- En el PR de GitHub, incluir `Closes SEN-xxx` o `Fixes SEN-xxx` en la descripcion
  para que Linear cierre automaticamente el issue al mergear.
- En los commits, siempre incluir el ID del issue como prefijo.

### Sub-issues

Para issues complejos (5+ puntos), considerar crear sub-issues:

```
SEN-20: Implementar motor de formatos (5 pts)
  +-- SEN-20a: Crear enum TipoFormato y config de campos
  +-- SEN-20b: Crear RegistroResource con formulario base
  +-- SEN-20c: Implementar campos dinamicos por formato
  +-- SEN-20d: Agregar generacion de numero de registro
```

### Relaciones entre issues

Linear permite definir relaciones:

| Relacion      | Uso                                                     | Ejemplo                        |
|---------------|---------------------------------------------------------|--------------------------------|
| **Blocks**    | Este issue debe completarse antes que otro              | SEN-5 blocks SEN-8             |
| **Blocked by**| Este issue depende de otro                              | SEN-8 blocked by SEN-5         |
| **Related**   | Issues relacionados tematicamente                       | SEN-10 related to SEN-11       |
| **Duplicate** | Issue duplicado (se cancela uno)                        | SEN-15 duplicate of SEN-12     |

### Prioridades en Linear

| Prioridad | Nombre  | Cuando usar                                   |
|-----------|---------|-----------------------------------------------|
| Urgent    | P0      | Bloquea el sprint o el sistema esta caido     |
| High      | P1      | Importante para el sprint goal                |
| Medium    | P2      | Deseable pero no critico para el sprint       |
| Low       | P3      | Nice-to-have, se puede posponer               |
| None      | —       | Sin priorizar, en el backlog general          |
