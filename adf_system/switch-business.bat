@echo off
REM Business Switcher Batch File for Windows
REM Usage: switch-business.bat <business-id>

cd /d "%~dp0.."

if "%1"=="" (
    echo.
    echo Usage: switch-business.bat ^<business-id^>
    echo.
    echo Examples:
    echo   switch-business.bat narayana-hotel
    echo   switch-business.bat warung-pakbudi
    echo   switch-business.bat fitness-zone
    echo.
    php tools/switch-business.php
    pause
    exit /b 1
)

php tools/switch-business.php %1
pause
