#!/bin/sh
set -eu

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

touch database/database.sqlite
php artisan storage:link 2>/dev/null || true
php artisan config:clear

chown -R www-data:www-data storage bootstrap/cache database
exec apache2-foreground
