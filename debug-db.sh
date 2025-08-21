#!/bin/bash

# Database Debug Script for Wall Planner
# Usage: ./debug-db.sh

set -e

echo "🔍 Wall Planner Database Troubleshooting..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
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

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    APP_DIR="$HOME/wall-planner"
    if [ -d "$APP_DIR" ]; then
        cd $APP_DIR
        print_status "Changed to application directory: $APP_DIR"
    else
        print_error "Laravel application not found. Please run from app directory or ensure deployment completed."
        exit 1
    fi
fi

echo ""
print_status "📋 System Information:"
echo "  Working directory: $(pwd)"
echo "  PHP version: $(php -v | head -n1)"
echo ""

# Check if .env file exists
if [ ! -f ".env" ]; then
    print_error ".env file not found!"
    exit 1
fi

print_status "📊 Current Database Configuration:"
DB_CONNECTION=$(grep "DB_CONNECTION=" .env | cut -d'=' -f2)
DB_HOST=$(grep "DB_HOST=" .env | cut -d'=' -f2)
DB_PORT=$(grep "DB_PORT=" .env | cut -d'=' -f2)
DB_DATABASE=$(grep "DB_DATABASE=" .env | cut -d'=' -f2)
DB_USERNAME=$(grep "DB_USERNAME=" .env | cut -d'=' -f2)

echo "  Connection: $DB_CONNECTION"
echo "  Host: $DB_HOST"
echo "  Port: $DB_PORT"
echo "  Database: $DB_DATABASE"
echo "  Username: $DB_USERNAME"
echo "  Password: [hidden]"
echo ""

# Test 1: Check if MySQL service is running (if applicable)
print_status "🔍 Test 1: Checking MySQL service status..."
if command -v systemctl &> /dev/null; then
    if systemctl is-active --quiet mysql || systemctl is-active --quiet mysqld || systemctl is-active --quiet mariadb; then
        print_success "MySQL service is running ✓"
    else
        print_warning "MySQL service may not be running"
        print_status "Try: sudo systemctl start mysql"
    fi
else
    print_warning "Cannot check service status (systemctl not available)"
fi

# Test 2: Check if we can reach the database host/port
print_status "🔍 Test 2: Testing network connectivity to database..."
if command -v nc &> /dev/null; then
    if nc -z $DB_HOST $DB_PORT 2>/dev/null; then
        print_success "Can connect to $DB_HOST:$DB_PORT ✓"
    else
        print_error "Cannot connect to $DB_HOST:$DB_PORT"
        print_warning "Check if database server is running and port is correct"
    fi
elif command -v telnet &> /dev/null; then
    if timeout 5 bash -c "echo >/dev/tcp/$DB_HOST/$DB_PORT" 2>/dev/null; then
        print_success "Can connect to $DB_HOST:$DB_PORT ✓"
    else
        print_error "Cannot connect to $DB_HOST:$DB_PORT"
        print_warning "Check if database server is running and port is correct"
    fi
else
    print_warning "Cannot test network connectivity (nc/telnet not available)"
fi

# Test 3: Test PHP MySQL extension
print_status "🔍 Test 3: Checking PHP MySQL extensions..."
if php -m | grep -i mysqli > /dev/null; then
    print_success "PHP MySQLi extension loaded ✓"
else
    print_error "PHP MySQLi extension not loaded"
    print_warning "Install with: sudo apt-get install php-mysql"
fi

if php -m | grep -i pdo_mysql > /dev/null; then
    print_success "PHP PDO MySQL extension loaded ✓"
else
    print_error "PHP PDO MySQL extension not loaded"
    print_warning "Install with: sudo apt-get install php-mysql"
fi

# Test 4: Test Laravel database configuration
print_status "🔍 Test 4: Testing Laravel database connection..."
DB_TEST_OUTPUT=$(php artisan migrate:status 2>&1)
DB_TEST_STATUS=$?

if [ $DB_TEST_STATUS -eq 0 ]; then
    print_success "Laravel database connection successful ✓"
    echo "Migration status:"
    echo "$DB_TEST_OUTPUT"
else
    print_error "Laravel database connection failed!"
    echo ""
    print_error "Full error output:"
    echo "$DB_TEST_OUTPUT"
    echo ""
fi

# Test 5: Try manual MySQL connection (if mysql client available)
if command -v mysql &> /dev/null; then
    print_status "🔍 Test 5: Testing direct MySQL connection..."
    DB_PASSWORD=$(grep "DB_PASSWORD=" .env | cut -d'=' -f2)
    
    if [ ! -z "$DB_PASSWORD" ]; then
        MYSQL_TEST=$(mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "SELECT 1;" 2>&1)
        MYSQL_STATUS=$?
        
        if [ $MYSQL_STATUS -eq 0 ]; then
            print_success "Direct MySQL connection successful ✓"
        else
            print_error "Direct MySQL connection failed!"
            echo "$MYSQL_TEST"
        fi
    else
        print_warning "No password set, skipping direct MySQL test"
    fi
else
    print_warning "MySQL client not available for direct testing"
fi

# Test 6: Check if database exists
print_status "🔍 Test 6: Checking if database exists..."
if command -v mysql &> /dev/null && [ ! -z "$DB_PASSWORD" ]; then
    DB_EXISTS=$(mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "SHOW DATABASES LIKE '$DB_DATABASE';" 2>/dev/null | grep -c "$DB_DATABASE")
    
    if [ "$DB_EXISTS" -eq 1 ]; then
        print_success "Database '$DB_DATABASE' exists ✓"
    else
        print_error "Database '$DB_DATABASE' does not exist!"
        print_warning "Create it with: CREATE DATABASE $DB_DATABASE;"
    fi
else
    print_warning "Cannot check if database exists (mysql client not available or no password)"
fi

echo ""
print_status "🛠️  Common Solutions:"
echo "1. Create the database: CREATE DATABASE $DB_DATABASE;"
echo "2. Grant permissions: GRANT ALL ON $DB_DATABASE.* TO '$DB_USERNAME'@'%';"
echo "3. Start MySQL service: sudo systemctl start mysql"
echo "4. Install PHP MySQL: sudo apt-get install php-mysql"
echo "5. Check firewall/port access to $DB_HOST:$DB_PORT"

echo ""
print_status "📚 Manual Commands to Try:"
echo "  Test Laravel connection: php artisan migrate:status"
echo "  Test direct MySQL: mysql -h$DB_HOST -P$DB_PORT -u$DB_USERNAME -p"
echo "  Create database: mysql -u root -p -e 'CREATE DATABASE $DB_DATABASE;'"
echo "  Check services: sudo systemctl status mysql"