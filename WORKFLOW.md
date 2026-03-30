# WORKFLOW.md — Flujo de trabajo SecuriForm

> Scrum + GitFlow + Linear integrados.

---

## Ramas Git

```
main            ← producción estable, solo recibe merges de release/* y hotfix/*
develop         ← integración, aquí se mergean todos los features terminados
feature/GEN-XX  ← una rama por user story (sale de develop, vuelve a develop)
release/vX.X    ← preparación para deploy (sale de develop, va a main + develop)
hotfix/GEN-XX   ← parches urgentes en producción (sale de main, va a main + develop)
```

### Convención de nombres de rama

| Tipo | Formato | Ejemplo |
|------|---------|---------|
| Feature | `feature/GEN-XX-descripcion-corta` | `feature/GEN-5-estructura-directorios` |
| Release | `release/vX.Y` | `release/v1.0` |
| Hotfix | `hotfix/GEN-XX-descripcion` | `hotfix/GEN-99-fix-login-csrf` |

---

## Flujo de una User Story (Scrum + GitFlow)

### 1. PLANIFICACION (Sprint Planning)

```
Linear: Backlog → Todo
```
- Se seleccionan stories del Backlog para el sprint actual
- Se mueven a **Todo** en Linear
- Se asigna responsable

### 2. DESARROLLO (In Progress)

```
Linear: Todo → In Progress
Git:    git checkout develop && git pull
        git checkout -b feature/GEN-XX-descripcion
```

- Mover la story a **In Progress** en Linear
- Crear rama feature desde `develop`
- Desarrollar con commits frecuentes
- Convención de commits:

```
feat(GEN-XX): descripción corta del cambio
fix(GEN-XX): corrección de bug
chore(GEN-XX): tarea de mantenimiento
refactor(GEN-XX): reestructuración sin cambio funcional
docs(GEN-XX): cambios en documentación
```

Ejemplo:
```bash
git commit -m "feat(GEN-10): login form with company selector and CSRF"
```

### 3. REVISION (In Review)

```
Linear: In Progress → In Review
Git:    git push -u origin feature/GEN-XX-descripcion
        # Crear Pull Request: feature/GEN-XX → develop
```

- Mover la story a **In Review** en Linear
- Push de la rama feature al remoto
- Crear PR apuntando a `develop`
- Revisar que cumple:
  - [ ] Prepared statements en todas las queries
  - [ ] CSRF en formularios POST
  - [ ] htmlspecialchars() en toda salida HTML
  - [ ] Tenant isolation (empresa_id en queries)
  - [ ] Auth check al inicio de cada metodo de controlador
  - [ ] Sin credenciales hardcodeadas

### 4. MERGE (Done)

```
Linear: In Review → Done
Git:    Merge PR a develop (squash o merge commit)
        Eliminar rama feature
```

- Aprobar y mergear el PR a `develop`
- Linear pasa a **Done**
- Eliminar la rama feature:

```bash
git checkout develop && git pull
git branch -d feature/GEN-XX-descripcion
```

### 5. RELEASE (Deploy)

Cuando se completa un sprint o un conjunto de features listo para produccion:

```bash
# Crear rama release desde develop
git checkout develop && git pull
git checkout -b release/v1.0

# Ajustes finales (version bump, tests finales)
git commit -m "chore: prepare release v1.0"

# Merge a main (produccion)
git checkout main && git pull
git merge --no-ff release/v1.0
git tag -a v1.0 -m "Release v1.0 - Sprint 1"

# Merge de vuelta a develop (para no perder los ajustes de release)
git checkout develop
git merge --no-ff release/v1.0

# Eliminar rama release
git branch -d release/v1.0
git push origin main develop --tags
```

### 6. HOTFIX (Emergencias en produccion)

```bash
# Sale de main
git checkout main && git pull
git checkout -b hotfix/GEN-XX-descripcion

# Fix rapido
git commit -m "fix(GEN-XX): descripcion del fix urgente"

# Merge a main
git checkout main
git merge --no-ff hotfix/GEN-XX-descripcion
git tag -a v1.0.1 -m "Hotfix: descripcion"

# Merge a develop
git checkout develop
git merge --no-ff hotfix/GEN-XX-descripcion

# Cleanup
git branch -d hotfix/GEN-XX-descripcion
git push origin main develop --tags
```

---

## Estados en Linear (Workflow)

```
Backlog ──→ Todo ──→ In Progress ──→ In Review ──→ Done
                          │                          │
                          └── (bloqueado) ───────────┘
                                                     │
                                              Canceled/Duplicate
```

| Estado | Significado | Quien lo mueve |
|--------|-------------|----------------|
| **Backlog** | Story definida, no priorizada para sprint | Product Owner |
| **Todo** | Priorizada para el sprint actual | Sprint Planning |
| **In Progress** | Desarrollo activo, rama feature creada | Desarrollador |
| **In Review** | PR creado, esperando revision | Desarrollador |
| **Done** | PR mergeado a develop | Reviewer |
| **Canceled** | Descartada | Product Owner |

---

## Sprints planificados

| Sprint | Duracion | Epicas | Story Points |
|--------|----------|--------|-------------|
| Sprint 1 | 2 semanas | EP-01 (Infraestructura) + EP-10 (Seguridad base) | ~31 SP |
| Sprint 2 | 2 semanas | EP-02 (Auth/Usuarios) | ~26 SP |
| Sprint 3 | 2 semanas | EP-03 (Multiempresa) + EP-04 (Motor formatos) | ~52 SP |
| Sprint 4 | 2 semanas | EP-05 (13 formatos) | ~46 SP |
| Sprint 5 | 2 semanas | EP-07 (Helpdesk) | ~42 SP |
| Sprint 6 | 2 semanas | EP-06 (Export) + EP-08 (Notif) + EP-09 (Dashboard) | ~55 SP |
| Sprint 7 | 2 semanas | EP-11 (Deploy) + pulido final | ~13 SP |

---

## Comandos rapidos para el dia a dia

```bash
# ── Empezar una story ──
# 1. Mover a "In Progress" en Linear
./scripts/linear_update_status.sh GEN-XX "In Progress"
# 2. Crear rama
git checkout develop && git pull
git checkout -b feature/GEN-XX-descripcion

# ── Terminar una story ──
# 1. Push y crear PR
git push -u origin feature/GEN-XX-descripcion
# 2. Mover a "In Review" en Linear
./scripts/linear_update_status.sh GEN-XX "In Review"

# ── Despues del merge ──
# 1. Mover a "Done" en Linear
./scripts/linear_update_status.sh GEN-XX "Done"
# 2. Limpiar rama local
git checkout develop && git pull
git branch -d feature/GEN-XX-descripcion

# ── Ver tareas del sprint ──
./scripts/linear_get_tasks.sh "Todo"
./scripts/linear_get_tasks.sh "In Progress"
```

---

## Checklist de PR (copiar en cada Pull Request)

```markdown
## Checklist de seguridad
- [ ] SQL: 100% prepared statements
- [ ] CSRF: token en cada form POST
- [ ] XSS: htmlspecialchars() en toda salida
- [ ] Auth: verificacion de sesion + rol en cada metodo
- [ ] Tenant: empresa_id en toda query de datos
- [ ] Uploads: validacion MIME + extension en servidor

## Checklist funcional
- [ ] Funcionalidad implementada segun descripcion de la story
- [ ] Sin errores de PHP (E_ALL, E_STRICT)
- [ ] Probado en navegador (Chrome/Firefox)
- [ ] Responsive (pantalla movil)
```
