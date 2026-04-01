# RFC-{NNN}: {Titulo del Feature}

- **Estado**: Borrador | En revision | Aprobado | Rechazado | Implementado
- **Autor**: {nombre}
- **Fecha**: {YYYY-MM-DD}
- **Epic Linear**: EP-{NN} (si aplica)

---

## Problema

Describir claramente el problema o necesidad que este feature resuelve.
- Quien tiene este problema?
- Con que frecuencia ocurre?
- Que impacto tiene no resolverlo?

## Propuesta

Describir la solucion propuesta a alto nivel.
- Que va a hacer el feature?
- Como interactua el usuario con el?
- Que cambia en el sistema?

## Diseno Tecnico

### Modelos / Migraciones

```
# Nuevas tablas o columnas necesarias
```

### Servicios

```
# Nuevos services o modificaciones a existentes
```

### UI (Filament)

```
# Nuevas pantallas, Resources o componentes
```

## Analisis de Impacto

### Clasificacion

- [ ] **Tipo A: Aditivo** — No toca codigo existente, bajo riesgo
- [ ] **Tipo B: Modificativo** — Modifica codigo existente, riesgo medio
- [ ] **Tipo C: Estructural** — Cambia arquitectura/modelos, alto riesgo

### Areas afectadas

| Area | Impacto | Tests existentes |
|------|---------|-----------------|
| {modelo/servicio/resource} | {descripcion del cambio} | {Si/No} |

### Riesgos

- Riesgo 1: ...
- Mitigacion: ...

## Criterios de Aceptacion

- [ ] Criterio 1
- [ ] Criterio 2
- [ ] Criterio 3

## Alternativas Consideradas

### Alternativa A: {nombre}
- Pro: ...
- Con: ...

### Alternativa B: {nombre}
- Pro: ...
- Con: ...

## Estimacion

| Tarea | Estimacion |
|-------|-----------|
| Migraciones y modelos | X pts |
| Service layer | X pts |
| Filament UI | X pts |
| Tests | X pts |
| **Total** | **X pts** |

## Plan de Implementacion

1. Sprint N: {descripcion}
2. Sprint N+1: {descripcion} (si aplica)

## Referencias

- Link a issue de Linear
- Link a documentacion externa relevante
