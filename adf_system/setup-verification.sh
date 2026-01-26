#!/bin/bash
# COMPLETE SETUP CHECKLIST FOR INVESTOR & PROJECT MODULES
# Run this after deployment to verify everything works

echo "=========================================="
echo "INVESTOR & PROJECT SYSTEM - SETUP CHECKLIST"
echo "=========================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Counter
TOTAL=0
PASSED=0
FAILED=0

# Test function
run_test() {
    TOTAL=$((TOTAL+1))
    if eval "$2"; then
        echo -e "${GREEN}✅ PASS${NC} - $1"
        PASSED=$((PASSED+1))
    else
        echo -e "${RED}❌ FAIL${NC} - $1"
        FAILED=$((FAILED+1))
    fi
}

echo "🔍 DATABASE CHECKS"
echo "=================="

# Check MySQL is running
run_test "MySQL Service Running" "nc -z localhost 3306 2>/dev/null"

# Check database exists
run_test "Database 'adf_narayana_hotel' exists" \
    "mysql -u root adf_narayana_hotel -e 'SELECT 1' 2>/dev/null | grep -q 1"

# Check tables exist
run_test "users table exists" \
    "mysql -u root adf_narayana_hotel -e 'SHOW TABLES LIKE \"users\"' 2>/dev/null | grep -q users"

run_test "user_permissions table exists" \
    "mysql -u root adf_narayana_hotel -e 'SHOW TABLES LIKE \"user_permissions\"' 2>/dev/null | grep -q user_permissions"

run_test "investors table exists" \
    "mysql -u root adf_narayana_hotel -e 'SHOW TABLES LIKE \"investors\"' 2>/dev/null | grep -q investors"

run_test "projects table exists" \
    "mysql -u root adf_narayana_hotel -e 'SHOW TABLES LIKE \"projects\"' 2>/dev/null | grep -q projects"

echo ""
echo "📁 FILE STRUCTURE CHECKS"
echo "========================"

# Check PHP files exist
run_test "config/config.php exists" \
    "[ -f c:/xampp/htdocs/adf_system/config/config.php ]"

run_test "includes/auth.php exists" \
    "[ -f c:/xampp/htdocs/adf_system/includes/auth.php ]"

run_test "modules/investor/index.php exists" \
    "[ -f c:/xampp/htdocs/adf_system/modules/investor/index.php ]"

run_test "modules/project/index.php exists" \
    "[ -f c:/xampp/htdocs/adf_system/modules/project/index.php ]"

echo ""
echo "🔐 PERMISSIONS CHECKS"
echo "====================="

# Check admin user has permissions
run_test "Admin user has permissions in database" \
    "mysql -u root adf_narayana_hotel -e 'SELECT COUNT(*) FROM user_permissions WHERE user_id=1' 2>/dev/null | grep -q '[1-9]'"

run_test "Investor permission assigned" \
    "mysql -u root adf_narayana_hotel -e 'SELECT 1 FROM user_permissions WHERE user_id=1 AND permission=\"investor\"' 2>/dev/null | grep -q 1"

run_test "Project permission assigned" \
    "mysql -u root adf_narayana_hotel -e 'SELECT 1 FROM user_permissions WHERE user_id=1 AND permission=\"project\"' 2>/dev/null | grep -q 1"

echo ""
echo "📊 SUMMARY"
echo "=========="
echo "Total Tests: $TOTAL"
echo -e "Passed: ${GREEN}$PASSED${NC}"
echo -e "Failed: ${RED}$FAILED${NC}"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}✅ ALL TESTS PASSED!${NC}"
    echo ""
    echo "Next steps:"
    echo "1. Open browser: http://localhost:8080/adf_system/"
    echo "2. Login with admin/password"
    echo "3. Check sidebar - Investor & Project menus should appear"
    echo "4. Click dropdown menus - they should expand/collapse"
    echo "5. Click submenu items - should navigate to modules"
else
    echo -e "${RED}❌ SOME TESTS FAILED${NC}"
    echo "Check the items marked FAIL above"
fi

echo ""
echo "=========================================="
