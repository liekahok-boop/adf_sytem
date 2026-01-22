@echo off
echo ================================================
echo NARAYANA HOTEL - XAMPP CHECKER
echo ================================================
echo.

REM Check if Apache is running
tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL | find /I /N "httpd.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo [OK] Apache is RUNNING
) else (
    echo [X] Apache is NOT RUNNING
    echo.
    echo SOLUSI: Buka XAMPP Control Panel dan klik START di Apache
    echo.
)

echo.

REM Check if MySQL is running
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo [OK] MySQL is RUNNING
) else (
    echo [X] MySQL is NOT RUNNING
    echo.
    echo SOLUSI: Buka XAMPP Control Panel dan klik START di MySQL
    echo.
)

echo.
echo ================================================
echo Tekan tombol apa saja untuk tutup...
pause >nul
