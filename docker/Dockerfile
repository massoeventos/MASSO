FROM php:7.4.30-apache

RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl libonig-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip

RUN a2enmod rewrite

COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

COPY . .


# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install
