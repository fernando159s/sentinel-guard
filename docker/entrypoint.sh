#!/bin/bash
set -e

cd /var/www/html

# Si no existe artisan, el proyecto Laravel aun no fue creado
if [ ! -f "artisan" ]; then
    echo "============================================="
    echo " Laravel no detectado."
    echo " Ejecuta: make init"
    echo "============================================="
    # Mantener Apache corriendo para que el contenedor no muera
    exec apache2-foreground
fi

# Instalar dependencias PHP si vendor esta vacio
if [ ! -f "vendor/autoload.php" ]; then
    echo "Instalando dependencias Composer..."
    composer install --no-interaction --prefer-dist
fi

# Instalar dependencias Node si node_modules esta vacio
if [ -f "package.json" ] && [ ! -d "node_modules/vite" ]; then
    echo "Instalando dependencias Node..."
    npm install
fi

# Generar APP_KEY si no existe
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    if grep -q "APP_KEY=$" .env 2>/dev/null || grep -q "APP_KEY=\"\"" .env 2>/dev/null; then
        echo "Generando APP_KEY..."
        php artisan key:generate --force
    fi
fi

# Cache de config y rutas en desarrollo (opcional, se puede quitar)
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# Permisos de storage y cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "============================================="
echo " SecuriForm corriendo en ${APP_URL:-http://localhost:8080}"
echo " phpMyAdmin:  http://localhost:8081"
echo " MailHog:     http://localhost:8025"
echo "============================================="

# Ejecutar Apache en primer plano
exec apache2-foreground
