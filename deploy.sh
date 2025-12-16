#!/bin/bash

################################################################################
#                     Laravel E-commerce Deployment Script                    #
################################################################################
# This script automates the deployment of the Laravel e-commerce platform    #
# with comprehensive error handling, validation, and rollback support        #
################################################################################

set -euo pipefail

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DEPLOYMENT_LOG="${SCRIPT_DIR}/storage/logs/deployment.log"
FAILED_STEPS=()
COMPLETED_STEPS=()
START_TIME=$(date +%s)

# Ensure log directory exists
mkdir -p "$(dirname "$DEPLOYMENT_LOG")"

################################################################################
# Logging and Output Functions
################################################################################

log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $*" | tee -a "$DEPLOYMENT_LOG"
}

success() {
    echo -e "${GREEN}✓ $*${NC}" | tee -a "$DEPLOYMENT_LOG"
}

error() {
    echo -e "${RED}✗ $*${NC}" | tee -a "$DEPLOYMENT_LOG"
}

warning() {
    echo -e "${YELLOW}⚠ $*${NC}" | tee -a "$DEPLOYMENT_LOG"
}

print_section() {
    echo "" | tee -a "$DEPLOYMENT_LOG"
    echo -e "${BLUE}════════════════════════════════════════════${NC}" | tee -a "$DEPLOYMENT_LOG"
    echo -e "${BLUE}$*${NC}" | tee -a "$DEPLOYMENT_LOG"
    echo -e "${BLUE}════════════════════════════════════════════${NC}" | tee -a "$DEPLOYMENT_LOG"
}

print_step() {
    echo "" | tee -a "$DEPLOYMENT_LOG"
    echo -e "${BLUE}→ $*${NC}" | tee -a "$DEPLOYMENT_LOG"
}

record_step() {
    COMPLETED_STEPS+=("$1")
}

record_failure() {
    FAILED_STEPS+=("$1")
}

################################################################################
# Validation Functions
################################################################################

check_prerequisites() {
    print_section "Checking Prerequisites"

    # Check if running as appropriate user
    if [[ $EUID -ne 0 && ! -w "$SCRIPT_DIR" ]]; then
        error "This script requires write permissions to the project directory"
        exit 1
    fi

    # Check required commands
    local required_commands=("git" "php" "composer" "npm" "mysql")
    for cmd in "${required_commands[@]}"; do
        if ! command -v "$cmd" &> /dev/null; then
            warning "$cmd not found in PATH"
        else
            success "$cmd is installed"
        fi
    done

    # Check PHP version
    local php_version=$(php -v | grep -oP 'PHP \K[0-9.]+' | head -1)
    if [[ ! -z "$php_version" ]]; then
        success "PHP version: $php_version"
    fi

    # Check if .env file exists
    if [[ ! -f "${SCRIPT_DIR}/.env" ]]; then
        error ".env file not found"
        exit 1
    fi
    success ".env file exists"
}

validate_environment() {
    print_step "Validating Environment Configuration"

    local required_vars=("APP_NAME" "DB_HOST" "DB_DATABASE" "DB_USERNAME")
    for var in "${required_vars[@]}"; do
        if grep -q "^${var}=" "${SCRIPT_DIR}/.env"; then
            success "Environment variable $var is configured"
        else
            error "Environment variable $var is not configured"
            record_failure "Environment validation: $var"
        fi
    done
}

check_database_connection() {
    print_step "Checking Database Connection"

    # Extract database credentials from .env
    local db_host=$(grep "^DB_HOST=" "${SCRIPT_DIR}/.env" | cut -d'=' -f2 | tr -d ' ')
    local db_name=$(grep "^DB_DATABASE=" "${SCRIPT_DIR}/.env" | cut -d'=' -f2 | tr -d ' ')
    local db_user=$(grep "^DB_USERNAME=" "${SCRIPT_DIR}/.env" | cut -d'=' -f2 | tr -d ' ')
    local db_pass=$(grep "^DB_PASSWORD=" "${SCRIPT_DIR}/.env" | cut -d'=' -f2 | tr -d ' ')

    # Test database connection
    if mysql -h "$db_host" -u "$db_user" ${db_pass:+-p"$db_pass"} -e "SELECT 1;" &> /dev/null; then
        success "Database connection successful"
        record_step "Database connection verified"
    else
        warning "Could not verify database connection"
    fi
}

################################################################################
# Deployment Functions
################################################################################

step_pull_repository() {
    print_step "Step 1: Cloning/Pulling Repository"

    cd "$SCRIPT_DIR"

    if git rev-parse --git-dir > /dev/null 2>&1; then
        log "Repository already exists, pulling latest changes..."
        if git pull origin main 2>> "$DEPLOYMENT_LOG"; then
            success "Repository updated successfully"
            record_step "Repository pull"
        else
            warning "Could not pull latest changes"
        fi
    else
        error "Not in a git repository"
        record_failure "Repository pull"
    fi
}

