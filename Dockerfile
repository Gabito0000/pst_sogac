# syntax=docker/dockerfile:1

# =============================================================================
# ETAPA 1 — assets de Vite
# Se compilan aqui para que la imagen final no necesite Node.
# =============================================================================
FROM node:22-bookworm-slim AS assets

WORKDIR /build

# Primero los manifiestos: mientras no cambien, la capa se reutiliza.
COPY package.json package-lock.json ./
RUN npm ci

COPY resources/js ./resources/js
COPY resources/css ./resources/css
COPY public ./public
COPY vite.config.js ./

RUN npm run build


# =============================================================================
# ETAPA 2 — dependencias PHP
# Se instalan las de desarrollo tambien (pest, pint, boost, pail) para que
# dentro del contenedor funcionen `artisan test` y `vendor/bin/pint`.
# =============================================================================
FROM composer:2 AS vendor

WORKDIR /build

COPY composer.json composer.lock ./

RUN composer install \
        --no-scripts \
        --no-interaction \
        --no-autoloader \
        --prefer-dist

COPY . .

RUN composer dump-autoload --optimize --no-scripts


# =============================================================================
# ETAPA 3 — imagen final de la aplicacion (php-fpm)
# =============================================================================
FROM php:8.4-fpm-bookworm AS app

# Paquetes de sistema y extensiones que exige el proyecto:
#   libpq-dev / pgsql  -> base de datos PostgreSQL (DB_CONNECTION=pgsql)
#   libzip-dev / zip   -> composer y archivos comprimidos
#   libicu-dev / intl  -> Laravel lo usa para formato de fechas y numeros
#   libpng/jpeg/freetype + gd -> imagenes
#   libonig-dev / mbstring     -> obligatorio: composer.lock pide ext-mbstring
#   pcntl               -> señales del worker de colas (queue:work)
#
# NOTA: las imagenes oficiales de php ya traenopenssl, ctype, tokenizer,
# fileinfo, dom, libxml, session, hash, json, filter y pcre compiladas.
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq-dev \
        libzip-dev \
        libicu-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
        unzip \
        curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        pgsql \
        mbstring \
        zip \
        intl \
        gd \
        bcmath \
        pcntl \
        opcache \
    && apt-get purge -y --auto-remove \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Codigo de la aplicacion
COPY . .

# Dependencias ya resueltas en la etapa 2
COPY --from=vendor /build/vendor ./vendor

# Assets compilados en la etapa 1
COPY --from=assets /build/public/build ./public/build

# Ajustes de PHP para desarrollo
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

# Permisos de los directorios que Laravel necesita escribir
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && chmod -R 775 storage bootstrap/cache

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 9000

# El codigo va montado desde el host, y en Windows los permisos del volumen
# no heredan el chown de la imagen: ejecutar como root y ajustar en el
# entrypoint es lo unico que evita errores de "permission denied" al cachear.
# En un despliegue real si se debe usar www-data.
ENTRYPOINT ["/usr/local/bin/entrypoint"]
CMD ["php-fpm"]