FROM php:8.5-apache

# System deps and PHP extensions
RUN apt-get update && apt-get install -y \
    ca-certificates \
    libzip-dev zip unzip curl git \
    libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd

RUN a2enmod rewrite

COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

COPY . .


# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

#RUN composer install
