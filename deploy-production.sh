#!/bin/bash

# Production Deployment Script
# This script automates the deployment process to production

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Production Deployment Script${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Function to print colored messages
print_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if .env exists
if [ ! -f ".env" ]; then
    print_error ".env file not found!"
    print_info "Creating from .env.example..."
    cp .env.example .env
    print_warn "Please configure .env file before continuing"
    exit 1
fi

# Check environment
APP_ENV=$(grep APP_ENV .env | cut -d '=' -f2)
if [ "$APP_ENV" != "production" ]; then
    print_warn "APP_ENV is not set to 'production'"
    read -p "Continue anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Pre-deployment checks
print_info "Running pre-deployment checks..."

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
print_info "PHP Version: $PHP_VERSION"

# Check required PHP extensions
REQUIRED_EXTENSIONS=("mbstring" "xml" "pdo_mysql" "redis" "gd" "zip")
for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -q "$ext"; then
        print_info "✓ PHP extension '$ext' is installed"
    else
        print_error "✗ PHP extension '$ext' is NOT installed"
        exit 1
    fi
done

# Check database connection
print_info "Testing database connection..."
if php artisan db:show > /dev/null 2>&1; then
    print_info "✓ Database connection successful"
else
    print_error "✗ Database connection failed"
    exit 1
fi

# Check Redis connection
print_info "Testing Redis connection..."
if php artisan tinker --execute="Redis::ping();" | grep -q "PONG"; then
    print_info "✓ Redis connection successful"
else
    print_warn "✗ Redis connection failed (non-critical)"
fi

# Backup current version
print_info "Creating backup..."
BACKUP_DIR="backups/$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

# Backup database
print_info "Backing up database..."
php artisan backup:run --only-db > /dev/null 2>&1 || print_warn "Database backup failed"

# Install dependencies
print_info "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

print_info "Installing NPM dependencies..."
npm ci --production

# Build assets
print_info "Building frontend assets..."
npm run build

# Clear caches
print_info "Clearing application caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Run migrations
print_info "Running database migrations..."
read -p "Run migrations? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate --force
    print_info "✓ Migrations completed"
else
    print_warn "Skipping migrations"
fi

# Optimize application
print_info "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Storage link
print_info "Creating storage link..."
php artisan storage:link

# Set permissions
print_info "Setting file permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || print_warn "Could not change ownership (run with sudo if needed)"

# Restart services
print_info "Restarting services..."
if command -v supervisorctl &> /dev/null; then
    supervisorctl restart all || print_warn "Could not restart supervisor services"
fi

if command -v systemctl &> /dev/null; then
    systemctl restart php8.2-fpm || print_warn "Could not restart PHP-FPM"
    systemctl reload nginx || print_warn "Could not reload Nginx"
fi

# Restart queue workers
print_info "Restarting queue workers..."
php artisan queue:restart

# Health check
print_info "Running health check..."
sleep 2
if curl -f http://localhost/api/health > /dev/null 2>&1; then
    print_info "✓ Health check passed"
else
    print_error "✗ Health check failed"
    print_warn "Application may not be responding correctly"
fi

# Final summary
echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Deployment completed successfully!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
print_info "Next steps:"
echo "  1. Test the application thoroughly"
echo "  2. Monitor logs: tail -f storage/logs/laravel.log"
echo "  3. Check queue workers: php artisan queue:work"
echo "  4. Monitor system resources"
echo ""
print_info "Rollback command (if needed):"
echo "  git reset --hard HEAD~1"
echo "  ./deploy-production.sh"
echo ""
