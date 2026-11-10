#!/bib/Bash

# Iniciar php-fpm en background
php-fpm &

# Esperar un poco
sleep 3

# Generar key si no existe
php artisan key:generate --force

# Migraciones (opcional)
php artisan migrate --seed --force

# Cachear config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Iniciar ngix
ngnix -g "daemon off;"