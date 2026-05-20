FROM php:8.1-apache

RUN apt-get update \
    && apt-get install -y libzip-dev unzip zlib1g-dev libonig-dev \
    && docker-php-ext-install pdo pdo_mysql mysqli zip \
    && a2enmod rewrite

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && find /var/www/html -type d -exec chmod 755 {} \;

EXPOSE 80

CMD ["apache2-foreground"]