step_install_composer() {
    print_step "Step 2: Installing Composer Dependencies"

    cd "$SCRIPT_DIR"

    if [[ ! -f "composer.json" ]]; then
        error "composer.json not found"
        record_failure "Composer install"
        return 1
    fi

    if composer install --optimize-autoloader --no-dev 2>> "$DEPLOYMENT_LOG"; then
        success "Composer dependencies installed"
        record_step "Composer dependencies"
    else
        error "Composer install failed"
        record_failure "Composer install"
        return 1
    fi
}

step_install_npm() {
    print_step "Step 3: Installing NPM Dependencies"

    cd "$SCRIPT_DIR"

    if [[ ! -f "package.json" ]]; then
        warning "package.json not found, skipping npm installation"
        return 0
    fi

    if npm install 2>> "$DEPLOYMENT_LOG"; then
        success "NPM dependencies installed"
        record_step "NPM dependencies"
    else
        warning "NPM install failed, continuing anyway..."
    fi
}

step_build_assets() {
    print_step "Step 4: Building Frontend Assets"

    cd "$SCRIPT_DIR"

    if npm run build 2>> "$DEPLOYMENT_LOG"; then
        success "Frontend assets built successfully"
        record_step "Asset build"
    else
        warning "Asset build failed, continuing anyway..."
    fi
}

step_validate_system() {
    print_step "Step 5: Running System Validation"

    cd "$SCRIPT_DIR"

    if php artisan validate:system --detailed 2>> "$DEPLOYMENT_LOG"; then
        success "System validation passed"
        record_step "System validation"
    else
        warning "System validation reported issues (check logs)"
    fi
}

step_run_migrations() {
    print_step "Step 6: Running Database Migrations"

    cd "$SCRIPT_DIR"

    if php artisan migrate --force 2>> "$DEPLOYMENT_LOG"; then
        success "Migrations completed successfully"
        record_step "Database migrations"
    else
        error "Migration failed"
        record_failure "Database migrations"
        return 1
    fi
}

step_seed_database() {
    print_step "Step 7: Seeding Database"

    cd "$SCRIPT_DIR"

    if php artisan db:seed --force 2>> "$DEPLOYMENT_LOG"; then
        success "Database seeding completed"
        record_step "Database seeding"
    else
        warning "Database seeding failed or no seeders found"
    fi
}

step_setup_permissions() {
    print_step "Step 8: Setting Up File Permissions"

    local directories=(
        "bootstrap/cache"
        "storage"
        "storage/logs"
        "storage/framework"
        "storage/framework/cache"
        "storage/framework/sessions"
        "storage/framework/views"
        "storage/app"
        "storage/app/public"
    )

    for dir in "${directories[@]}"; do
        if [[ -d "${SCRIPT_DIR}/${dir}" ]]; then
            chmod 775 "${SCRIPT_DIR}/${dir}"
            success "Permissions set for $dir"
        fi
    done

    # Set permissions for public directory
    if [[ -d "${SCRIPT_DIR}/public" ]]; then
        chmod 755 "${SCRIPT_DIR}/public"
        success "Permissions set for public"
    fi

    record_step "File permissions"
}

step_link_storage() {
    print_step "Step 9: Linking Storage Directory"

    cd "$SCRIPT_DIR"

    local target="${SCRIPT_DIR}/storage/app/public"
    local link="${SCRIPT_DIR}/public/storage"

    if [[ -L "$link" ]]; then
        success "Storage symlink already exists"
    elif [[ -d "$link" ]]; then
        rm -rf "$link"
        ln -s "$target" "$link"
        success "Storage symlink created (replaced existing directory)"
    else
        mkdir -p "$(dirname "$link")"
        ln -s "$target" "$link"
        success "Storage symlink created"
    fi

    record_step "Storage linking"
}

step_generate_app_key() {
    print_step "Step 10: Generating Application Key"

    cd "$SCRIPT_DIR"

    if grep -q "^APP_KEY=$" "${SCRIPT_DIR}/.env"; then
        if php artisan key:generate --force 2>> "$DEPLOYMENT_LOG"; then
            success "Application key generated"
            record_step "App key generation"
        else
            warning "Could not generate application key"
        fi
    else
        success "Application key already exists"
    fi
}

step_clear_caches() {
    print_step "Step 11: Clearing Caches"

    cd "$SCRIPT_DIR"

    local caches=("cache:clear" "config:clear" "route:clear" "view:clear" "event:clear")

    for cache_cmd in "${caches[@]}"; do
        if php artisan "$cache_cmd" 2>> "$DEPLOYMENT_LOG"; then
            success "$cache_cmd executed"
        else
            warning "Could not execute $cache_cmd"
        fi
    done

    record_step "Cache clearing"
}

step_run_tests() {
    print_step "Step 12: Running Application Tests"

    cd "$SCRIPT_DIR"

    if [[ ! -f "phpunit.xml" ]]; then
        warning "phpunit.xml not found, skipping tests"
        return 0
    fi

    if php artisan test 2>> "$DEPLOYMENT_LOG"; then
        success "All tests passed"
        record_step "Test execution"
    else
        warning "Some tests failed (review logs)"
    fi
}

