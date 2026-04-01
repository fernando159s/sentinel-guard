# Proceso Agil — SecuriForm

## Resumen

SecuriForm utiliza un proceso agil basado en Sprints de 2 semanas, gestionado en Linear.
El equipo consta de dos desarrolladores full-stack: **Fernando** (humano) y **Claude AI**
(asistente de desarrollo). Ambos participan en todas las capas del stack (Laravel, Filament,
Docker, tests).

---

## Cadencia de Sprints

| Parametro           | Valor                |
|---------------------|----------------------|
| Duracion            | 2 semanas (10 dias habiles) |
| Herramienta         | Linear (Cycles)      |
| Dias habiles        | Lunes a Viernes      |
| Velocidad objetivo  | ~40-50 puntos/sprint (ajustable) |

---

## Ceremonias

### 1. Sprint Planning — Dia 1 (30 minutos)

**Objetivo:** Definir el alcance del sprint.

**Actividades:**
1. Revisar el backlog priorizado en Linear.
2. Seleccionar issues para el sprint segun capacidad y prioridad.
3. Verificar que cada issue tiene criterios de aceptacion claros.
4. Asignar issues iniciales (se pueden reasignar durante el sprint).
5. Mover issues seleccionados al Cycle activo en Linear.

**Resultado:** Sprint backlog definido con issues estimados y asignados.

### 2. Mid-Sprint Check — Dia 7 (15 minutos)

**Objetivo:** Detectar bloqueos y ajustar rumbo.

**Actividades:**
1. Revisar estado de cada issue en el board.
2. Identificar issues bloqueados o en riesgo.
3. Reasignar o re-priorizar si es necesario.
4. Decidir si se reduce o amplia el scope del sprint.

**Resultado:** Problemas identificados con acciones correctivas asignadas.

### 3. Sprint Review + Retrospectiva — Dia 14 (30 minutos)

**Objetivo:** Evaluar lo entregado y mejorar el proceso.

**Sprint Review (15 min):**
1. Demostrar las features completadas (funcionalidad real, no codigo).
2. Verificar que cumplen la Definition of Done.
3. Mover issues completados a "Done" en Linear.
4. Documentar issues que no se completaron y por que.

**Retrospectiva (15 min):**
1. Que funciono bien este sprint.
2. Que se puede mejorar.
3. Acciones concretas para el siguiente sprint.

**Formato de retro sugerido:**
```
+ Bien:   La generacion de exports PDF/Excel fue rapida y sin errores.
- Mejorar: Los tests del motor de formatos tardaron mas de lo estimado.
> Accion:  Agregar 1 punto extra a issues que involucren formatos dinamicos.
```

---

## Workflow por Issue

Cada issue sigue este flujo de estados en Linear:

```
Backlog > Todo > In Progress > In Review > Done
                                    |
                               Canceled / Duplicate
```

### Descripcion de cada estado

| Estado        | Significado                                                |
|---------------|------------------------------------------------------------|
| **Backlog**   | Issue creado pero no priorizado para ningun sprint.        |
| **Todo**      | Priorizado y asignado a un sprint (Cycle), listo para empezar. |
| **In Progress** | Alguien esta trabajando activamente en el issue.         |
| **In Review** | PR creado y esperando code review.                         |
| **Done**      | PR mergeado a develop, tests pasando, criterios cumplidos. |
| **Canceled**  | Issue descartado (ya no es necesario).                     |
| **Duplicate** | Issue duplicado de otro existente.                         |

### Reglas de transicion

1. Solo mover a **In Progress** cuando se empieza a trabajar activamente.
2. Crear la rama Git al mover a **In Progress**.
3. Mover a **In Review** cuando se crea el PR, no antes.
4. Solo mover a **Done** cuando se cumple la **Definition of Done**.
5. Nunca tener mas de 2 issues en **In Progress** por persona simultaneamente.

---

## Definition of Done

### Por Issue

Un issue se considera **Done** cuando:

- [ ] El codigo esta mergeado a `develop` via PR aprobado.
- [ ] Los tests unitarios y/o de feature estan escritos y pasan.
- [ ] `make test` pasa sin errores en todo el proyecto.
- [ ] Las migraciones se ejecutan sin errores (`make migrate`).
- [ ] El codigo sigue las convenciones de `CLAUDE.md`.
- [ ] Los commits siguen el formato `SEN-xxx: descripcion` (o `GEN-xxx` segun el issue).
- [ ] No hay regresiones en funcionalidad existente.
- [ ] Si es un panel Filament: las columnas, filtros y acciones funcionan correctamente.
- [ ] Si es una migracion: tiene rollback funcional.

### Por Sprint

Un sprint se considera exitoso cuando:

