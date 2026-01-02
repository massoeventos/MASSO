FROM php:7.4.30-apache

RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl libonig-dev \
    libjpeg62-turbo-dev libpng-dev libfreetype6-dev libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd pdo pdo_mysql mbstring zip

RUN a2enmod rewrite

COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

COPY . .


# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install
