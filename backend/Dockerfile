FROM php:8.3-apache

WORKDIR /var/www/html

RUN a2enmod rewrite

RUN apt-get update && apt-get install -y libpq-dev && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_pgsql pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN chown -R www-data:www-data storage bootstrap/cache

RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf && \
    sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/apache2.conf

# Use Render's PORT env var (default 10000) instead of hardcoded 80
RUN sed -i 's/Listen 80/Listen ${PORT:-80}/' /etc/apache2/ports.conf && \
    sed -i 's/*:80/*:${PORT:-80}/' /etc/apache2/sites-available/000-default.conf && \
    sed -i 's/:80/:${PORT:-80}/' /etc/apache2/apache2.conf

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]
