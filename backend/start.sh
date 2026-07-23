#!/bin/bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

sed -i "s/Listen 80/Listen ${PORT:-10000}/" /etc/apache2/ports.conf
sed -i "s/*:80/*:${PORT:-10000}/" /etc/apache2/sites-available/000-default.conf
sed -i "s/:80/:${PORT:-10000}/" /etc/apache2/apache2.conf

apache2-foreground
