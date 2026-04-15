#!/bin/bash
# =============================================================
# SecuriForm — Post-deploy (ejecutado por GitHub Actions en SSH)
# =============================================================

set -e

PROJECT_DIR="$HOME/sentinel-guard"
PUBLIC_HTML="$HOME/domains/co-de.com.pe/public_html/sentinel-guard"

# Detectar PHP 8.2-8.4
PHP_BIN=""
for v in 8.4 8.3 8.2; do
    for path in "/usr/bin/php${v}" "/opt/alt/php${v//.}/usr/bin/php" "/usr/local/bin/php${v}"; do
        if [ -x "$path" ]; then
            PHP_BIN="$path"
            break 2
        fi
    done
done
if [ -z "$PHP_BIN" ]; then
    PHP_BIN="php"
fi

cd "$PROJECT_DIR"

echo "[POST-DEPLOY] PHP: $($PHP_BIN -r 'echo PHP_VERSION;')"
echo "[POST-DEPLOY] Commit: $(git log --oneline -1 2>/dev/null || echo 'n/a')"

# ── Directorios de storage ───────────────────────────────────
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache

# ── .env ─────────────────────────────────────────────────────
if [ ! -f .env ]; then
    if [ -f .env.production ]; then
        cp .env.production .env
        echo "[POST-DEPLOY] .env creado desde .env.production"
    else
        echo "[POST-DEPLOY] ERROR: No existe .env ni .env.production"
        exit 1
    fi
fi

# ── Limpiar TODO el cache (antes de cualquier artisan) ───────
echo "[POST-DEPLOY] Limpiando cache..."
$PHP_BIN -r "if(function_exists('opcache_reset')) { opcache_reset(); echo 'OPcache limpiado'; } else { echo 'OPcache no disponible (CLI)'; }" || true
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/routes-v7.php
rm -f bootstrap/cache/events.php
$PHP_BIN artisan config:clear
$PHP_BIN artisan route:clear
$PHP_BIN artisan view:clear
$PHP_BIN artisan event:clear

# ── APP_KEY ──────────────────────────────────────────────────
if grep -q "^APP_KEY=$" .env 2>/dev/null; then
    $PHP_BIN artisan key:generate --force
    echo "[POST-DEPLOY] APP_KEY generada"
fi

# ── Migraciones ──────────────────────────────────────────────
echo "[POST-DEPLOY] Ejecutando migraciones..."
$PHP_BIN artisan migrate --force

# ── Optimización (re-cachear con config limpio) ──────────────
echo "[POST-DEPLOY] Optimizando..."
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
$PHP_BIN artisan event:cache
$PHP_BIN artisan icons:cache
$PHP_BIN artisan filament:cache-components
$PHP_BIN artisan storage:link --force 2>/dev/null || true

# ── Symlink public_html ──────────────────────────────────────
if [ -L "$PUBLIC_HTML" ]; then
    : # symlink ya existe
elif [ -d "$PUBLIC_HTML" ]; then
    rm -rf "$PUBLIC_HTML"
    ln -sfn "$PROJECT_DIR/public" "$PUBLIC_HTML"
    echo "[POST-DEPLOY] Symlink recreado"
else
    ln -sfn "$PROJECT_DIR/public" "$PUBLIC_HTML"
    echo "[POST-DEPLOY] Symlink creado"
fi

# ── Permisos ─────────────────────────────────────────────────
chmod -R 755 storage bootstrap/cache

# ── Invalidar OPcache de Apache (separado del CLI) ───────────
echo "[POST-DEPLOY] Invalidando OPcache de Apache..."
OPCACHE_FILE="$PROJECT_DIR/public/_opcache_reset.php"
cat > "$OPCACHE_FILE" <<'OPCACHE_PHP'
<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo 'OPCACHE_CLEARED';
} else {
    echo 'OPCACHE_NOT_AVAILABLE';
}
OPCACHE_PHP
# Llamar via HTTP para que Apache ejecute el reset en su proceso
RESULT=$(curl -sf --max-time 10 "https://sentinel-guard.co-de.com.pe/_opcache_reset.php" 2>/dev/null || echo "CURL_FAILED")
rm -f "$OPCACHE_FILE"
echo "[POST-DEPLOY] OPcache Apache: $RESULT"

echo "[POST-DEPLOY] Deploy completado: https://sentinel-guard.co-de.com.pe"
