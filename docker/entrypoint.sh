#!/bin/sh
set -e

# Crear migraciones
php artisan migrate --seed --force

# Corregir propiedad y permisos del almacenamiento
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

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