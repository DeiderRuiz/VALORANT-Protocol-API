#!/bin/sh
set -e

# Crear migraciones
php artisan migrate --seed --force

echo "=== STORAGE PERMISSIONS ==="

ls -ld /var/www/storage
ls -ld /var/www/storage/app
ls -ld /var/www/storage/app/public
ls -ld /var/www/storage/app/public/maps

echo "=== PHP USER ==="

id www-data

# Crear enlace simbólico para archivos públicos
php artisan storage:link

# Cachear configuración y rutas
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Iniciar PHP-FPM en segundo plano
php-fpm -F &

# Iniciar Nginx en primer plano
nginx -g "daemon off;"