#!/bin/sh

# Indicar que el script debe detener su ejecución si algún comando
# devuelve un código de error distinto de cero.
# Evita continuar con el arranque si una tarea de preparación falla.
set -e

# Ejecutar las migraciones pendientes de Laravel y los seeders.
# --force permite ejecutar estas operaciones sin confirmación interactiva
# en un entorno de producción.
php artisan migrate --seed --force

# Corregir la propiedad de los directorios que Laravel necesita utilizar.
# www-data es el usuario utilizado por los servicios web.
# Esto permite que la aplicación pueda escribir archivos y generar cachés.
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Crear el enlace simbólico que permite acceder públicamente
# a los archivos almacenados en storage/app/public.
# El enlace conecta public/storage con storage/app/public.
php artisan storage:link

# Generar cachés de Laravel para optimizar su funcionamiento.
# config:cache agrupa la configuración de la aplicación en una caché
php artisan config:cache
# route:cache genera una caché de las rutas registradas.
php artisan route:cache
# view:cache compila las vistas Blade para evitar compilarlas nuevamente durante cada solicitud.
php artisan view:cache

# Iniciar PHP-FPM en segundo plano.
# -F mantiene PHP-FPM en primer plano, evitando que se demonice.
# El operador & permite que el script continúe ejecutando
# las siguientes instrucciones sin esperar a que PHP-FPM termine.
php-fpm -F &

# Iniciar Nginx en primer plano.
# daemon off evita que Nginx se ejecute como proceso independiente en segundo plano.
# Esto permite mantener activo el contenedor mientras Nginx funciona.
nginx -g "daemon off;"