FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY vite.config.ts tsconfig.json ./
RUN npm run build


# FrankenPHP rather than `php artisan serve`, which is a single-threaded dev
# server that Laravel's own docs tell you not to deploy.
FROM dunglas/frankenphp:1-php8.3-alpine AS app

RUN install-php-extensions pdo_pgsql opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --optimize-autoloader --prefer-dist

COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN composer dump-autoload --optimize \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8080

# Caching happens at boot, not at build: the environment variables these read
# are only present at runtime, so baking them into the image would capture the
# wrong values. Migrations are idempotent, so running them on start is safe.
CMD php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan migrate --force \
    && frankenphp php-server -r public/ --listen ":${PORT:-8080}"