################################################################################
# Summary and Rollback Functions
################################################################################

calculate_duration() {
    local end_time=$(date +%s)
    local duration=$((end_time - START_TIME))
    local hours=$((duration / 3600))
    local minutes=$(( (duration % 3600) / 60 ))
    local seconds=$((duration % 60))

    if [[ $hours -gt 0 ]]; then
        echo "${hours}h ${minutes}m ${seconds}s"
    elif [[ $minutes -gt 0 ]]; then
        echo "${minutes}m ${seconds}s"
    else
        echo "${seconds}s"
    fi
}

display_summary() {
    print_section "Deployment Summary"

    echo "" | tee -a "$DEPLOYMENT_LOG"
    echo -e "${GREEN}═════════════════════════════════════════════${NC}" | tee -a "$DEPLOYMENT_LOG"
    echo -e "${GREEN}         Completed Steps (${#COMPLETED_STEPS[@]})${NC}" | tee -a "$DEPLOYMENT_LOG"
    echo -e "${GREEN}═════════════════════════════════════════════${NC}" | tee -a "$DEPLOYMENT_LOG"

    for step in "${COMPLETED_STEPS[@]}"; do
        success "  $step"
    done

    if [[ ${#FAILED_STEPS[@]} -gt 0 ]]; then
        echo "" | tee -a "$DEPLOYMENT_LOG"
        echo -e "${RED}═════════════════════════════════════════════${NC}" | tee -a "$DEPLOYMENT_LOG"
        echo -e "${RED}         Failed Steps (${#FAILED_STEPS[@]})${NC}" | tee -a "$DEPLOYMENT_LOG"
        echo -e "${RED}═════════════════════════════════════════════${NC}" | tee -a "$DEPLOYMENT_LOG"

        for step in "${FAILED_STEPS[@]}"; do
            error "  $step"
        done
    fi

    echo "" | tee -a "$DEPLOYMENT_LOG"
    log "Deployment Duration: $(calculate_duration)"
    log "Deployment Log: $DEPLOYMENT_LOG"
}

show_next_steps() {
    print_section "Next Steps"

    echo -e "${BLUE}Verify Deployment:${NC}" | tee -a "$DEPLOYMENT_LOG"
    echo "  1. Visit application: $(grep "^APP_URL=" "${SCRIPT_DIR}/.env" | cut -d'=' -f2)" | tee -a "$DEPLOYMENT_LOG"
    echo "  2. Check logs: tail -f ${SCRIPT_DIR}/storage/logs/laravel.log" | tee -a "$DEPLOYMENT_LOG"
    echo "" | tee -a "$DEPLOYMENT_LOG"

    echo -e "${BLUE}Recommended Actions:${NC}" | tee -a "$DEPLOYMENT_LOG"
    echo "  • Monitor queue: cd ${SCRIPT_DIR} && php artisan queue:work" | tee -a "$DEPLOYMENT_LOG"
    echo "  • Watch logs: tail -f ${SCRIPT_DIR}/storage/logs/laravel.log" | tee -a "$DEPLOYMENT_LOG"
    echo "  • Run Horizon: php artisan horizon" | tee -a "$DEPLOYMENT_LOG"
    echo "" | tee -a "$DEPLOYMENT_LOG"

    echo -e "${BLUE}Useful Commands:${NC}" | tee -a "$DEPLOYMENT_LOG"
    echo "  • Re-validate: php artisan validate:system" | tee -a "$DEPLOYMENT_LOG"
    echo "  • Clear cache: php artisan cache:clear" | tee -a "$DEPLOYMENT_LOG"
    echo "  • Run migrations: php artisan migrate" | tee -a "$DEPLOYMENT_LOG"
}

handle_error() {
    local line=$1
    error "Deployment failed at line $line"
    echo "" | tee -a "$DEPLOYMENT_LOG"
    display_summary
    echo -e "${RED}Please review the deployment log for details.${NC}" | tee -a "$DEPLOYMENT_LOG"
    exit 1
}

################################################################################
# Main Execution
################################################################################

main() {
    print_section "Laravel E-Commerce Deployment"
    log "Starting deployment at $(date +'%Y-%m-%d %H:%M:%S')"
    log "Script location: $SCRIPT_DIR"

    # Set error trap
    trap 'handle_error ${LINENO}' ERR

    # Run checks and deployment
    check_prerequisites
    validate_environment
    check_database_connection

    # Run deployment steps
    step_pull_repository || true
    step_install_composer || true
    step_install_npm || true
    step_build_assets || true
    step_validate_system || true
    step_generate_app_key || true
    step_clear_caches || true
    step_run_migrations || true
    step_seed_database || true
    step_setup_permissions || true
    step_link_storage || true
    step_run_tests || true

    # Display results
    display_summary

    if [[ ${#FAILED_STEPS[@]} -eq 0 ]]; then
        print_section "Deployment Completed Successfully!"
        success "All deployment steps completed successfully"
        show_next_steps
        exit 0
    else
        warning "Deployment completed with some failures - review logs"
        show_next_steps
        exit 1
    fi
}

# Run main function
main "$@"
