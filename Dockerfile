FROM php:8.3.20-fpm

# Instalar dependencias del sistema
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

RUN docker-php-ext-install \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

RUN sed -i \
    -e 's/^;catch_workers_output =.*/catch_workers_output = yes/' \
    -e 's/^;php_admin_flag\[log_errors\] =.*/php_admin_flag[log_errors] = on/' \
    /usr/local/etc/php-fpm.d/www.conf

# Instalar composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear directorio app
WORKDIR /var/www

# Copiar proyecto
COPY . .

# Instalar dependencias de laravel
RUN composer install --no-dev --optimize-autoloader

# Permisos
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Copiar configuración de ngnix
# COPY docker/ngnix.conf /etc/ngnix/sites-avaliable/default
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

# Script de arranque
COPY docker/entrypoint.sh /entrypoint.sh
RUN sed -i 's/\r$//' /entrypoint.sh \
    && chmod +x /entrypoint.sh

EXPOSE 10000

CMD ["/entrypoint.sh"]