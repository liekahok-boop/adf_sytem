@echo off
echo ========================================
echo    CEK IP ADDRESS KOMPUTER
echo ========================================
echo.

echo IP Address Komputer Anda:
echo.
ipconfig | findstr /i "IPv4"

echo.
echo ========================================
echo    URL untuk Owner Dashboard
echo ========================================
echo.

for /f "tokens=14" %%a in ('ipconfig ^| findstr /i "IPv4"') do (
    echo Akses dari HP:
    echo   http://%%a:8080/narayana/owner-login.php
    echo.
    echo Akses Dashboard Langsung:
    echo   http://%%a:8080/narayana/modules/owner/dashboard.php
    echo.
)

echo ========================================
echo    CATATAN PENTING
echo ========================================
echo.
echo 1. HP dan komputer harus di WiFi yang SAMA
echo 2. Jalankan open-firewall.bat sebagai admin
echo 3. Pastikan Apache XAMPP running (hijau)
echo.

pause
