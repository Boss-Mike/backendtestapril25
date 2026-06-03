FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    default-mysql-client \
    redis-tools \
    supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo pdo_mysql bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .
# Create necessary directories
RUN mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/bootstrap/cache \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/storage/framework/cache
# Install PHP dependencies
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --no-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/storage \
    && chmod -R 777 /var/www/html/bootstrap/cache

# Copy supervisord configuration
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY init.sh /var/www/html/init.sh
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /var/www/html/init.sh /usr/local/bin/entrypoint.sh

# Create directories
RUN mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/bootstrap/cache

# Expose port
EXPOSE 9000

# Run initialization
RUN /var/www/html/init.sh || true

# Start PHP-FPM
CMD ["php-fpm"]
