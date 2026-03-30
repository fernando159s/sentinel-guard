# WORKFLOW.md — Flujo de trabajo profesional SecuriForm

> Scrum + GitFlow + Linear + GitHub integrados con automatizaciones.
> Stack: Laravel 11 + Filament 3 + Docker

---

## 1. Configurar integracion Linear ↔ GitHub (una sola vez)

### Paso 1 — Conectar Linear con GitHub

1. Ir a **Linear > Settings > Integrations > GitHub**
2. Click **Connect** y autorizar con tu cuenta de GitHub
3. Seleccionar el repositorio `fernando159s/sentinel-guard`
4. Esperar a que Linear confirme la conexion

### Paso 2 — Configurar automatizaciones de estado

En **Linear > Team Settings > Workflow > Auto-close**:

| Evento Git/PR                    | Estado Linear automatico |
|----------------------------------|--------------------------|
| Rama creada con ID de issue      | **In Progress**          |
| PR abierto / ready for review    | **In Review**            |
| PR mergeado                      | **Done**                 |

### Paso 3 — Preferencias personales (opcional)

En **Linear > Preferences > Account**:

- [x] Auto-assign issue when copying branch name
- [x] Auto-move issue to "In Progress" on branch copy

---

## 2. Ramas Git

```
main            <- produccion estable, solo recibe merges de release/* y hotfix/*
develop         <- integracion, aqui se mergean todos los features terminados
feature/GEN-XX  <- una rama por user story (sale de develop, vuelve a develop)
release/vX.Y    <- preparacion para deploy (sale de develop, va a main + develop)
hotfix/GEN-XX   <- parches urgentes en produccion (sale de main, va a main + develop)
```

### Convencion de nombres de rama

| Tipo    | Formato                           | Ejemplo                               |
|---------|-----------------------------------|---------------------------------------|
| Feature | `feature/GEN-XX-descripcion`      | `feature/GEN-12-login-filament`       |
| Release | `release/vX.Y`                    | `release/v1.0`                        |
| Hotfix  | `hotfix/GEN-XX-descripcion`       | `hotfix/GEN-99-fix-tenant-scope`      |

> **Importante:** El ID de Linear (`GEN-XX`) en el nombre de la rama es lo que
> activa la conexion automatica entre GitHub y Linear. Sin el ID, no hay link.

---

## 3. Convencion de commits

Formato: `tipo(GEN-XX): descripcion corta`

```
feat(GEN-12):     nueva funcionalidad
fix(GEN-12):      correccion de bug
chore(GEN-12):    mantenimiento, config, dependencias
refactor(GEN-12): reestructuracion sin cambio funcional
docs(GEN-12):     documentacion
style(GEN-12):    formato, espacios, sin cambio logico
test(GEN-12):     agregar o modificar tests
```

Ejemplos reales:

```bash
git commit -m "feat(GEN-12): add Filament login page with company selector"
git commit -m "fix(GEN-45): fix tenant scope bypass in TicketResource"
git commit -m "chore(GEN-01): configure Docker + Laravel + Filament"
```

> Incluir `GEN-XX` en el commit lo vincula automaticamente al issue en Linear.

---

## 4. Convencion de Pull Requests

### Titulo del PR

```
[GEN-XX] tipo: descripcion corta
```

Ejemplos:

```
[GEN-12] feat: Filament login with company selector
[GEN-45] fix: tenant scope bypass in ticket queries
```

### Body del PR — usar magic words

Las **magic words** en el body del PR hacen que Linear cierre automaticamente
el issue cuando el PR se mergea:

```
closes GEN-12
fixes GEN-12
resolves GEN-12
```

### Template de PR

```markdown
## Summary
- [Descripcion de los cambios]

closes GEN-XX

## Security checklist
- [ ] SQL: Eloquent/Query Builder, sin raw queries sin bindings
- [ ] CSRF: Laravel lo maneja (verificar en forms custom fuera de Filament)
- [ ] XSS: Blade {{ }} en toda salida (nunca {!! !!} con datos de usuario)
- [ ] Auth: Policies + middleware aplicados correctamente
- [ ] Tenant: Global Scope activo, verificar que no se bypasea
- [ ] Uploads: Validacion MIME en servidor, storage privado

## Functional checklist
- [ ] Funcionalidad segun la user story
- [ ] Sin errores PHP (`make logs`)
- [ ] Tests pasan (`make test`)
- [ ] Probado en navegador (panel Filament)
- [ ] Responsive (mobile)
```

