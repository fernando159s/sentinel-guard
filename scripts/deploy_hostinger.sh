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
PUBLIC_HTML="$HOME/domains/sentinel-guard.co-de.com.pe/public_html"
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
# PHP 8.5 no es compatible con openspout (requerido por Filament)
detect_php() {
    # Prioridad: 8.4 > 8.3 > 8.2  (excluye 8.5+)
    for v in 8.4 8.3 8.2; do
        for path in "/usr/bin/php${v}" "/opt/alt/php${v//.}/usr/bin/php" "/usr/local/bin/php${v}"; do
            if [ -x "$path" ]; then
                echo "$path"
                return 0
            fi
        done
    done
    # Fallback: php del sistema si es 8.2, 8.3 o 8.4
    local sys_minor
    sys_minor=$(php -r "echo PHP_MINOR_VERSION;" 2>/dev/null)
    local sys_major
    sys_major=$(php -r "echo PHP_MAJOR_VERSION;" 2>/dev/null)
    if [ "$sys_major" = "8" ] && [ "$sys_minor" -ge 2 ] && [ "$sys_minor" -le 4 ]; then
        echo "php"
        return 0
    fi
    return 1
}

PHP_BIN=$(detect_php) || error "No se encontró PHP 8.2, 8.3 o 8.4.
  PHP 8.5 NO es compatible (openspout/filament aún no lo soportan).
  Solución: hPanel → Avanzado → Configuración de PHP → PHP 8.4 (recomendado).
  Binarios disponibles: $(ls /usr/bin/php* 2>/dev/null | tr '\n' ' ')"

info "Verificando PHP..."
$PHP_BIN -v | head -1
PHP_VERSION=$($PHP_BIN -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
$PHP_BIN -r "
    \$major = PHP_MAJOR_VERSION;
    \$minor = PHP_MINOR_VERSION;
    if (\$major != 8 || \$minor < 2 || \$minor > 4) {
        echo \"ERROR: PHP {\$major}.{\$minor} no es compatible. Se requiere 8.2, 8.3 o 8.4.\n\";
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

# ── Crear symlink en public_html ─────────────────────────────
info "Configurando public_html..."

# Limpiar public_html (excepto .well-known para SSL)
if [ -d "$PUBLIC_HTML" ]; then
    find "$PUBLIC_HTML" -mindepth 1 -not -name '.well-known' -not -path '*/.well-known/*' -delete 2>/dev/null || true
fi

# Copiar .htaccess de redirección a public_html
cp "$PROJECT_DIR/public_html.htaccess" "$PUBLIC_HTML/.htaccess"

# Crear symlink del proyecto en el directorio del dominio
DOMAIN_DIR="$(dirname "$PUBLIC_HTML")"
ln -sfn "$PROJECT_DIR" "$DOMAIN_DIR/sentinel-guard"

info "Symlink creado: $DOMAIN_DIR/sentinel-guard -> $PROJECT_DIR"

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
echo -e "  URL: ${YELLOW}https://sentinel-guard.co-de.com.pe${NC}"
echo -e "  Admin: ${YELLOW}https://sentinel-guard.co-de.com.pe/admin${NC}"
echo ""
echo -e "  Proyecto: $PROJECT_DIR"
echo -e "  Public:   $PUBLIC_HTML"
echo ""

# Verificar que la app responde
$PHP_BIN artisan about 2>/dev/null | head -20 || true
