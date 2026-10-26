FROM php:8.2-apache

# System deps and PHP extensions CodeIgniter needs
RUN apt-get update && apt-get install -y \
    libicu-dev libzip-dev unzip git libpq-dev \
 && docker-php-ext-install intl mysqli pdo pdo_mysql pdo_pgsql pgsql zip \
 && a2enmod rewrite \
 && rm -rf /var/lib/apt/lists/*

# CodeIgniter serves from /public, not the project root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
 && sed -ri -e 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .
RUN composer install --no-dev --optimize-autoloader \
 && chown -R www-data:www-data writable

EXPOSE 80