---

## 5. Flujo completo de una User Story

### Ciclo de vida automatizado

```
                        Linear                          GitHub
                        ------                          ------
Sprint Planning    Backlog -> Todo
                                         git checkout -b feature/GEN-XX-desc
Crear rama         -> In Progress (auto)
                                         commits con GEN-XX en mensaje
Desarrollo         (linked commits)
                                         git push + crear PR
Abrir PR           -> In Review (auto)
                                         code review + aprobar
Mergear PR         -> Done (auto)
                                         git branch -d feature/GEN-XX
```

> Con la integracion activa, **ya no necesitas cambiar estados manualmente** en
> Linear. Todo se actualiza solo basado en tus acciones en Git/GitHub.

### Paso a paso

#### 1. TOMAR UNA STORY

```bash
# Ver stories del sprint actual en Linear
./scripts/linear_get_tasks.sh "Todo"

# Desde Linear: click en la story > Ctrl+Shift+. para copiar nombre de rama
# O crear manualmente:
git checkout develop && git pull
git checkout -b feature/GEN-12-login-filament
```

> Linear mueve automaticamente GEN-12 a **In Progress**.

#### 2. DESARROLLAR

```bash
# Levantar entorno Docker (si no esta corriendo)
make up

# Ejemplo: generar un Filament Resource
make shell
php artisan make:filament-resource Empresa --generate

# Commits frecuentes con el ID de Linear
git add app/Filament/Resources/EmpresaResource.php
git commit -m "feat(GEN-12): add EmpresaResource with CRUD forms"

git add app/Models/Empresa.php database/migrations/
git commit -m "feat(GEN-12): add Empresa model and migration"

git add app/Policies/EmpresaPolicy.php
git commit -m "feat(GEN-12): add EmpresaPolicy for role-based access"
```

> Cada commit con `GEN-12` aparece automaticamente en el issue de Linear.

#### 3. CREAR PR

```bash
git push -u origin feature/GEN-12-login-filament

gh pr create \
  --base develop \
  --title "[GEN-12] feat: Filament login with company selector" \
  --body "$(cat <<'EOF'
## Summary
- Custom Filament login page with company dropdown
- Empresa model + migration + seeder
- EmpresaPolicy for super_admin access

closes GEN-12

## Security checklist
- [x] SQL: Eloquent queries only
- [x] CSRF: Filament handles it
- [x] XSS: Blade {{ }} escaping
- [x] Auth: Policy + middleware
- [x] Tenant: Global Scope active
- [ ] Uploads: N/A

## Functional checklist
- [x] Funcionalidad segun la user story
- [x] Sin errores PHP
- [x] Tests pasan
- [x] Probado en navegador
- [x] Responsive (mobile)
EOF
)"
```

> Linear mueve automaticamente GEN-12 a **In Review**.

#### 4. MERGE

```bash
# Aprobar y mergear via GitHub (squash merge recomendado)
gh pr merge --squash

# Limpiar rama local
git checkout develop && git pull
git branch -d feature/GEN-12-login-filament
```

> Linear mueve automaticamente GEN-12 a **Done**.

---

## 6. Release y Deploy

### Crear release (al terminar un sprint)

```bash
# Crear rama release desde develop
git checkout develop && git pull
git checkout -b release/v1.0

# Ajustes finales (version bump, tests finales)
php artisan test
git commit -m "chore: prepare release v1.0"

# Merge a main (produccion)
git checkout main && git pull
git merge --no-ff release/v1.0
git tag -a v1.0 -m "Release v1.0 - Sprint 1: Infraestructura Laravel + Docker"

# Merge de vuelta a develop
git checkout develop
git merge --no-ff release/v1.0

# Cleanup y push
git branch -d release/v1.0
git push origin main develop --tags
```

### Hotfix (emergencias en produccion)

