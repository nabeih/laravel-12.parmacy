#!/bin/bash
set -e

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
