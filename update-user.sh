#!/bin/bash

# Wall Planner User Update Script (No Sudo Required)
# Usage: ./update-user.sh

set -e

echo "🔄 Updating Wall Planner (user mode)..."

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Configuration
APP_DIR="$HOME/wall-planner"

# Check if in correct directory or navigate to app directory
if [ ! -f "artisan" ]; then
    if [ -d "$APP_DIR" ]; then
        print_status "Navigating to application directory: $APP_DIR"
        cd $APP_DIR
    else
        print_error "Laravel application not found. Please run deploy-user.sh first."
        exit 1
    fi
fi

print_status "Working in: $(pwd)"

# Check if we're in maintenance mode (optional)
MAINTENANCE_MODE=false
if [ -f "storage/framework/down" ]; then
    MAINTENANCE_MODE=true
    print_warning "Application is already in maintenance mode"
else
    # Put application in maintenance mode (optional, comment out if not needed)
    print_status "Putting application in maintenance mode..."
    php artisan down --message="Updating application..." --retry=60 2>/dev/null || {
        print_warning "Could not enable maintenance mode (this is okay)"
    }
fi

# Pull latest changes
print_status "Pulling latest changes from repository..."
git pull origin main

# Install/update Composer dependencies
print_status "Updating PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Install/update Node.js dependencies
print_status "Updating Node.js dependencies..."
npm ci --only=production

# Build frontend assets
print_status "Rebuilding production assets..."
npm run build

# Run database migrations
print_status "Running database migrations..."
php artisan migrate --force --no-interaction

# Clear and optimize caches
print_status "Clearing and optimizing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Update storage link if needed
if [ ! -L "public/storage" ]; then
    print_status "Creating storage symbolic link..."
    php artisan storage:link
fi

# Set permissions (if possible)
chmod -R 775 storage bootstrap/cache 2>/dev/null || {
    print_warning "Could not update some directory permissions"
}

# Bring application back online (if we put it in maintenance mode)
if [ "$MAINTENANCE_MODE" = false ]; then
    print_status "Bringing application back online..."
    php artisan up 2>/dev/null || {
        print_warning "Could not disable maintenance mode (this is okay if it wasn't enabled)"
    }
fi

# Test application
print_status "Testing application..."
if php artisan --version >/dev/null 2>&1; then
    print_success "Application test passed ✓"
else
    print_error "Application test failed"
    exit 1
fi

print_success "🎉 Update completed successfully!"
print_status "Application is now online with the latest changes."

# Show current status
echo ""
print_status "📊 Current Status:"
echo "  Git branch: $(git branch --show-current)"
echo "  Last commit: $(git log --oneline -1)"
echo "  PHP version: $(php -r 'echo PHP_VERSION;')"
echo "  Laravel version: $(php artisan --version | cut -d' ' -f3)"

echo ""
print_success "✨ Your Wall Planner application has been updated!"