```bash
# Sale de main
git checkout main && git pull
git checkout -b hotfix/GEN-99-fix-tenant-scope

# Fix rapido
git commit -m "fix(GEN-99): fix tenant scope bypass in RegistroResource"

# Merge a main + tag
git checkout main
git merge --no-ff hotfix/GEN-99-fix-tenant-scope
git tag -a v1.0.1 -m "Hotfix: tenant scope bypass in registros"

# Merge a develop
git checkout develop
git merge --no-ff hotfix/GEN-99-fix-tenant-scope

# Cleanup
git branch -d hotfix/GEN-99-fix-tenant-scope
git push origin main develop --tags
```

---

## 7. Estados en Linear

```text
Backlog --> Todo --> In Progress --> In Review --> Done
                        |                          |
                        +-- (bloqueado) -----------+
                                                   |
                                            Canceled/Duplicate
```

| Estado          | Trigger                            | Quien/Que lo mueve          |
|-----------------|------------------------------------|-----------------------------|
| **Backlog**     | Story definida                     | Product Owner               |
| **Todo**        | Sprint planning                    | Sprint Planning             |
| **In Progress** | Rama creada con GEN-XX             | GitHub integration (auto)   |
| **In Review**   | PR abierto                         | GitHub integration (auto)   |
| **Done**        | PR mergeado / magic word `closes`  | GitHub integration (auto)   |
| **Canceled**    | Story descartada                   | Product Owner               |

---

## 8. Sprints planificados (Laravel + Filament)

| Sprint   | Epicas                                                    | ~SP | Foco                                    |
|----------|-----------------------------------------------------------|-----|-----------------------------------------|
| Sprint 1 | EP-01 (Infra Docker+Laravel) + EP-10 (Seguridad base)    |  40 | Docker, Laravel, Filament, migraciones  |
| Sprint 2 | EP-02 (Auth/Usuarios) + EP-03 (Multiempresa)             |  34 | Login, users, empresas, multi-tenancy   |
| Sprint 3 | EP-04 (Motor formatos) + EP-05 parte 1 (F01-F07)         |  34 | Registros base + primeros 7 formatos    |
| Sprint 4 | EP-05 parte 2 (F08-F13) + EP-06 (Exportacion)            |  33 | Últimos 6 formatos + PDF/Excel          |
| Sprint 5 | EP-07 (Helpdesk) + EP-08 (Notificaciones)                |  44 | Tickets, hilos, email                   |
| Sprint 6 | EP-09 (Dashboard) + EP-11 (Deploy) + pulido               |  22 | Widgets, gráficos, deploy, docs         |

> **Total:** 47 user stories, ~207 story points, 6 sprints de 2 semanas.
> Con Filament, se eliminaron 7 stories que el framework resuelve out-of-the-box
> (Router manual, Database singleton, CSRF helper, session management, etc.)

---

## 9. Sesion de trabajo con Claude Code

### Iniciar sesion de trabajo

```bash
# 1. Levantar Docker
make up

# 2. Ver que toca hoy
./scripts/linear_get_tasks.sh "In Progress"
./scripts/linear_get_tasks.sh "Todo"

# 3. Pegar la story en el prompt de Claude Code:
#    "Trabaja en GEN-XX: [titulo de la story]"

# 4. Claude Code crea la rama, desarrolla, y prepara el PR
```

### Cerrar sesion de trabajo

```bash
# Verificar estado limpio
git status

# Ver que quedo pendiente
./scripts/linear_get_tasks.sh "In Progress"

# Parar Docker (opcional)
make down
```

---

## Resumen: lo que cambia con la integracion

| Antes (manual)                          | Ahora (automatizado)                           |
|-----------------------------------------|------------------------------------------------|
| Cambiar estado en Linear a mano         | Linear se actualiza solo con eventos de Git    |
| Copiar IDs de Linear manualmente        | Ctrl+Shift+. copia el nombre de rama           |
| Buscar PRs relacionados a una story     | Linear muestra PRs y commits vinculados        |
| Recordar actualizar estado al mergear   | `closes GEN-XX` en el PR lo hace automatico    |
| Scripts `linear_update_status.sh`       | Solo como fallback si algo no se auto-actualiza|
