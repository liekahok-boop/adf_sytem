@echo off
echo ================================================
echo MEMBUKA XAMPP CONTROL PANEL...
echo ================================================
echo.

REM Try to open XAMPP Control Panel
if exist "C:\xampp\xampp-control.exe" (
    start "" "C:\xampp\xampp-control.exe"
    echo XAMPP Control Panel dibuka!
    echo.
    echo LANGKAH SELANJUTNYA:
    echo 1. Klik START di Apache
    echo 2. Klik START di MySQL
    echo 3. Tunggu sampai kedua tombol jadi hijau
    echo.
) else (
    echo ERROR: XAMPP tidak ditemukan di C:\xampp\
    echo.
    echo Apakah XAMPP terinstall di lokasi lain?
    echo Silakan buka XAMPP Control Panel secara manual
    echo.
)

echo ================================================
echo Tekan tombol apa saja untuk tutup...
pause >nul
