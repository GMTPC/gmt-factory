#!/bin/sh
set -eu

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
fi

# APP_KEY จาก environment (docker-compose) มีผลเหนือ .env — generate เฉพาะตอนไม่มี key จริง ๆ
if [ -z "${APP_KEY:-}" ] && ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

touch database/database.sqlite
if [ ! -L public/storage ] && [ ! -e public/storage ]; then
    php artisan storage:link
fi
php artisan config:clear

chown -R www-data:www-data storage bootstrap/cache database
exec apache2-foreground
