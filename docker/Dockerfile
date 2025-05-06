FROM php:7.4.30-apache

RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl libonig-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip

RUN a2enmod rewrite

COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# No copiamos el código fuente aquí porque ya se monta como volumen desde el host.
# Esto permite trabajar con los archivos en tiempo real sin necesidad de reconstruir la imagen.