FROM php:8.2-apache

# Cài các package cần thiết
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libonig-dev \
    default-mysql-client \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && a2enmod rewrite

# Copy code vào container
COPY . /var/www/html/

EXPOSE 80
CMD ["apache2-foreground"]