- [ ] Al menos el 80% de los puntos comprometidos estan en **Done**.
- [ ] No hay issues criticos (P0) pendientes.
- [ ] `develop` es desplegable (todos los tests pasan, Docker levanta sin errores).
- [ ] La retrospectiva se realizo y se documentaron las acciones de mejora.

---

## Estimacion

### Escala Fibonacci

| Puntos | Significado                                           | Ejemplo                                     |
|--------|-------------------------------------------------------|---------------------------------------------|
| **1**  | Trivial, menos de 1 hora                             | Corregir un typo, agregar un label          |
| **2**  | Simple, 1-2 horas                                    | Agregar una columna a una migracion         |
| **3**  | Moderado, medio dia                                  | CRUD basico de Filament con validaciones    |
| **5**  | Complejo, 1 dia                                      | Implementar motor de formatos dinamicos     |
| **8**  | Muy complejo, 2+ dias — **considerar dividir**       | Flujo completo de export con PDF y Excel    |

### Reglas de estimacion

1. Si un issue se estima en **8 puntos**, evaluar si se puede dividir en 2-3 issues mas pequenos.
2. Si no se puede estimar con confianza, hacer un **spike** (investigacion timeboxed de 2h) antes de estimar.
3. La estimacion incluye: desarrollo, tests, y code review.
4. No incluye: deploy a produccion ni QA manual externo.

---

## Proceso para nuevas features (post-MVP)

Una vez que el MVP este estable, las nuevas features siguen un proceso estructurado
para evitar romper la base existente.

### Fase 1: RFC (Request for Comments)

Antes de implementar una feature significativa, documentar un RFC en `docs/rfcs/`.

### Fase 2: Analisis de Impacto

Clasificar el cambio segun su nivel de impacto:

| Nivel          | Descripcion                                        | Riesgo | Ejemplo                                    |
|----------------|----------------------------------------------------|---------|--------------------------------------------|
| **Aditivo**    | Agrega funcionalidad nueva sin tocar lo existente  | Bajo    | Nuevo formato, nueva tabla, nuevo widget   |
| **Modificativo** | Cambia comportamiento existente de forma controlada | Medio | Agregar campo a un modelo, cambiar validacion |
| **Estructural** | Cambia arquitectura o relaciones fundamentales     | Alto    | Cambiar esquema de multi-tenancy, refactorizar helpdesk |

**Regla:** Los cambios **Estructurales** requieren:
- RFC aprobado por ambos desarrolladores.
- Plan de rollback documentado.
- Tests de regresion antes y despues.

### Fase 3: Descomposicion

Dividir la feature en issues atomicos siguiendo el patron:

```
1. Migracion / modelo (cambios en BD)
2. Logica de negocio (Service)
3. Panel Filament (Resource + Pages)
4. Tests (unitarios + feature)
```

Cada issue debe ser desplegable de forma independiente sin romper el sistema.

### Fase 4: Sprint Planning

Los issues descompuestos entran al backlog y se priorizan en el siguiente Sprint Planning.

---

## Priorizacion de Features

### Niveles de prioridad

| Nivel | Nombre        | Criterio                                              | Tiempo de respuesta |
|-------|---------------|-------------------------------------------------------|--------------------|
| **P0** | Revenue      | Impacta directamente la generacion de ingresos        | Sprint actual       |
| **P1** | Engagement   | Mejora retencion o experiencia del usuario             | Proximo sprint      |
| **P2** | Operational  | Mejora eficiencia operativa o developer experience     | Dentro de 2 sprints |
| **P3** | Nice-to-have | Mejora menor, no urgente                              | Backlog             |

### Regla de priorizacion en Sprint Planning

1. Completar todos los P0 antes de tomar P1.
2. No tomar P3 si hay P1 o P2 pendientes, salvo que sean issues de 1 punto que se pueden resolver en tiempo muerto.
3. Si aparece un P0 a mitad de sprint, se incorpora inmediatamente desplazando el issue de menor prioridad.

---

## Metricas del equipo

### Metricas que se rastrean

| Metrica              | Como se mide                                    | Objetivo         |
|----------------------|-------------------------------------------------|------------------|
| Velocidad            | Puntos completados por sprint                   | Estable +/- 10%  |
| Completion rate      | % de puntos comprometidos vs completados        | >= 80%           |
| Cycle time           | Tiempo promedio de In Progress a Done            | < 3 dias         |
| PR review time       | Tiempo desde PR creado hasta merge              | < 24 horas       |
| Test coverage        | % de codigo cubierto por tests                  | Creciente        |

### Revision de metricas

Se revisan en cada **Sprint Review** para detectar tendencias y ajustar estimaciones.
