FROM php:8.2-apache

# HARD FIX MPM CONFLICT (WAJIB DI RAILWAY)
RUN rm -f /etc/apache2/mods-enabled/mpm_event.* \
         /etc/apache2/mods-enabled/mpm_worker.* \
    && rm -f /etc/apache2/mods-available/mpm_event.* \
             /etc/apache2/mods-available/mpm_worker.* \
    && a2enmod mpm_prefork rewrite

# Dependencies
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev zip \
    && docker-php-ext-install pdo pdo_mysql mbstring exif bcmath gd

# Laravel public root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Railway PORT
RUN sed -i 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf \
 && sed -i 's/:80/:${PORT}/g' /etc/apache2/sites-available/000-default.conf

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 775 storage bootstrap/cache

EXPOSE ${PORT}
CMD ["apache2-foreground"]
