#!/bin/bash
chmod -R 775 /var/www/storage
chmod -R 775 /var/www/bootstrap/cache
php artisan migrate:fresh --force
php artisan config:cache
php artisan storage:link
php artisan config:clear
php artisan view:clear
php artisan cache:clear
php artisan livewire:discover

# Seed in background so supervisord starts immediately
php artisan db:seed --force &

# Start nginx and php-fpm immediately
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf