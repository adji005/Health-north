FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git zip unzip libicu-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql intl mbstring

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN echo "DocumentRoot /var/www/html/public" >> /etc/apache2/sites-enabled/000-default.conf
RUN a2enmod rewrite

EXPOSE 80