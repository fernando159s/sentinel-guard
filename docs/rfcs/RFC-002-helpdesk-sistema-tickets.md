# RFC-002: Helpdesk — Sistema de Tickets

- **Estado**: Borrador
- **Autor**: Fernando + Claude AI
- **Fecha**: 2026-04-01
- **Epic Linear**: EP-06 Helpdesk

---

## Problema

El sistema necesita un helpdesk centralizado donde los usuarios de cualquier empresa
puedan crear tickets de soporte y los agentes puedan atenderlos.

- **Quien tiene este problema?** Usuarios que necesitan soporte tecnico o resolver dudas.
- **Con que frecuencia ocurre?** Diariamente — es funcionalidad core del MVP.
- **Que impacto tiene no resolverlo?** Sin helpdesk no hay canal de soporte, el MVP esta incompleto.

## Propuesta

Implementar el panel de helpdesk en Filament con:
1. **TicketResource** — CRUD de tickets con tabla filtrable y formulario de creacion
2. **Hilo de conversacion** — Vista de detalle con mensajes publicos e internos
3. **Adjuntos** — Upload de archivos en mensajes
4. **Asignacion** — Agentes helpdesk se asignan tickets

### Lo que YA existe (no hay que crear)

- Modelos: `Ticket`, `TicketMensaje`, `TicketAdjunto` (con relaciones)
- Migraciones: tablas `tickets`, `ticket_mensajes`, `ticket_adjuntos`, `secuencias_ticket`
- Trait `BelongsToEmpresa` en Ticket (multi-tenancy)
- Permisos: `ver_tickets`, `crear_tickets`, `editar_tickets`, `asignar_tickets`, `ver_notas_internas`
- Disco de storage `tickets` configurado
- AuditableObserver registrado en Ticket

### Lo que FALTA implementar

- TicketResource (Filament) — tabla + formulario
- ViewTicket page con hilo de conversacion
- Logica de numeracion automatica (TKT-YYYY-NNN)
- Upload de adjuntos en mensajes
- Accion de asignar agente

### Mockup: Lista de tickets (agente helpdesk)

```
+-----------------------------------------------------------------------+
| SecuriForm > Tickets                                                  |
+-----------------------------------------------------------------------+
| +------------+ +------------------------------------------------------+
| | SecuriForm | | Tickets                       [+ Nuevo Ticket]       |
| |            | +------------------------------------------------------+
| | ---------- | |                                                      |
| | Dashboard  | | Filtros: [Estado v] [Prioridad v] [Empresa v]        |
| | Registros  | |                                                      |
| | Tickets    | | +-------+----------+---------+------+--------+-----+ |
| | Reportes   | | |Numero |Asunto    |Empresa  |Prior.|Estado  |Agente| |
| |            | | +-------+----------+---------+------+--------+-----+ |
| |            | | |TKT-   |No puedo  |Estudio  |Alta  |Nuevo   | --  | |
| |            | | |2026-  |acceder   |Palacios |      |        |     | |
| |            | | |001    |al sistema|         |      |        |     | |
| |            | | +-------+----------+---------+------+--------+-----+ |
| |            | | |TKT-   |Error en  |TechSoft |Media |En rev. |Ana  | |
| |            | | |2026-  |formato   |         |      |        |     | |
| |            | | |002    |F03       |         |      |        |     | |
| +------------+ +------------------------------------------------------+
```

### Mockup: Detalle de ticket con hilo

```
+-----------------------------------------------------------------------+
| SecuriForm > Tickets > TKT-2026-001                                   |
+-----------------------------------------------------------------------+
| +------------+ +------------------------------------------------------+
| | SecuriForm | | TKT-2026-001: No puedo acceder al sistema            |
| |            | +------------------------------------------------------+
| | ---------- | |                                                      |
| | Dashboard  | | Estado: [En revision v]  Prioridad: [Alta v]         |
| | Registros  | | Asignado a: [Ana Soporte v]                          |
| | Tickets    | | Empresa: Estudio Palacios | Creado por: Carlos P.   |
| | Reportes   | |                                                      |
| |            | | == Conversacion ==                                    |
| |            | |                                                      |
| |            | | [Carlos P.] 01/04/2026 10:30                          |
| |            | | No puedo acceder al panel, me sale error 403.         |
| |            | | Adjunto: [screenshot.png]                             |
| |            | |                                                      |
| |            | | [Ana Soporte] 01/04/2026 11:15                         |
| |            | | Revisando. Parece un tema de permisos.                |
| |            | |                                                      |
| |            | | [NOTA INTERNA - Ana] 01/04/2026 11:16                  |
| |            | | El usuario tiene rol solo_lectura, necesita usuario.  |
| |            | |                                                      |
| |            | | +------------------------------------------------+  |
| |            | | | Escribe tu respuesta...                         |  |
| |            | | |                                                  |  |
| |            | | +------------------------------------------------+  |
| |            | | [x] Nota interna    [Adjuntar]    [Enviar]          |
| +------------+ +------------------------------------------------------+
```

## Diseno Tecnico

### Modelos / Migraciones

