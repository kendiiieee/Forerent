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

# Build Firebase credentials file from env when secret file mounts are not readable.
if [ -n "${FIREBASE_CREDENTIALS_BASE64}" ] || [ -n "${FIREBASE_CREDENTIALS_JSON}" ]; then
	FIREBASE_CREDENTIALS_FILE="/var/www/storage/app/firebase-adminsdk.json"
	mkdir -p "$(dirname "$FIREBASE_CREDENTIALS_FILE")"

	if [ -n "${FIREBASE_CREDENTIALS_BASE64}" ]; then
		printf '%s' "${FIREBASE_CREDENTIALS_BASE64}" | base64 --decode > "$FIREBASE_CREDENTIALS_FILE"
	else
		printf '%s' "${FIREBASE_CREDENTIALS_JSON}" > "$FIREBASE_CREDENTIALS_FILE"
	fi

	chmod 600 "$FIREBASE_CREDENTIALS_FILE"

	if id -u www-data >/dev/null 2>&1; then
		chown www-data:www-data "$FIREBASE_CREDENTIALS_FILE"
	fi

	export FIREBASE_CREDENTIALS="$FIREBASE_CREDENTIALS_FILE"
fi

if [ -n "${FIREBASE_CREDENTIALS}" ] && [ ! -r "${FIREBASE_CREDENTIALS}" ]; then
	echo "[WARN] FIREBASE_CREDENTIALS is set but not readable by runtime user: ${FIREBASE_CREDENTIALS}" >&2
fi

php artisan migrate --force
php artisan config:cache
php artisan storage:link
php artisan config:clear
php artisan view:clear
php artisan cache:clear

# Start nginx and php-fpm immediately
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf