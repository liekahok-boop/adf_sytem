@echo off
title Start Apache & Buka Owner Dashboard
color 0A

echo ========================================
echo   START APACHE DAN OWNER DASHBOARD
echo ========================================
echo.

REM Check if Apache is running
tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL | find /I /N "httpd.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo [✓] Apache sudah running
) else (
    echo [!] Apache belum running
    echo [!] Starting Apache...
    
    REM Try to start Apache via XAMPP
    if exist "C:\xampp\apache_start.bat" (
        call "C:\xampp\apache_start.bat"
    ) else if exist "C:\xampp\apache\bin\httpd.exe" (
        start "" "C:\xampp\apache\bin\httpd.exe"
    ) else (
        echo [X] XAMPP tidak ditemukan!
        echo [!] Buka XAMPP Control Panel secara manual
        pause
        exit
    )
    
    timeout /t 3 >nul
)

echo.
echo ========================================
echo   MENDAPATKAN IP ADDRESS
echo ========================================
echo.

REM Get IP Address
for /f "tokens=14" %%a in ('ipconfig ^| findstr /i "IPv4 Address"') do set IP=%%a

if "%IP%"=="" (
    set IP=192.168.1.2
    echo [!] Menggunakan IP default: %IP%
) else (
    echo [✓] IP Address: %IP%
)

echo.
echo ========================================
echo   URL OWNER DASHBOARD
echo ========================================
echo.
echo AKSES DARI KOMPUTER:
echo   http://localhost:8080/narayana/modules/owner/dashboard.php
echo.
echo AKSES DARI HP/TABLET:
echo   http://%IP%:8080/narayana/modules/owner/dashboard.php
echo.
echo ATAU untuk login dulu:
echo   http://%IP%:8080/narayana/owner-login.php
echo.

REM Check Firewall
echo ========================================
echo   CEK FIREWALL
echo ========================================
echo.
netsh advfirewall firewall show rule name="Apache Port 8080" >nul 2>&1
if %errorlevel% equ 0 (
    echo [✓] Firewall port 8080 sudah terbuka
) else (
    echo [X] Firewall port 8080 BELUM terbuka!
    echo [!] Jalankan: open-firewall.bat sebagai Administrator
    echo.
)

echo.
echo ========================================
echo   BUKA DASHBOARD
echo ========================================
echo.
set /p open="Buka dashboard di browser? (Y/N): "
if /i "%open%"=="Y" (
    echo.
    echo [✓] Membuka browser...
    start http://localhost:8080/narayana/modules/owner/dashboard.php
    echo.
    echo [i] Jika berhasil di komputer,
    echo     copy URL HP di atas untuk akses dari HP
)

echo.
echo ========================================
echo   CARA AKSES DARI HP
echo ========================================
echo.
echo 1. Pastikan HP terhubung WiFi yang SAMA
echo 2. Buka browser di HP (Chrome/Safari)
echo 3. Ketik URL HP di atas
echo 4. Login dengan username owner
echo.
echo Tips: Bookmark atau Add to Home Screen
echo.
pause
