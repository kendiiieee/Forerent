# Stage 1: Build assets
FROM php:8.4-cli AS builder

# Install Node.js, zip, git (required for Composer)
RUN apt-get update && apt-get install -y curl zip unzip git && \
    curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt-get install -y nodejs && \
    apt-get clean

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy everything first
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Install Node dependencies
RUN npm ci

RUN echo "VITE_APP_NAME=Forerent" > .env

RUN npm run build

# Stage 2: Production image
FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    nginx supervisor curl zip unzip git \
    libpng-dev libonig-dev libxml2-dev libpq-dev \
    libjpeg62-turbo-dev libfreetype6-dev \        
    && docker-php-ext-configure gd \
        --with-freetype --with-jpeg \            
    && docker-php-ext-install \
    pdo pdo_mysql pdo_pgsql \
    mbstring exif pcntl bcmath gd \
    && apt-get clean

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy app files
COPY . .

# Copy built assets from builder
COPY --from=builder /var/www/public/build ./public/build

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy config files
COPY ./docker/production/nginx/nginx.conf /etc/nginx/nginx.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

EXPOSE 80

COPY start.sh /start.sh
RUN chmod +x /start.sh
CMD ["/start.sh"]