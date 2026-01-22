@echo off
color 0A
echo ========================================
echo   QUICK START - NARAYANA HOTEL
echo ========================================
echo.
echo Membuka aplikasi di browser...
echo.

REM Check if Apache running on port 80 or 8080
netstat -ano | findstr ":80 " | findstr "LISTENING" >nul
if "%ERRORLEVEL%"=="0" (
    echo Apache detected on port 80
    start http://localhost/narayana
    goto :end
)

netstat -ano | findstr ":8080 " | findstr "LISTENING" >nul
if "%ERRORLEVEL%"=="0" (
    echo Apache detected on port 8080
    start http://localhost:8080/narayana
    goto :end
)

echo.
echo ERROR: Apache tidak terdeteksi!
echo.
echo SOLUSI:
echo 1. Buka XAMPP Control Panel
echo 2. Start Apache
echo 3. Jalankan file ini lagi
echo.

:end
echo.
echo ========================================
timeout /t 3 /nobreak >nul
