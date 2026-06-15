FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git zip unzip libicu-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql intl mbstring

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN cp .env.dev .env

RUN composer install --no-dev --optimize-autoloader

RUN php bin/console doctrine:schema:create --no-interaction || true

RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-enabled/000-default.conf

RUN a2enmod rewrite

EXPOSE 80