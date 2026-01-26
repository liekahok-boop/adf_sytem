@echo off
REM Investor & Project Module Setup Script for Windows
REM This script will install the database schema

echo.
echo ==============================================
echo Investor & Project Management System Setup
echo ==============================================
echo.

REM Check if MySQL is running
echo Checking MySQL service...
sc query MySQL80 >nul 2>&1
if %errorlevel% neq 0 (
    echo MySQL service not found. Please start XAMPP MySQL first.
    echo.
    pause
    exit /b 1
)

echo ✓ MySQL is running

REM Navigate to database directory
cd /d "%~dp0"
echo Current directory: %cd%

REM Run migration
echo.
echo Running database migration...
echo.

mysql -u root -p narayana_hotel < database\migration-investor-project.sql

if %errorlevel% equ 0 (
    echo.
    echo ✓ Database migration completed successfully!
    echo.
    echo Next steps:
    echo 1. Open browser: http://localhost:8080/adf_system/modules/investor/index.php
    echo 2. Create a new investor
    echo 3. Add capital transaction (USD to IDR conversion)
    echo 4. Create a project
    echo 5. Add project expenses to test auto-deduction
    echo.
) else (
    echo.
    echo ✗ Database migration failed!
    echo Please check your MySQL connection and try again.
    echo.
)

pause
