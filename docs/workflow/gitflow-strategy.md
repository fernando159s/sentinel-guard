# Estrategia de Branching — GitFlow para SecuriForm

## Resumen

SecuriForm sigue una estrategia GitFlow adaptada para un equipo de dos desarrolladores
(Fernando + Claude AI). Toda rama nace de `develop`, se integra via Pull Request,
y solo llega a `main` a traves de ramas de release o hotfix.

---

## Estructura de ramas

```
main            <- produccion estable, cada merge se tagea (vX.Y.Z)
|
+-- hotfix/*    <- correcciones urgentes desde main
|
develop         <- integracion continua, base para features
|
+-- feature/*   <- nuevas funcionalidades
+-- fix/*       <- correcciones de bugs no urgentes
+-- chore/*     <- tareas de mantenimiento, config, dependencias
+-- test/*      <- adicion o mejora de tests
+-- refactor/*  <- reestructuracion de codigo sin cambio funcional
+-- release/*   <- preparacion de una version para produccion
```

### Reglas fundamentales

1. **Nunca** hacer commit directo a `main` ni a `develop`.
2. Todo cambio entra a traves de un **Pull Request** aprobado.
3. `main` siempre refleja el estado de produccion.
4. `develop` siempre es desplegable (no debe estar rota).

---

## Convencion de nombres de rama

### Formato

```
{tipo}/{ID-issue}-{descripcion-en-kebab-case}
```

Donde `{ID-issue}` es el identificador de Linear (ej: `SEN-12`, `GEN-95`).

### Tipos validos

| Tipo       | Uso                                              |
|------------|--------------------------------------------------|
| `feature`  | Nueva funcionalidad                              |
| `fix`      | Correccion de bug                                |
| `chore`    | Mantenimiento, dependencias, config              |
| `test`     | Tests nuevos o mejora de tests existentes        |
| `refactor` | Reestructuracion sin cambio funcional            |
| `hotfix`   | Correccion urgente directamente desde `main`     |
| `release`  | Preparacion de version para produccion           |

### Ejemplos concretos

```bash
feature/SEN-12-crud-registros-filament
feature/GEN-95-exportacion-pdf
fix/SEN-45-formato-fecha-registro
chore/SEN-08-actualizar-dependencias-composer
test/SEN-33-unit-tests-motor-formatos
refactor/SEN-21-extraer-export-service
hotfix/SEN-50-fix-login-produccion
release/v0.2.0
```

---

## Convencion de commits

### Formato

```
{ID-issue}: {descripcion imperativa en espanol o ingles}
```

- Usar verbo imperativo: "agregar", "corregir", "eliminar", "refactorizar" (o en ingles: "add", "fix", "remove", "refactor").
- Maximo 72 caracteres en la primera linea.
- Si se necesita mas detalle, dejar una linea en blanco y escribir el cuerpo.

### Ejemplos

```
SEN-12: agregar CRUD de registros en panel Filament

GEN-95: implementar exportacion a PDF con mPDF

Se usa mPDF para generar el documento con el formato de la empresa.
Incluye logo, encabezados y tabla de datos del registro.

SEN-08: actualizar spatie/laravel-permission a v6.4
```

### Commits co-authored con Claude AI

Cuando Claude escribe o co-escribe el codigo, el commit debe incluir
la linea de co-autoria:

```
SEN-12: agregar CRUD de registros en panel Filament

Co-Authored-By: Claude Opus 4.6 (1M context) <noreply@anthropic.com>
```

Cuando Fernando escribe el codigo completamente solo, no se agrega la linea.

---

## Flujo de trabajo diario (Feature / Fix / Chore)

### 1. Crear la rama desde develop

```bash
git checkout develop
git pull origin develop
git checkout -b feature/SEN-12-crud-registros-filament
```

### 2. Desarrollar y hacer commits atomicos

```bash
# Trabajo iterativo
git add app/Filament/Resources/RegistroResource.php
git commit -m "SEN-12: agregar RegistroResource con tabla y formulario"

git add database/migrations/
git commit -m "SEN-12: agregar migracion de tabla registros"

git add tests/Feature/
git commit -m "SEN-12: agregar tests para CRUD de registros"
```

### 3. Push y crear Pull Request

```bash
git push -u origin feature/SEN-12-crud-registros-filament
```

Crear PR en GitHub apuntando a `develop`:

