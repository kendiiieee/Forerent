#!/bin/bash
set -e

# Ensure Laravel writable paths exist.
mkdir -p /var/www/storage/logs /var/www/bootstrap/cache
touch /var/www/storage/logs/laravel.log

# If php-fpm user exists, align ownership with runtime user.
if id -u www-data >/dev/null 2>&1; then
	chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
fi

chmod -R ug+rwX /var/www/storage /var/www/bootstrap/cache

# On production, default to stderr logging unless explicitly configured.
if [ "${APP_ENV}" = "production" ] && [ -z "${LOG_CHANNEL}" ]; then
	export LOG_CHANNEL=stderr
fi

if [ "${APP_ENV}" = "production" ] && [ "${LOG_CHANNEL}" = "stack" ] && [ -z "${LOG_STACK}" ]; then
	export LOG_STACK=stderr
fi

php artisan migrate --force
php artisan config:cache
php artisan storage:link
php artisan config:clear
php artisan view:clear
php artisan cache:clear

# Start nginx and php-fpm immediately
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf