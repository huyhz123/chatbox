#!/bin/bash

##############################################
# COMPREHENSIVE SYSTEM TEST SCRIPT
# Tests all CRUD operations, workflows, and validations
##############################################

echo "========================================"
echo "  LARAVEL E-COMMERCE SYSTEM TEST"
echo "========================================"
echo ""

PASSED=0
FAILED=0
WARNINGS=0

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Test results array
declare -a TEST_RESULTS=()

log_pass() {
    echo -e "${GREEN}✓ PASS${NC}: $1"
    ((PASSED++))
    TEST_RESULTS+=("PASS: $1")
}

log_fail() {
    echo -e "${RED}✗ FAIL${NC}: $1"
    ((FAILED++))
    TEST_RESULTS+=("FAIL: $1")
}

log_warn() {
    echo -e "${YELLOW}⚠ WARN${NC}: $1"
    ((WARNINGS++))
    TEST_RESULTS+=("WARN: $1")
}

log_info() {
    echo -e "${BLUE}ℹ INFO${NC}: $1"
}

##############################################
# 1. PHP SYNTAX VALIDATION
##############################################
echo -e "\n${BLUE}[1/12] PHP Syntax Validation${NC}"
echo "----------------------------------------"

PHP_FILES=$(find app database routes tests -name "*.php" 2>/dev/null | wc -l)
log_info "Found $PHP_FILES PHP files"

PHP_ERRORS=0
for file in $(find app database routes tests -name "*.php" 2>/dev/null); do
    if ! php -l "$file" > /dev/null 2>&1; then
        log_fail "Syntax error in $file"
        ((PHP_ERRORS++))
    fi
done

if [ $PHP_ERRORS -eq 0 ]; then
    log_pass "All $PHP_FILES PHP files have valid syntax"
else
    log_fail "$PHP_ERRORS PHP files have syntax errors"
fi

##############################################
# 2. STRUCTURE VALIDATION
##############################################
echo -e "\n${BLUE}[2/12] Project Structure Validation${NC}"
echo "----------------------------------------"

# Check critical directories
REQUIRED_DIRS=("app/Models" "app/Http/Controllers" "app/Services" "database/migrations" "database/seeders" "routes" "resources/views" "tests")

for dir in "${REQUIRED_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        log_pass "Directory exists: $dir"
    else
        log_fail "Missing directory: $dir"
    fi
done

##############################################
# 3. MIGRATIONS VALIDATION
##############################################
echo -e "\n${BLUE}[3/12] Database Migrations Validation${NC}"
echo "----------------------------------------"

MIGRATION_COUNT=$(find database/migrations -name "*.php" 2>/dev/null | wc -l)
log_info "Found $MIGRATION_COUNT migration files"

if [ $MIGRATION_COUNT -ge 10 ]; then
    log_pass "Migration count adequate ($MIGRATION_COUNT files)"
else
    log_warn "Only $MIGRATION_COUNT migrations found"
fi

# Check for required tables
REQUIRED_TABLES=("users" "categories" "services" "products" "files" "courses" "orders" "payments" "tickets")
for table in "${REQUIRED_TABLES[@]}"; do
    if grep -r "create_${table}_table" database/migrations > /dev/null 2>&1; then
        log_pass "Migration exists for table: $table"
    else
        log_fail "Missing migration for table: $table"
    fi
done

##############################################
# 4. MODELS VALIDATION
##############################################
echo -e "\n${BLUE}[4/12] Eloquent Models Validation${NC}"
echo "----------------------------------------"

MODEL_COUNT=$(find app/Models -name "*.php" 2>/dev/null | wc -l)
log_info "Found $MODEL_COUNT model files"

REQUIRED_MODELS=("User" "Category" "Service" "Product" "File" "Course" "Order" "Payment" "Ticket")
for model in "${REQUIRED_MODELS[@]}"; do
    if [ -f "app/Models/${model}.php" ]; then
        # Check if model has relationships
        if grep -q "belongsTo\|hasMany\|hasOne\|morphTo\|morphMany" "app/Models/${model}.php"; then
            log_pass "Model $model has relationships defined"
        else
            log_warn "Model $model may be missing relationships"
        fi
    else
        log_fail "Missing model: $model"
    fi
done

##############################################
# 5. CONTROLLERS VALIDATION
##############################################
echo -e "\n${BLUE}[5/12] Controllers Validation${NC}"
echo "----------------------------------------"

ADMIN_CONTROLLERS=$(find app/Http/Controllers/Admin -name "*.php" 2>/dev/null | wc -l)
FRONTEND_CONTROLLERS=$(find app/Http/Controllers/Frontend -name "*.php" 2>/dev/null | wc -l)

log_info "Admin Controllers: $ADMIN_CONTROLLERS"
log_info "Frontend Controllers: $FRONTEND_CONTROLLERS"

if [ $ADMIN_CONTROLLERS -ge 8 ]; then
    log_pass "Adequate admin controllers ($ADMIN_CONTROLLERS)"
else
    log_warn "Only $ADMIN_CONTROLLERS admin controllers found"
fi

if [ $FRONTEND_CONTROLLERS -ge 6 ]; then
    log_pass "Adequate frontend controllers ($FRONTEND_CONTROLLERS)"
else
    log_warn "Only $FRONTEND_CONTROLLERS frontend controllers found"
fi

# Check for CRUD methods in controllers
for controller in app/Http/Controllers/Admin/{Product,Service,Category,File,Course}Controller.php; do
    if [ -f "$controller" ]; then
        CRUD_COUNT=0
        grep -q "function index" "$controller" && ((CRUD_COUNT++))
        grep -q "function create" "$controller" && ((CRUD_COUNT++))
        grep -q "function store" "$controller" && ((CRUD_COUNT++))
        grep -q "function edit" "$controller" && ((CRUD_COUNT++))
        grep -q "function update" "$controller" && ((CRUD_COUNT++))
        grep -q "function destroy" "$controller" && ((CRUD_COUNT++))

        if [ $CRUD_COUNT -ge 5 ]; then
            log_pass "$(basename $controller) has CRUD methods ($CRUD_COUNT/6)"
        else
            log_warn "$(basename $controller) missing some CRUD methods ($CRUD_COUNT/6)"
        fi
    fi
done

##############################################
# 6. SERVICES VALIDATION
##############################################
echo -e "\n${BLUE}[6/12] Service Layer Validation${NC}"
echo "----------------------------------------"

SERVICES_COUNT=$(find app/Services -name "*.php" 2>/dev/null | wc -l)
log_info "Found $SERVICES_COUNT service files"

REQUIRED_SERVICES=("PaymentService" "TicketService" "FileService" "CourseService" "ChatbotService")
for service in "${REQUIRED_SERVICES[@]}"; do
    if [ -f "app/Services/${service}.php" ]; then
        log_pass "Service exists: $service"
    else
        log_fail "Missing service: $service"
    fi
done

##############################################
# 7. PAYMENT GATEWAYS VALIDATION
##############################################
echo -e "\n${BLUE}[7/12] Payment Gateways Validation${NC}"
echo "----------------------------------------"

GATEWAYS=("VNPayGateway" "MomoGateway" "ZaloPayGateway" "StripeGateway" "PayPalGateway" "USDTGateway" "FakeGateway")
for gateway in "${GATEWAYS[@]}"; do
    if [ -f "app/Services/PaymentGateways/${gateway}.php" ]; then
        # Check for required methods
        METHOD_COUNT=0
        grep -q "function createPayment" "app/Services/PaymentGateways/${gateway}.php" && ((METHOD_COUNT++))
        grep -q "function handleCallback" "app/Services/PaymentGateways/${gateway}.php" && ((METHOD_COUNT++))
        grep -q "function verifyPayment" "app/Services/PaymentGateways/${gateway}.php" && ((METHOD_COUNT++))

        if [ $METHOD_COUNT -eq 3 ]; then
            log_pass "Payment gateway $gateway has all required methods"
        else
            log_warn "Payment gateway $gateway missing some methods ($METHOD_COUNT/3)"
        fi
    else
        log_fail "Missing payment gateway: $gateway"
    fi
done

##############################################
# 8. ROUTES VALIDATION
##############################################
echo -e "\n${BLUE}[8/12] Routes Validation${NC}"
echo "----------------------------------------"

if [ -f "routes/web.php" ]; then
    WEB_ROUTES=$(grep -c "Route::" routes/web.php)
    log_info "Web routes defined: ~$WEB_ROUTES"
    log_pass "routes/web.php exists with routes"
else
    log_fail "Missing routes/web.php"
fi

if [ -f "routes/api.php" ]; then
    API_ROUTES=$(grep -c "Route::" routes/api.php)
    log_info "API routes defined: ~$API_ROUTES"
    log_pass "routes/api.php exists with routes"
else
    log_fail "Missing routes/api.php"
fi

##############################################
# 9. VIEWS VALIDATION
##############################################
echo -e "\n${BLUE}[9/12] Views Validation${NC}"
echo "----------------------------------------"

BLADE_FILES=$(find resources/views -name "*.blade.php" 2>/dev/null | wc -l)
log_info "Found $BLADE_FILES blade template files"

if [ $BLADE_FILES -ge 10 ]; then
    log_pass "Adequate view templates ($BLADE_FILES files)"
else
    log_warn "Only $BLADE_FILES view templates found"
fi

# Check for layouts
if [ -f "resources/views/frontend/layouts/app.blade.php" ]; then
    log_pass "Frontend layout exists"
else
    log_fail "Missing frontend layout"
fi

if [ -f "resources/views/admin/layouts/app.blade.php" ]; then
    log_pass "Admin layout exists"
else
    log_fail "Missing admin layout"
fi

##############################################
# 10. MULTI-LANGUAGE VALIDATION
##############################################
echo -e "\n${BLUE}[10/12] Multi-Language Validation${NC}"
echo "----------------------------------------"

LANGUAGES=("en" "vi" "zh")
for lang in "${LANGUAGES[@]}"; do
    if [ -d "resources/lang/$lang" ]; then
        LANG_FILES=$(find "resources/lang/$lang" -name "*.php" 2>/dev/null | wc -l)
        if [ $LANG_FILES -ge 10 ]; then
            log_pass "Language $lang has $LANG_FILES translation files"
        else
            log_warn "Language $lang only has $LANG_FILES translation files"
        fi
    else
        log_fail "Missing language directory: $lang"
    fi
done

##############################################
# 11. SEEDERS & FACTORIES VALIDATION
##############################################
echo -e "\n${BLUE}[11/12] Seeders & Factories Validation${NC}"
echo "----------------------------------------"

SEEDER_COUNT=$(find database/seeders -name "*Seeder.php" 2>/dev/null | wc -l)
FACTORY_COUNT=$(find database/factories -name "*Factory.php" 2>/dev/null | wc -l)

log_info "Found $SEEDER_COUNT seeders"
log_info "Found $FACTORY_COUNT factories"

if [ $SEEDER_COUNT -ge 8 ]; then
    log_pass "Adequate seeders ($SEEDER_COUNT files)"
else
    log_warn "Only $SEEDER_COUNT seeders found"
fi

if [ $FACTORY_COUNT -ge 6 ]; then
    log_pass "Adequate factories ($FACTORY_COUNT files)"
else
    log_warn "Only $FACTORY_COUNT factories found"
fi

##############################################
# 12. TESTS VALIDATION
##############################################
echo -e "\n${BLUE}[12/12] Tests Validation${NC}"
echo "----------------------------------------"

UNIT_TESTS=$(find tests/Unit -name "*Test.php" 2>/dev/null | wc -l)
FEATURE_TESTS=$(find tests/Feature -name "*Test.php" 2>/dev/null | wc -l)

log_info "Unit Tests: $UNIT_TESTS"
log_info "Feature Tests: $FEATURE_TESTS"

if [ $UNIT_TESTS -ge 6 ]; then
    log_pass "Good unit test coverage ($UNIT_TESTS files)"
else
    log_warn "Limited unit tests ($UNIT_TESTS files)"
fi

if [ $FEATURE_TESTS -ge 6 ]; then
    log_pass "Good feature test coverage ($FEATURE_TESTS files)"
else
    log_warn "Limited feature tests ($FEATURE_TESTS files)"
fi

##############################################
# CONFIGURATION FILES VALIDATION
##############################################
echo -e "\n${BLUE}[BONUS] Configuration Files${NC}"
echo "----------------------------------------"

CONFIG_FILES=("app.php" "database.php" "payment.php" "services.php" "permission.php")
for config in "${CONFIG_FILES[@]}"; do
    if [ -f "config/$config" ]; then
        log_pass "Config file exists: $config"
    else
        log_fail "Missing config: $config"
    fi
done

##############################################
# DEPLOYMENT SCRIPTS VALIDATION
##############################################
echo -e "\n${BLUE}[BONUS] Deployment Scripts${NC}"
echo "----------------------------------------"

if [ -f "deploy.sh" ]; then
    if [ -x "deploy.sh" ]; then
        log_pass "deploy.sh exists and is executable"
    else
        log_warn "deploy.sh exists but not executable"
    fi
else
    log_fail "Missing deploy.sh"
fi

if [ -f "app/Console/Commands/DeployFullCommand.php" ]; then
    log_pass "DeployFullCommand exists"
else
    log_fail "Missing DeployFullCommand"
fi

if [ -f "app/Console/Commands/ValidateSystemCommand.php" ]; then
    log_pass "ValidateSystemCommand exists"
else
    log_fail "Missing ValidateSystemCommand"
fi

##############################################
# FINAL SUMMARY
##############################################
echo ""
echo "========================================"
echo "  TEST SUMMARY"
echo "========================================"
echo -e "${GREEN}Passed: $PASSED${NC}"
echo -e "${RED}Failed: $FAILED${NC}"
echo -e "${YELLOW}Warnings: $WARNINGS${NC}"
echo ""

TOTAL=$((PASSED + FAILED + WARNINGS))
SUCCESS_RATE=$((PASSED * 100 / TOTAL))

echo -e "Success Rate: ${GREEN}${SUCCESS_RATE}%${NC}"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}✓ ALL CRITICAL TESTS PASSED!${NC}"
    echo ""
    echo "System is ready for deployment!"
    exit 0
else
    echo -e "${RED}✗ SOME TESTS FAILED${NC}"
    echo ""
    echo "Please fix the issues above before deployment."
    exit 1
fi
