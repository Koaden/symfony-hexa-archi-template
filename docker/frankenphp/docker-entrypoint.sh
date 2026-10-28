#!/bin/sh
set -e

cd /app/apps/symfony

if [ "$APP_ENV" = "dev" ] && [ -f composer.json ]; then
    if [ ! -d "vendor" ] || [ -z "$(ls -A vendor 2>/dev/null)" ]; then
        echo "Installing composer dependencies..."
        composer install --no-interaction --prefer-dist
    fi
fi

exec "$@"
