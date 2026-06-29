#!/bin/sh

echo "Starting Laravel..."

if [ ! -d "vendor" ]; then
    echo "Installing dependencies..."
    composer install --no-interaction --prefer-dist
fi


# Aguarda banco

echo "Waiting MySQL..."

until php -r "
try {
new PDO(
'mysql:host=' . getenv('DB_HOST'),
getenv('DB_USERNAME'),
getenv('DB_PASSWORD')
);
echo 'ok';
} catch(Exception \$e){
exit(1);
}
"; do
    sleep 2
done


# APP KEY
if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    php artisan key:generate --force
fi


# Migrations
php artisan migrate --force


# Seed opcional
php artisan db:seed || true


# Storage
php artisan storage:link || true


# Cache
php artisan optimize:clear
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
fi

chmod -R 777 storage
chmod -R 777 bootstrap/cache

echo "Laravel Ready"

exec "$@"