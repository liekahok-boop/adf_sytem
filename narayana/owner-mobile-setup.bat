@echo off
title Buka Owner Dashboard dari HP
color 0A

echo ========================================
echo    AKSES OWNER DASHBOARD DARI HP
echo ========================================
echo.

REM Get IP Address
for /f "tokens=14" %%a in ('ipconfig ^| findstr /i "IPv4 Address"') do set IP=%%a

echo [1] IP Komputer Anda: %IP%
echo.
echo [2] Firewall Status:
netsh advfirewall firewall show rule name="Apache Port 8080" >nul 2>&1
if %errorlevel% equ 0 (
    echo     ✅ Firewall sudah terbuka
) else (
    echo     ❌ Firewall BELUM terbuka!
    echo     Jalankan: open-firewall.bat sebagai admin
)

echo.
echo [3] Apache Status:
tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL | find /I /N "httpd.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo     ✅ Apache sedang running
) else (
    echo     ❌ Apache TIDAK running!
    echo     Buka XAMPP Control Panel dan start Apache
)

echo.
echo ========================================
echo    URL UNTUK HP/TABLET
echo ========================================
echo.
echo Owner Login:
echo   http://%IP%:8080/narayana/owner-login.php
echo.
echo Owner Dashboard:
echo   http://%IP%:8080/narayana/modules/owner/dashboard.php
echo.
echo ========================================
echo    CARA AKSES DARI HP
echo ========================================
echo.
echo 1. Pastikan HP terhubung WiFi yang SAMA
echo 2. Buka browser di HP (Chrome/Safari)
echo 3. Ketik URL di atas
echo 4. Login dengan akun owner
echo.
echo Tips: Bookmark URL atau Add to Home Screen
echo       untuk akses lebih cepat!
echo.

REM Open browser to test
echo ========================================
echo    TEST KONEKSI
echo ========================================
echo.
set /p test="Test buka di browser komputer? (Y/N): "
if /i "%test%"=="Y" (
    echo.
    echo Membuka browser...
    start http://%IP%:8080/narayana/owner-login.php
    echo.
    echo ✅ Jika berhasil di komputer,
    echo    coba akses dari HP dengan URL yang sama
)

echo.
pause
