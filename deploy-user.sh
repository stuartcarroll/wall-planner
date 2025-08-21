#!/bin/bash

# Wall Planner User Deployment Script (No Sudo Required)
# Usage: ./deploy-user.sh

set -e

echo "🚀 Starting Wall Planner deployment (user mode)..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="$HOME/wall-planner"
REPO_URL="https://github.com/stuartcarroll/wall-planner.git"
BRANCH="main"

# Function to print colored output
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

# Check if required commands exist
check_command() {
    if ! command -v $1 &> /dev/null; then
        print_error "$1 is required but not installed. Please install it first."
        exit 1
    fi
}

print_status "Checking required commands..."
check_command "git"
check_command "php"
check_command "composer"
check_command "node"
check_command "npm"

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
if [[ "$PHP_VERSION" < "8.1" ]]; then
    print_error "PHP 8.1+ is required. Current version: $PHP_VERSION"
    exit 1
fi
print_success "PHP version: $PHP_VERSION ✓"

# Check Node version
NODE_VERSION=$(node -v)
print_success "Node.js version: $NODE_VERSION ✓"

# Create application directory if it doesn't exist
if [ ! -d "$APP_DIR" ]; then
    print_status "Creating application directory at $APP_DIR..."
    mkdir -p $APP_DIR
fi

# Navigate to app directory
cd $APP_DIR
print_status "Working in: $(pwd)"

# Git operations
if [ -d ".git" ]; then
    print_status "Pulling latest changes from repository..."
    git fetch origin
    git reset --hard origin/$BRANCH
else
    print_status "Cloning repository..."
    rm -rf * .* 2>/dev/null || true
    git clone $REPO_URL .
    git checkout $BRANCH
fi

# Install Composer dependencies
print_status "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Install Node.js dependencies
print_status "Installing Node.js dependencies..."
npm ci --only=production

# Build frontend assets
print_status "Building production assets..."
npm run build

# Set up environment file
if [ ! -f ".env" ]; then
    print_status "Creating environment file..."
    cp .env.example .env
    
    # Generate application key
    php artisan key:generate --no-interaction
    
    print_warning "Environment file created. Please configure your .env file:"
    print_warning "Edit: $APP_DIR/.env"
    print_warning ""
    print_warning "Required settings to configure:"
    print_warning "- Database connection (DB_*)"
    print_warning "- Application URL (APP_URL)"
    print_warning "- Mail settings (if needed)"
    print_warning ""
    
    # Ask if user wants to edit now
    read -p "Would you like to edit .env now? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        ${EDITOR:-nano} .env
    fi
fi

# Check if .env is configured
if grep -q "APP_KEY=base64:" .env && ! grep -q "DB_DATABASE=laravel" .env; then
    print_success ".env file appears to be configured ✓"
else
    print_warning ".env file may need configuration"
    print_warning "Please ensure database and other settings are correct"
fi

# Test database connection
print_status "Testing database connection..."
if php artisan migrate:status >/dev/null 2>&1; then
    print_success "Database connection successful ✓"
else
    print_error "Database connection failed. Please check your .env configuration"
    print_error "Run 'php artisan migrate:status' to test"
    exit 1
fi

# Run database migrations
print_status "Running database migrations..."
php artisan migrate --force --no-interaction

# Create storage link if it doesn't exist
if [ ! -L "public/storage" ]; then
    print_status "Creating storage symbolic link..."
    php artisan storage:link
fi

# Set up proper permissions for storage and cache
print_status "Setting up directory permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || {
    print_warning "Could not set permissions on some directories"
    print_warning "You may need to manually set permissions for:"
    print_warning "- storage/ (writable)"
    print_warning "- bootstrap/cache/ (writable)"
}

# Clear and optimize caches
print_status "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Test if application works
print_status "Testing application..."
if php artisan --version >/dev/null 2>&1; then
    print_success "Application is working ✓"
else
    print_error "Application test failed"
    exit 1
fi

# Show deployment information
print_success "🎉 Deployment completed successfully!"
echo ""
print_status "📋 Deployment Summary:"
print_status "├── Application directory: $APP_DIR"
print_status "├── Git branch: $BRANCH"
print_status "├── PHP version: $PHP_VERSION"
print_status "├── Node.js version: $NODE_VERSION"
print_status "├── Environment: Production"
print_status "└── Status: Ready"
echo ""

# Show next steps
print_warning "🔧 Next Steps:"
print_warning "1. Configure your web server to point to: $APP_DIR/public"
print_warning "2. Ensure the web server user can read/write to:"
print_warning "   - $APP_DIR/storage/"
print_warning "   - $APP_DIR/bootstrap/cache/"
print_warning "3. Set up SSL certificate for HTTPS"
print_warning "4. Configure any required services (Redis, queues, etc.)"

# Show useful commands
echo ""
print_status "📚 Useful Commands:"
echo "  Start development server: cd $APP_DIR && php artisan serve"
echo "  Run migrations:          cd $APP_DIR && php artisan migrate"
echo "  Clear cache:             cd $APP_DIR && php artisan cache:clear"
echo "  View logs:               cd $APP_DIR && tail -f storage/logs/laravel.log"

echo ""
print_success "✨ Your Wall Planner application is ready!"

# Offer to start development server
read -p "Would you like to start the development server for testing? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_status "Starting development server on http://localhost:8000..."
    print_status "Press Ctrl+C to stop the server"
    php artisan serve
fi