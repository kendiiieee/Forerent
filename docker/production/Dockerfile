# Stage 1: Build assets
FROM php:8.4-cli AS builder

RUN apt-get update && apt-get install -y curl zip unzip git && \
    curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt-get install -y nodejs && \
    apt-get clean

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# --- CACHE LAYER START ---
# Only copy dependency files first. 
# Docker will cache the "install" results unless these files change.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --ignore-platform-reqs

COPY package.json package-lock.json ./
RUN npm ci
# --- CACHE LAYER END ---

# Now copy the rest of the code
COPY . .

# Finalize composer (generate autoloader)
RUN composer dump-autoload --optimize --no-dev

RUN echo "VITE_APP_NAME=Forerent" > .env
RUN npm run build

# Stage 2: Production image
FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    nginx supervisor curl zip unzip git \
    libpng-dev libonig-dev libxml2-dev libpq-dev \
    && docker-php-ext-install \
    pdo pdo_mysql pdo_pgsql \
    mbstring exif pcntl bcmath gd \
    && apt-get clean

WORKDIR /var/www

# Copy vendor and build assets from builder stage
# This avoids running composer/npm install again in Stage 2!
COPY --from=builder /var/www/vendor ./vendor
COPY --from=builder /var/www/public/build ./public/build

# Copy the rest of the app
COPY . .

# Copy config files
COPY ./docker/production/nginx/nginx.conf /etc/nginx/nginx.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

EXPOSE 80

# Clean up CMD conflict (Always put the start script last)
COPY start.sh /start.sh
RUN chmod +x /start.sh
CMD ["/start.sh"]