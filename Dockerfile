FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev zip \
    && docker-php-ext-install pdo pdo_mysql mbstring exif bcmath gd

# Fix Apache MPM (harus 1 saja)
RUN a2dismod mpm_event mpm_worker \
    && a2enmod mpm_prefork rewrite

# Set document root ke Laravel public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 🔥 PENTING: Apache listen ke PORT dari Railway
RUN sed -i 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf \
 && sed -i 's/:80/:${PORT}/g' /etc/apache2/sites-available/000-default.conf

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Railway butuh ini
EXPOSE ${PORT}

# 🔥 JALANKAN APACHE (WAJIB ADA)
CMD ["apache2-foreground"]