```bash
gh pr create --base develop \
  --title "SEN-12: Agregar CRUD de registros en panel Filament" \
  --body "$(cat <<'EOF'
## Summary
- Crear RegistroResource con tabla, formulario y acciones CRUD
- Migracion para tabla `registros`
- Tests de Feature para validar operaciones CRUD

## Test plan
- [ ] Verificar que se listan registros correctamente
- [ ] Verificar creacion con validaciones
- [ ] Verificar edicion y eliminacion
- [ ] Ejecutar `make test` sin errores

## Linear
Closes SEN-12
EOF
)"
```

### 4. Code Review

- En un equipo de 2 (Fernando + Claude AI), **uno crea el PR y el otro revisa**.
- Si Fernando desarrollo la feature, Claude revisa el PR y viceversa.
- El reviewer puede aprobar o solicitar cambios.
- Una vez aprobado, se hace **squash merge** a `develop`.

### 5. Limpiar

```bash
git checkout develop
git pull origin develop
git branch -d feature/SEN-12-crud-registros-filament
```

---

## Flujo de Release

Las releases se preparan cuando `develop` tiene suficientes features estables
para una nueva version.

### 1. Crear rama de release desde develop

```bash
git checkout develop
git pull origin develop
git checkout -b release/v0.2.0
```

### 2. Preparar la release

En esta rama solo se permiten:
- Correccion de bugs encontrados en QA.
- Actualizacion de version en archivos de configuracion.
- Ajustes menores de documentacion.

**No se agregan features nuevas.**

### 3. Merge a main y tag

```bash
# Crear PR: release/v0.2.0 -> main
gh pr create --base main \
  --title "Release v0.2.0" \
  --body "Release con features SEN-12, SEN-15, SEN-18"

# Despues de aprobar y mergear:
git checkout main
git pull origin main
git tag -a v0.2.0 -m "Release v0.2.0: CRUD registros, exports, motor formatos"
git push origin v0.2.0
```

### 4. Back-merge a develop

```bash
git checkout develop
git pull origin develop
git merge main
git push origin develop
```

---

## Flujo de Hotfix

Los hotfixes corrigen errores criticos en produccion que no pueden esperar
al siguiente ciclo de release.

### 1. Crear rama desde main

```bash
git checkout main
git pull origin main
git checkout -b hotfix/SEN-50-fix-login-produccion
```

### 2. Corregir y commitear

```bash
git add app/Filament/Pages/Auth/Login.php
git commit -m "SEN-50: corregir validacion de token en login"
```

### 3. Merge a main (con tag) y back-merge a develop

```bash
# PR: hotfix/SEN-50 -> main
gh pr create --base main \
  --title "SEN-50: Hotfix - Corregir login en produccion" \
  --body "Corrige error critico de validacion en login Filament"

# Despues de mergear a main:
git checkout main
git pull origin main
git tag -a v0.1.1 -m "Hotfix v0.1.1: corregir login"
git push origin v0.1.1

# Back-merge a develop:
git checkout develop
git merge main
git push origin develop
```

---

## Versionado Semantico

Se sigue **SemVer** (`vMAJOR.MINOR.PATCH`):

| Componente | Cuando incrementar                                     | Ejemplo         |
|------------|-------------------------------------------------------|-----------------|
| MAJOR      | Cambios incompatibles en funcionalidad core           | v1.0.0 -> v2.0.0|
| MINOR      | Nueva funcionalidad compatible hacia atras            | v0.1.0 -> v0.2.0|
| PATCH      | Correccion de bugs, hotfixes                          | v0.1.0 -> v0.1.1|

Durante el MVP (pre-v1.0.0), el MAJOR se mantiene en 0.

---

## Diagrama de flujo completo

```
main -----*----------------*----------*--------- (tags: v0.1.0, v0.1.1, v0.2.0)
           \              ^ \        ^
            \    hotfix--/   \      /
             \                \    /
develop ------*---*---*---*----*--*----*----------
               \     ^       ^
                \   /       /
feature/X ---*--*  /      /
                  /      /
fix/Y -------*--*      /
                      /
release/v0.2.0 --*--*
```

---

## Checklist antes de mergear un PR

- [ ] Los tests pasan (`make test`).
- [ ] El codigo sigue las convenciones del proyecto (ver `CLAUDE.md`).
- [ ] Los commits tienen el formato correcto (`SEN-xxx: descripcion` o `GEN-xxx: descripcion`).
- [ ] La rama esta actualizada con `develop` (rebase o merge).
- [ ] El PR tiene descripcion clara con summary y test plan.
- [ ] El issue de Linear esta vinculado (mencionar el ID del issue en el PR).
