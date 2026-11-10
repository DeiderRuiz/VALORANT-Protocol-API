#!/bin/sh
set -e

# Iniciar php-fpm en background
php-fpm &

# Esperar un poco
sleep 3

# Migraciones (opcional)
php artisan migrate --seed --force

# Cachear config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Iniciar ngix
nginx -g "daemon off;"