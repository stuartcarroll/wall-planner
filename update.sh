#!/bin/bash

# Wall Planner Update Script
# Usage: ./update.sh

set -e

echo "🔄 Updating Wall Planner..."

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="/var/www/wall-planner"

print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Check if in correct directory
if [ ! -f "artisan" ]; then
    if [ -d "$APP_DIR" ]; then
        cd $APP_DIR
    else
        echo "Error: Not in Laravel directory and $APP_DIR doesn't exist"
        exit 1
    fi
fi

# Put application in maintenance mode
print_status "Putting application in maintenance mode..."
sudo -u www-data php artisan down

# Pull latest changes
print_status "Pulling latest changes from repository..."
sudo -u www-data git pull origin main

# Install/update Composer dependencies
print_status "Updating PHP dependencies..."
sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction

# Install/update Node.js dependencies
print_status "Updating Node.js dependencies..."
sudo -u www-data npm ci

# Build frontend assets
print_status "Rebuilding production assets..."
sudo -u www-data npm run build

# Run database migrations
print_status "Running database migrations..."
sudo -u www-data php artisan migrate --force --no-interaction

# Clear and optimize caches
print_status "Clearing and optimizing caches..."
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan cache:clear

sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Restart queue workers if using supervisor
if command -v supervisorctl &> /dev/null; then
    print_status "Restarting queue workers..."
    supervisorctl restart wall-planner-worker:* 2>/dev/null || true
fi

# Restart PHP-FPM
print_status "Restarting PHP-FPM..."
systemctl reload php8.2-fpm

# Bring application back online
print_status "Bringing application back online..."
sudo -u www-data php artisan up

print_success "🎉 Update completed successfully!"
print_status "Application is now online with the latest changes."