```
YA EXISTEN — no se necesitan nuevas migraciones.

Tablas: tickets, ticket_mensajes, ticket_adjuntos, secuencias_ticket
Modelos: Ticket, TicketMensaje, TicketAdjunto
```

### Servicios

```php
// Logica de numeracion automatica en TicketResource al crear
// Usa tabla secuencias_ticket para generar TKT-YYYY-NNN
// Similar a como funciona RegistroResource con secuencias_registro
```

### UI (Filament)

```
Nuevos archivos:
- app/Filament/Resources/Tickets/TicketResource.php
- app/Filament/Resources/Tickets/Schemas/TicketForm.php
- app/Filament/Resources/Tickets/Tables/TicketsTable.php
- app/Filament/Resources/Tickets/Pages/ListTickets.php
- app/Filament/Resources/Tickets/Pages/CreateTicket.php
- app/Filament/Resources/Tickets/Pages/ViewTicket.php (hilo de conversacion)
```

## Analisis de Impacto

### Clasificacion

- [x] **Tipo A: Aditivo** — No toca codigo existente, bajo riesgo
- [ ] **Tipo B: Modificativo** — Modifica codigo existente, riesgo medio
- [ ] **Tipo C: Estructural** — Cambia arquitectura/modelos, alto riesgo

### Areas afectadas

| Area | Impacto | Tests existentes |
|------|---------|-----------------|
| Filament Resources | Nuevos archivos (aditivo) | No |
| Modelos existentes | Sin cambios | No |
| Migraciones | Sin cambios | No |

### Riesgos

- **Riesgo 1:** El hilo de conversacion con Livewire puede tener complejidad UI.
  - **Mitigacion:** Usar Filament Infolist + custom Livewire component minimo.

## Criterios de Aceptacion

- [ ] TicketResource con tabla filtrable (estado, prioridad, empresa, agente)
- [ ] Formulario de creacion con asunto, descripcion, categoria, prioridad
- [ ] Numeracion automatica TKT-YYYY-NNN
- [ ] Vista de detalle con hilo de mensajes (publicos + internos)
- [ ] Agentes pueden ver notas internas, usuarios normales no
- [ ] Upload de adjuntos en mensajes
- [ ] Accion para asignar/reasignar agente
- [ ] Acciones para cambiar estado (nuevo → en_revision → resuelto → cerrado)
- [ ] Tenant isolation: usuarios solo ven tickets de su empresa
- [ ] Super admin y agentes ven tickets de todas las empresas

## Alternativas Consideradas

### Alternativa A: Resource completo con ViewPage custom (Elegida)

- **Pro:** Usa patrones existentes de Filament, ViewPage permite UI rica para el hilo
- **Con:** ViewPage custom requiere mas codigo que un Resource estandar

### Alternativa B: Resource estandar sin hilo

- **Pro:** Mas simple, rapido de implementar
- **Con:** Sin hilo de conversacion pierde la funcionalidad core del helpdesk

## Estimacion

| Tarea | Estimacion |
|-------|-----------|
| TicketResource (tabla + formulario + numeracion) | 3 pts |
| ViewTicket con hilo de conversacion | 5 pts |
| Upload de adjuntos en mensajes | 2 pts |
| Asignacion de agentes + acciones de estado | 2 pts |
| Tests | 3 pts |
| **Total** | **15 pts** |

## Plan de Implementacion

1. **Sprint 1 (actual, quedan 5 dias):**
   - Issue 1: TicketResource tabla + formulario + numeracion (3 pts)
   - Issue 2: ViewTicket con hilo de conversacion (5 pts)
   - Issue 3: Adjuntos + asignacion + acciones (4 pts)
   - Issue 4: Tests (3 pts)
   - **Total: 15 pts** (Sprint 1 tiene 8 pts completados + 15 = 23 pts, dentro del target)

## Referencias

- [SEN-20](https://linear.app/genniality-nerve/issue/SEN-20) — US-0701: Filament TicketResource (crear ticket) — 5 pts
- [SEN-19](https://linear.app/genniality-nerve/issue/SEN-19) — US-0702: Listado de tickets (usuario/admin) — sin est.
- [SEN-18](https://linear.app/genniality-nerve/issue/SEN-18) — US-0703: Hilo de conversacion del ticket — sin est.
- [SEN-17](https://linear.app/genniality-nerve/issue/SEN-17) — US-0704: Panel de agente helpdesk — sin est.
- [SEN-16](https://linear.app/genniality-nerve/issue/SEN-16) — US-0705: Gestion del ticket por el agente — 5 pts
- [SEN-15](https://linear.app/genniality-nerve/issue/SEN-15) — US-0706: Adjuntos en tickets — sin est.
- [SEN-14](https://linear.app/genniality-nerve/issue/SEN-14) — US-0707: Reapertura de ticket resuelto — sin est.
- Nota: SEN-59 a SEN-62 fueron marcados como duplicados de estos issues
- RF-011: Helpdesk - Gestion de Tickets
- RF-012: Helpdesk - Hilo de Conversacion
- RF-013: Helpdesk - Adjuntos
