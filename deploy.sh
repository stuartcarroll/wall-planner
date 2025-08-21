#!/bin/bash

# Wall Planner Deployment Script
# Usage: ./deploy.sh

set -e

echo "🚀 Starting Wall Planner deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="/var/www/wall-planner"
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

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run as root (use sudo)"
    exit 1
fi

# Update system packages
print_status "Updating system packages..."
apt-get update -q

# Install required packages if not present
print_status "Checking required packages..."
REQUIRED_PACKAGES="git curl unzip nginx mysql-server redis-server supervisor"
for package in $REQUIRED_PACKAGES; do
    if ! dpkg -l | grep -q "^ii  $package "; then
        print_status "Installing $package..."
        apt-get install -y $package
    fi
done

# Install PHP 8.2 and extensions if not present
if ! command -v php8.2 &> /dev/null; then
    print_status "Installing PHP 8.2 and extensions..."
    add-apt-repository ppa:ondrej/php -y
    apt-get update
    apt-get install -y php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring \
                       php8.2-curl php8.2-zip php8.2-bcmath php8.2-gd php8.2-redis \
                       php8.2-intl php8.2-soap php8.2-sqlite3
fi

# Install Node.js 20 if not present
if ! command -v node &> /dev/null || [[ $(node -v) != v20* ]]; then
    print_status "Installing Node.js 20..."
    curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
    apt-get install -y nodejs
fi

# Install Composer if not present
if ! command -v composer &> /dev/null; then
    print_status "Installing Composer..."
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
fi

# Create application directory
if [ ! -d "$APP_DIR" ]; then
    print_status "Creating application directory..."
    mkdir -p $APP_DIR
    chown -R www-data:www-data $APP_DIR
fi

# Navigate to app directory
cd $APP_DIR

# Git operations
if [ -d ".git" ]; then
    print_status "Pulling latest changes from repository..."
    sudo -u www-data git fetch origin
    sudo -u www-data git reset --hard origin/$BRANCH
else
    print_status "Cloning repository..."
    sudo -u www-data git clone $REPO_URL .
    sudo -u www-data git checkout $BRANCH
fi

# Install Composer dependencies
print_status "Installing PHP dependencies..."
sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction

# Install Node.js dependencies
print_status "Installing Node.js dependencies..."
sudo -u www-data npm ci --only=production

# Build frontend assets
print_status "Building production assets..."
sudo -u www-data npm run build

# Set up environment file
if [ ! -f ".env" ]; then
    print_status "Creating environment file..."
    sudo -u www-data cp .env.example .env
    
    # Generate application key
    sudo -u www-data php artisan key:generate --no-interaction
    
    print_warning "Please configure your .env file with database and other settings"
    print_warning "Edit: $APP_DIR/.env"
    
    # Wait for user to configure .env
    read -p "Press Enter after configuring .env file..."
fi

# Set proper permissions
print_status "Setting file permissions..."
chown -R www-data:www-data $APP_DIR
chmod -R 755 $APP_DIR
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache

# Database operations
print_status "Running database migrations..."
sudo -u www-data php artisan migrate --force --no-interaction

# Clear and cache configuration
print_status "Optimizing application..."
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Create storage link
sudo -u www-data php artisan storage:link

# Configure Nginx
print_status "Configuring Nginx..."
cat > /etc/nginx/sites-available/wall-planner << 'EOL'
server {
    listen 80;
    listen [::]:80;
    server_name _;
    root /var/www/wall-planner/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOL

# Enable site
if [ ! -L "/etc/nginx/sites-enabled/wall-planner" ]; then
    ln -s /etc/nginx/sites-available/wall-planner /etc/nginx/sites-enabled/
fi

# Remove default site if it exists
if [ -L "/etc/nginx/sites-enabled/default" ]; then
    rm /etc/nginx/sites-enabled/default
fi

# Test and reload Nginx
print_status "Testing Nginx configuration..."
nginx -t

if [ $? -eq 0 ]; then
    systemctl reload nginx
    print_success "Nginx configured and reloaded"
else
    print_error "Nginx configuration test failed"
    exit 1
fi

# Configure PHP-FPM
print_status "Configuring PHP-FPM..."
systemctl enable php8.2-fpm
systemctl start php8.2-fpm

# Start and enable services
print_status "Starting services..."
systemctl enable nginx
systemctl start nginx
systemctl enable redis-server
systemctl start redis-server

# Set up queue worker (optional)
if command -v supervisorctl &> /dev/null; then
    print_status "Setting up queue worker..."
    cat > /etc/supervisor/conf.d/wall-planner-worker.conf << EOL
[program:wall-planner-worker]
process_name=%(program_name)s_%(process_num)02d
command=php $APP_DIR/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=$APP_DIR/storage/logs/worker.log
stopwaitsecs=3600
EOL

    supervisorctl reread
    supervisorctl update
    supervisorctl start wall-planner-worker:*
fi

# Final checks
print_status "Running final checks..."

# Check if services are running
services=("nginx" "php8.2-fpm" "mysql" "redis-server")
for service in "${services[@]}"; do
    if systemctl is-active --quiet $service; then
        print_success "$service is running"
    else
        print_warning "$service is not running"
    fi
done

# Check if application is accessible
if curl -s -o /dev/null -w "%{http_code}" http://localhost | grep -q "200\|302"; then
    print_success "Application is accessible"
else
    print_warning "Application may not be accessible - check configuration"
fi

print_success "🎉 Deployment completed!"
print_status "Application URL: http://$(curl -s ifconfig.me 2>/dev/null || echo 'your-server-ip')"
print_status "Application directory: $APP_DIR"
print_status "Nginx configuration: /etc/nginx/sites-available/wall-planner"
print_status "Logs location: $APP_DIR/storage/logs/"

print_warning "Don't forget to:"
print_warning "1. Configure your database in .env file"
print_warning "2. Set up SSL certificate (Let's Encrypt recommended)"
print_warning "3. Configure your domain name in Nginx"
print_warning "4. Set up regular backups"
print_warning "5. Configure firewall rules"

echo ""
print_success "Deployment script completed successfully!"