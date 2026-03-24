#!/bin/bash
chmod -R 775 /var/www/storage
chmod -R 775 /var/www/bootstrap/cache
php artisan migrate:fresh --seed --force
php artisan config:cache
php artisan storage:link
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf