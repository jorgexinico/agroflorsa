FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN a2enmod rewrite

RUN sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf && \
    sed -ri 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf