#!/bin/sh
set -e

# Cachear configuración y rutas
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Iniciar PHP-FPM en segundo plano
php-fpm -F &

# Iniciar Nginx en primer plano
nginx -g "daemon off;"