#!/bin/bash

# Create necessary directories if they don't exist
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

# Set permissions
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/bootstrap/cache

# Copy env file if it doesn't exist
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Generate app key if not already set
if ! grep -q "^APP_KEY=base64:" /var/www/html/.env; then
    php /var/www/html/artisan key:generate
fi

# Run migrations
php /var/www/html/artisan migrate --force

# Clear cache
php /var/www/html/artisan config:clear
php /var/www/html/artisan cache:clear

echo "Laravel setup completed!"

# Start PHP-FPM
php-fpm
