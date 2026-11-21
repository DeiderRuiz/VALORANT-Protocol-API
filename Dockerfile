# Imagen base: PHP 8.3.20 con PHP-FPM.
# PHP-FPM es el gestor que permite a Nginx procesar las solicitudes PHP
# mediante procesos de PHP (FastCGI).
FROM php:8.3.20-fpm

# Instalar dependencias del sistema.
# apt-get update actualiza el índice de paquetes disponibles.
# apt-get install -y instala los paquetes sin solicitar confirmación.
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    unzip \
    libpq-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Nginx: servidor web que recibe las solicitudes HTTP.
# git, unzip y curl: herramientas auxiliares del sistema.
# libpq-dev: librerías necesarias para compilar la extensión
# de PHP que permite conectarse a PostgreSQL.
# Las librerías restantes proporcionan dependencias para extensiones
# de PHP relacionadas con imágenes, ZIP y otros componentes.

# Instalar extensiones de PHP.
# docker-php-ext-install es un script proporcionado por la imagen
# oficial de PHP que permite compilar e instalar extensiones.
RUN docker-php-ext-install \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip
# pdo_pgsql: conexión a PostgreSQL mediante PDO.
# mbstring: manejo de cadenas de caracteres multibyte.
# exif: lectura de metadatos EXIF de imágenes.
# pcntl: manejo de procesos, utilizado por algunas herramientas de PHP.
# bcmath: operaciones matemáticas de precisión arbitraria.
# gd: procesamiento de imágenes.
# zip: manejo de archivos ZIP.

# Ajustar la configuración de PHP-FPM para facilitar el registro de errores.
# sed modifica las líneas comentadas de la configuración de PHP-FPM
# para habilitar la salida de los trabajadores y el registro de errores.
RUN sed -i \
    -e 's/^;catch_workers_output =.*/catch_workers_output = yes/' \
    -e 's/^;php_admin_flag\[log_errors\] =.*/php_admin_flag[log_errors] = on/' \
    /usr/local/etc/php-fpm.d/www.conf

# Instalar composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer el directorio de trabajo de la aplicación.
# Las siguientes instrucciones se ejecutarán desde esta ubicación dentro del contenedor.
WORKDIR /var/www

# Copiar el código fuente del proyecto al contenedor.
COPY . .

# Instalar las dependencias de Laravel mediante Composer.
# --no-dev excluye las dependencias destinadas al desarrollo.
# --optimize-autoloader optimiza el autoloader de Composer
# para mejorar la carga de clases en producción.
RUN composer install --no-dev --optimize-autoloader

# Asignar permisos de propiedad a los directorios de Laravel
# que necesitan escribir archivos durante su ejecución.
# www-data es el usuario utilizado por los servicios web
# para ejecutar sus procesos.
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Copiar la configuración de Nginx al contenedor.
# Esta configuración define cómo Nginx recibe las solicitudes HTTP
# y las dirige a Laravel a través de PHP-FPM.
# COPY docker/ngnix.conf /etc/ngnix/sites-avaliable/default
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

# Copiar el script de arranque de la aplicación.
# Este script coordina el inicio de los servicios y las tareas
# de preparación necesarias para ejecutar Laravel.
COPY docker/entrypoint.sh /entrypoint.sh

# Eliminar posibles caracteres CRLF del archivo de arranque,
# convirtiéndolo al formato de saltos de línea compatible con Linux.
# Después, otorgar permisos de ejecución al script.
RUN sed -i 's/\r$//' /entrypoint.sh \
    && chmod +x /entrypoint.sh

# Declarar el puerto 10000 como puerto de referencia del contenedor.
# Render utiliza este puerto para comunicarse con el servicio web.
EXPOSE 10000

# Definir el script que se ejecutará al iniciar el contenedor.
# Este script se encarga de preparar y levantar los servicios.
CMD ["/entrypoint.sh"]