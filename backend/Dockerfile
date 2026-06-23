FROM php:8.4-cli-alpine

RUN apk add --no-cache postgresql-dev icu-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql intl zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copiamos el código fuente de Laravel dentro de la imagen
COPY . /app

# Instalamos las dependencias
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Damos permisos a las carpetas que Laravel necesita escribir
RUN chmod -R 777 storage bootstrap/cache

# Railway inyecta el puerto en la variable $PORT, localmente usamos el 80
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-80}
