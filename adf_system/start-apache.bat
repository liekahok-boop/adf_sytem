@echo off
color 0A
echo ========================================
echo   NARAYANA - START XAMPP APACHE
echo ========================================
echo.
echo Memulai Apache dan MySQL...
echo.

REM Start Apache
echo [1/2] Starting Apache...
"C:\xampp\apache\bin\httpd.exe" -k start
timeout /t 2 /nobreak >nul

REM Start MySQL
echo [2/2] Starting MySQL...
"C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone --console
timeout /t 2 /nobreak >nul

echo.
echo ========================================
echo   STATUS CHECK
echo ========================================
echo.

REM Check Apache
tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL | find /I /N "httpd.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo [OK] Apache RUNNING
) else (
    echo [X] Apache GAGAL START
)

REM Check MySQL
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo [OK] MySQL RUNNING
) else (
    echo [X] MySQL GAGAL START
)

echo.
echo ========================================
echo Silakan coba buka browser:
echo http://localhost/narayana
echo ========================================
echo.
pause
