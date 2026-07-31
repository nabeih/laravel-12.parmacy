#!/bin/bash
set -e

# Fail loudly and immediately if the database isn't configured for MySQL,
# instead of silently falling back to a throwaway local SQLite file (which
# this app's migrations aren't compatible with — it relies on MySQL-only
# features like ENUM columns and MODIFY COLUMN).
if [ "${DB_CONNECTION:-}" != "mysql" ]; then
    echo "FATAL: DB_CONNECTION is '${DB_CONNECTION:-<unset>}', expected 'mysql'." >&2
    echo "Set DB_CONNECTION=mysql plus DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD" >&2
    echo "on this service's Variables tab in Railway (reference the MySQL plugin's variables," >&2
    echo "e.g. DB_HOST=\${{MySQL.MYSQLHOST}})." >&2
    exit 1
fi

for var in DB_HOST DB_DATABASE DB_USERNAME; do
    if [ -z "${!var:-}" ]; then
        echo "FATAL: required env var $var is not set." >&2
        exit 1
    fi
done

if [ ! -L "public/storage" ]; then
    php artisan storage:link || true
fi

# Config/env values are only known at container start on Railway (not at
# `docker build` time), so caching happens here rather than in the Dockerfile.
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set RUN_MIGRATIONS=false in Railway if you'd rather run `php artisan migrate`
# manually (e.g. via the Railway CLI) instead of on every deploy.
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
