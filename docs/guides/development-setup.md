# Guia de Setup de Desarrollo — SecuriForm

## Prerrequisitos

- **Docker Desktop** 24+ con Docker Compose v2
- **Git** 2.40+
- **Editor**: VS Code (recomendado) con extensiones: PHP Intelephense, Laravel Blade
- **No se necesita**: PHP, Composer, Node.js, MariaDB instalados en el host

## Clonar el Repositorio

```bash
git clone git@github.com:{org}/sentinel-guard.git
cd sentinel-guard
git checkout develop
```

## Levantar el Entorno

```bash
# Primera vez — setup completo
make init

# Siguientes veces — solo levantar
make up
```

`make init` ejecuta:
1. Construye imagen Docker
2. Levanta servicios (DB, Redis)
3. Crea proyecto Laravel (si no existe)
4. Instala Filament y dependencias
5. Ejecuta migraciones
6. Instala assets de Node

## Verificar que Todo Funciona

```bash
# Ver logs
make logs

# Entrar al contenedor
make shell
```

## URLs de Desarrollo

| Servicio | URL | Descripcion |
|----------|-----|-------------|
| App | http://localhost:8080 | Frontend Laravel |
| Panel Filament | http://localhost:8080/admin | Backoffice admin |
| phpMyAdmin | http://localhost:8081 | Gestion de base de datos |
| MailHog | http://localhost:8025 | Captura de emails |

## Comandos Frecuentes

### Docker

```bash
make up              # Levantar todos los servicios
make down            # Apagar todos los servicios
make restart         # Reiniciar
make logs            # Ver logs en tiempo real
make build           # Rebuild de imagenes
```

### Backend (Laravel)

```bash
make shell           # Shell interactivo dentro del container
make migrate         # Ejecutar migraciones
make migrate-fresh   # migrate:fresh --seed (borra datos)
make seed            # Ejecutar seeders
make test            # Ejecutar tests
make cache-clear     # Limpiar todos los caches
make tinker          # Laravel REPL
```

### Filament

```bash
make filament-user   # Crear usuario administrador
```

### Assets

```bash
make npm-dev         # Compilar assets en modo desarrollo (watch)
make npm-build       # Compilar assets para produccion
```

### Limpieza

```bash
make clean           # Borrar volumenes Docker (reset total)
```

## Estructura de Branches

```
main                    <- Produccion (no tocar directamente)
  +-- develop           <- Branch principal de desarrollo
        +-- feature/SEN-xxx-descripcion  <- Tu branch de trabajo
```

### Workflow diario

```bash
# 1. Actualizar develop
git checkout develop
git pull origin develop

# 2. Crear branch para tu issue
git checkout -b feature/SEN-123-nombre-descriptivo

# 3. Trabajar, commitear
git add <archivos>
git commit -m "SEN-123: descripcion imperativa"

# 4. Push y crear PR
git push -u origin feature/SEN-123-nombre-descriptivo
gh pr create --base develop --title "SEN-123: Titulo del cambio"
```

## Troubleshooting

### Los contenedores no arrancan

```bash
make down
docker system prune -f
make build
make up
```

### Error de permisos en storage/

```bash
make shell
chmod -R 775 storage bootstrap/cache
```

### La base de datos no conecta

Verificar que el servicio DB este healthy:
```bash
docker compose ps db
docker compose logs db
```

### MailHog no muestra emails

Verificar que `MAIL_HOST=mailhog` y `MAIL_PORT=1025` en `.env`.

## Datos de acceso

Despues de ejecutar `make migrate-fresh` o `make seed`:

- **Super Admin**: configurado via `make filament-user`
- Consultar el seeder para credenciales de prueba
