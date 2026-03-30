# WORKFLOW.md — Flujo de trabajo profesional SecuriForm

> Scrum + GitFlow + Linear + GitHub integrados con automatizaciones.

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
| Feature | `feature/GEN-XX-descripcion`      | `feature/GEN-12-login-csrf`           |
| Release | `release/vX.Y`                    | `release/v1.0`                        |
| Hotfix  | `hotfix/GEN-XX-descripcion`       | `hotfix/GEN-99-fix-sql-injection`     |

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
git commit -m "feat(GEN-12): add login form with company selector and CSRF"
git commit -m "fix(GEN-45): prepared statement missing in ticket query"
git commit -m "chore(GEN-01): initial project structure and config"
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
[GEN-12] feat: login form with CSRF and company selector
[GEN-45] fix: SQL injection in ticket search
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
- [ ] SQL: 100% prepared statements
- [ ] CSRF: token en cada form POST
- [ ] XSS: htmlspecialchars() en toda salida HTML
- [ ] Auth: verificacion de sesion + rol en cada metodo
- [ ] Tenant: empresa_id en toda query de datos
- [ ] Uploads: validacion MIME + extension en servidor

## Functional checklist
- [ ] Funcionalidad segun la user story
- [ ] Sin errores PHP (E_ALL)
- [ ] Probado en navegador
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
git checkout -b feature/GEN-12-login-csrf
```

> Linear mueve automaticamente GEN-12 a **In Progress**.

#### 2. DESARROLLAR

```bash
# Commits frecuentes con el ID de Linear
git add app/controllers/AuthController.php
git commit -m "feat(GEN-12): add login form with company selector"

git add app/views/auth/login.php
git commit -m "feat(GEN-12): add login view with CSRF token"

git add app/helpers/Auth.php
git commit -m "feat(GEN-12): add session management and role verification"
```

> Cada commit con `GEN-12` aparece automaticamente en el issue de Linear.

#### 3. CREAR PR

```bash
git push -u origin feature/GEN-12-login-csrf

gh pr create \
  --base develop \
  --title "[GEN-12] feat: login with company selector and CSRF" \
  --body "$(cat <<'EOF'
## Summary
- Login form with company dropdown selector
- CSRF token generation and validation
- Session-based auth with role verification

closes GEN-12

## Security checklist
- [x] SQL: 100% prepared statements
- [x] CSRF: token en cada form POST
- [x] XSS: htmlspecialchars() en toda salida HTML
- [x] Auth: verificacion de sesion + rol en cada metodo
- [x] Tenant: empresa_id en toda query de datos
- [ ] Uploads: N/A

## Functional checklist
- [x] Funcionalidad segun la user story
- [x] Sin errores PHP (E_ALL)
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
git branch -d feature/GEN-12-login-csrf
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
git commit -m "chore: prepare release v1.0"

# Merge a main (produccion)
git checkout main && git pull
git merge --no-ff release/v1.0
git tag -a v1.0 -m "Release v1.0 - Sprint 1: Infraestructura base"

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
git checkout -b hotfix/GEN-99-fix-sql-injection

# Fix rapido
git commit -m "fix(GEN-99): sanitize input in ticket search query"

# Merge a main + tag
git checkout main
git merge --no-ff hotfix/GEN-99-fix-sql-injection
git tag -a v1.0.1 -m "Hotfix: SQL injection in ticket search"

# Merge a develop
git checkout develop
git merge --no-ff hotfix/GEN-99-fix-sql-injection

# Cleanup
git branch -d hotfix/GEN-99-fix-sql-injection
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

## 8. Sprints planificados

| Sprint   | Epicas                                              | ~SP |
|----------|-----------------------------------------------------|-----|
| Sprint 1 | EP-01 (Infraestructura) + EP-10 (Seguridad)         |  31 |
| Sprint 2 | EP-02 (Auth/Usuarios)                               |  26 |
| Sprint 3 | EP-03 (Multiempresa) + EP-04 (Motor formatos)       |  52 |
| Sprint 4 | EP-05 (13 formatos)                                 |  46 |
| Sprint 5 | EP-07 (Helpdesk)                                    |  42 |
| Sprint 6 | EP-06 (Export) + EP-08 (Notif) + EP-09 (Dashboard)  |  55 |
| Sprint 7 | EP-11 (Deploy) + pulido final                       |  13 |

---

## 9. Sesion de trabajo con Claude Code

### Iniciar sesion de trabajo

```bash
# 1. Ver que toca hoy
./scripts/linear_get_tasks.sh "In Progress"
./scripts/linear_get_tasks.sh "Todo"

# 2. Pegar la story en el prompt de Claude Code:
#    "Trabaja en GEN-XX: [titulo de la story]"

# 3. Claude Code crea la rama, desarrolla, y prepara el PR
```

### Cerrar sesion de trabajo

```bash
# Verificar estado limpio
git status

# Ver que quedo pendiente
./scripts/linear_get_tasks.sh "In Progress"
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
