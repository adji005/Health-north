FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git zip unzip libicu-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql intl mbstring

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN cp .env.dev .env

RUN composer install --no-dev --optimize-autoloader

RUN echo '#!/bin/bash\nphp /var/www/html/bin/console doctrine:schema:create --env=prod --no-interaction 2>/dev/null || true\napache2-foreground' > /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

RUN echo 'ServerName localhost\n\
<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
        DirectoryIndex index.php\n\
        <IfModule mod_rewrite.c>\n\
            RewriteEngine On\n\
            RewriteCond %{REQUEST_FILENAME} !-f\n\
            RewriteRule ^(.*)$ index.php [QSA,L]\n\
        </IfModule>\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-enabled/000-default.conf

RUN a2enmod rewrite headers

CMD ["/usr/local/bin/start.sh"]

EXPOSE 80