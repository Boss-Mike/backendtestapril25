#!/bin/bash

echo "=== Initializing Laravel Multi-Tenant Expense Management API ==="

# Navigate to the project directory
cd /var/www/html

# Remove existing vendor directory if it causes issues
rm -rf vendor/

# Install PHP dependencies via Composer
echo "Installing PHP dependencies..."
if [ -f "composer.json" ]; then
    composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader 2>&1 | head -50
fi

# Create necessary directories
echo "Creating directories..."
mkdir -p storage/logs storage/framework/sessions storage/framework/views storage/framework/cache
mkdir -p bootstrap/cache

# Set permissions
echo "Setting permissions..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache /var/www/html

# Copy .env file if it doesn't exist
if [ ! -f ".env" ]; then
    echo "Creating .env file..."
    cp .env.example .env
fi

# Generate application key
echo "Generating application key..."
php artisan key:generate --force 2>&1 || true

# Wait for database to be ready
echo "Waiting for database to be ready..."
for i in {1..60}; do
    if mysql -h db -u expense_user -p expense_password -e "SELECT 1" &> /dev/null; then
        echo "Database is ready!"
        break
    fi
    echo "Waiting... ($i/60)"
    sleep 1
done

# Clear cache before migrations
echo "Clearing caches..."
php artisan config:clear 2>&1 || true
php artisan cache:clear 2>&1 || true

# Run migrations
echo "Running migrations..."
php artisan migrate --force 2>&1 || true

# Seed the database with test data (optional)
# php artisan db:seed

echo "=== Setup complete! ==="
echo "API is available at http://localhost:8000"
