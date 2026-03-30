FROM php:8.2-apache

# Instalar dependencias del sistema y extensiones PHP para Laravel + Filament
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libpq-dev \
    unzip \
    git \
    curl \
    gnupg \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        gd \
        zip \
        intl \
        mbstring \
        opcache \
        bcmath \
        exif \
        pcntl \
        redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Node.js 20 LTS (necesario para Vite / assets de Filament)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Habilitar mod_rewrite y mod_headers
RUN a2enmod rewrite headers

# Configurar Apache: DocumentRoot apunta a /public de Laravel
COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf

# Permitir .htaccess (AllowOverride All)
RUN sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# PHP config para desarrollo
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY docker/php.ini /usr/local/etc/php/conf.d/securiform.ini

# Instalar Composer 2
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Entrypoint para desarrollo
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
