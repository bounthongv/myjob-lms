FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite

COPY custom.ini /usr/local/etc/php/conf.d/custom.ini

EXPOSE 80
