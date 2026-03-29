#!/bin/bash
set -e

# 1. Ensure permissions are correct
mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 2. Wait for Database (Crucial for Cloud Deploys)
# This prevents the "Connection Refused" crash on Render
echo "Waiting for database connection..."
until php artisan db:monitor --databases=mysql > /dev/null 2>&1; do
  echo "Database is unavailable - sleeping"
  sleep 2
done
echo "Database is up!"

# 3. Production Optimizations
if [ "${APP_ENV}" = "production" ]; then
    echo "Running in production mode..."
    php artisan migrate --force
    # 'optimize' handles config, routes, and views in one go
    php artisan optimize
    php artisan storage:link
else
    echo "Running in development mode..."
    php artisan migrate
fi

# 4. Start the Engine
echo "Starting Supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf