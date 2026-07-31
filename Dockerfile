# syntax=docker/dockerfile:1

# ---------- Stage 1: build the (currently unused-by-the-app) Vite assets ----------
# Only resources/views/welcome.blade.php references @vite. Nothing else in the app
# uses it (all real pages load static assets from public/assest_pharmacy directly),
# but without a build, Laravel throws when Blade hits @vite and no manifest exists.
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci
COPY resources/css ./resources/css
COPY resources/js ./resources/js
COPY vite.config.js ./
RUN npm run build

# ---------- Stage 2: application image ----------
FROM php:8.3-cli-alpine

RUN apk add --no-cache bash curl git

# mlocati/docker-php-extension-installer: pulls in build deps, compiles, then
# strips them again so the final image doesn't carry a full toolchain.
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo_mysql mbstring bcmath pcntl gd zip exif intl opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install vendor/ from just the lock files first so this layer is cached
# unless composer.json/composer.lock actually change.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist

COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-dev \
    && php artisan package:discover --ansi

RUN grep -q '^www-data:' /etc/group || addgroup -S www-data
RUN id -u www-data >/dev/null 2>&1 || adduser -D -S -G www-data www-data
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER www-data

EXPOSE 8080

ENTRYPOINT ["entrypoint.sh"]
