#!/bin/bash
# =============================================================
# SecuriForm — Deploy a Hostinger Shared Hosting
# Dominio: sentinel-guard.co-de.com.pe
# =============================================================
# Uso: Ejecutar via SSH en el servidor de Hostinger
#   bash deploy_hostinger.sh [fresh]
#
#   fresh  = primera vez (migrate:fresh --seed)
#   (vacío) = actualización (migrate solamente)
# =============================================================

set -e

# ── Configuración ────────────────────────────────────────────
PROJECT_DIR="$HOME/sentinel-guard"
PUBLIC_HTML="$HOME/domains/co-de.com.pe/public_html/sentinel-guard"
REPO_URL="https://github.com/fernando159s/sentinel-guard.git"
BRANCH="develop"

# ── Colores ──────────────────────────────────────────────────
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

info()  { echo -e "${GREEN}[INFO]${NC} $1"; }
warn()  { echo -e "${YELLOW}[WARN]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }

# ── Detectar PHP 8.2-8.4 automáticamente ─────────────────────
detect_php() {
    for v in 8.4 8.3 8.2; do
        for path in "/usr/bin/php${v}" "/opt/alt/php${v//.}/usr/bin/php" "/usr/local/bin/php${v}"; do
            if [ -x "$path" ]; then
                echo "$path"
                return 0
            fi
        done
    done
    local sys_minor sys_major
    sys_major=$(php -r "echo PHP_MAJOR_VERSION;" 2>/dev/null)
    sys_minor=$(php -r "echo PHP_MINOR_VERSION;" 2>/dev/null)
    if [ "$sys_major" = "8" ] && [ "$sys_minor" -ge 2 ] && [ "$sys_minor" -le 4 ]; then
        echo "php"
        return 0
    fi
    return 1
}

PHP_BIN=$(detect_php) || error "No se encontró PHP 8.2-8.4.
  PHP 8.5 NO es compatible (openspout/filament aún no lo soportan).
  Solución: hPanel → Avanzado → Configuración PHP → PHP 8.4.
  Binarios disponibles: $(ls /usr/bin/php* 2>/dev/null | tr '\n' ' ')"

info "Verificando PHP..."
$PHP_BIN -v | head -1
PHP_VERSION=$($PHP_BIN -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
$PHP_BIN -r "
    \$major = PHP_MAJOR_VERSION;
    \$minor = PHP_MINOR_VERSION;
    if (\$major != 8 || \$minor < 2 || \$minor > 4) {
        echo \"ERROR: PHP {\$major}.{\$minor} no compatible. Se requiere 8.2-8.4.\n\";
        exit(1);
    }
" || error "Versión de PHP incompatible."
info "PHP version: $PHP_VERSION (binario: $PHP_BIN)"

# ── Clonar o actualizar repo ─────────────────────────────────
if [ ! -d "$PROJECT_DIR" ]; then
    info "Clonando repositorio..."
    git clone -b "$BRANCH" "$REPO_URL" "$PROJECT_DIR"
else
    info "Actualizando repositorio..."
    cd "$PROJECT_DIR"
    git fetch origin
    git reset --hard "origin/$BRANCH"
fi

cd "$PROJECT_DIR"
info "En commit: $(git log --oneline -1)"

# ── Instalar dependencias PHP (sin dev) ──────────────────────
info "Instalando dependencias de Composer (producción)..."
if [ ! -f composer.phar ]; then
    curl -sS https://getcomposer.org/installer | $PHP_BIN
fi
$PHP_BIN composer.phar install --no-dev --optimize-autoloader --no-interaction

# ── Configurar .env ──────────────────────────────────────────
if [ ! -f .env ]; then
    if [ -f .env.production ]; then
        info "Copiando .env.production -> .env"
        cp .env.production .env
    else
        error "No se encontró .env.production. Crea el archivo primero."
    fi
fi

# ── Generar APP_KEY si no existe ─────────────────────────────
if grep -q "^APP_KEY=$" .env; then
    info "Generando APP_KEY..."
    $PHP_BIN artisan key:generate --force
else
    info "APP_KEY ya existe, saltando..."
fi

# ── Crear directorios necesarios ─────────────────────────────
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache

# ── Crear symlink de storage ─────────────────────────────────
info "Creando symlink de storage..."
$PHP_BIN artisan storage:link --force 2>/dev/null || true

# ── Migraciones ──────────────────────────────────────────────
if [ "$1" = "fresh" ]; then
    warn "Ejecutando migrate:fresh --seed (BORRA TODO)..."
    $PHP_BIN artisan migrate:fresh --seed --force
else
    info "Ejecutando migraciones pendientes..."
    $PHP_BIN artisan migrate --force
fi

# ── Optimizar para producción ────────────────────────────────
info "Optimizando para producción..."
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
$PHP_BIN artisan event:cache
$PHP_BIN artisan icons:cache
$PHP_BIN artisan filament:cache-components

# ── Configurar public_html (symlink a public/) ──────────────
info "Configurando public_html..."

# Eliminar carpeta/symlink anterior si existe
if [ -L "$PUBLIC_HTML" ]; then
    rm "$PUBLIC_HTML"
elif [ -d "$PUBLIC_HTML" ]; then
    rm -rf "$PUBLIC_HTML"
fi

# Crear symlink: public_html/sentinel-guard -> ~/sentinel-guard/public
ln -sfn "$PROJECT_DIR/public" "$PUBLIC_HTML"
info "Symlink: $PUBLIC_HTML -> $PROJECT_DIR/public"

# ── Permisos ─────────────────────────────────────────────────
info "Configurando permisos..."
chmod -R 755 "$PROJECT_DIR/storage"
chmod -R 755 "$PROJECT_DIR/bootstrap/cache"

# ── Verificación final ───────────────────────────────────────
echo ""
echo -e "${GREEN}=========================================${NC}"
echo -e "${GREEN}  Deploy completado exitosamente!${NC}"
echo -e "${GREEN}=========================================${NC}"
echo ""
echo -e "  URL:   ${YELLOW}https://sentinel-guard.co-de.com.pe${NC}"
echo -e "  Admin: ${YELLOW}https://sentinel-guard.co-de.com.pe/admin${NC}"
echo ""
echo -e "  Proyecto: $PROJECT_DIR"
echo -e "  Symlink:  $PUBLIC_HTML -> $PROJECT_DIR/public"
echo ""

$PHP_BIN artisan about 2>/dev/null | head -20 || true
