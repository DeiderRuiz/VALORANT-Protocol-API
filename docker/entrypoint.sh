
#!/bin/sh
set -e

php artisan optimize:clear

# Ejecutar migraciones
php artisan migrate --seed --force -vvv

# Cachear configuración y rutas
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Iniciar PHP-FPM en segundo plano
php-fpm -D

# Iniciar Nginx en primer plano
nginx -g "daemon off;"