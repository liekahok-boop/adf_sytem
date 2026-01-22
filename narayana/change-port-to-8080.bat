@echo off
color 0E
echo ========================================
echo   GANTI PORT APACHE - Port 80 to 8080
echo ========================================
echo.
echo Port 80 sudah dipakai aplikasi lain (Dinara)
echo Mengubah Apache ke port 8080...
echo.
echo ========================================
echo.

REM Backup httpd.conf
echo [1/3] Backup httpd.conf...
if exist "C:\xampp\apache\conf\httpd.conf" (
    copy "C:\xampp\apache\conf\httpd.conf" "C:\xampp\apache\conf\httpd.conf.backup" >nul
    echo     Backup berhasil: httpd.conf.backup
) else (
    echo     ERROR: File httpd.conf tidak ditemukan!
    pause
    exit
)

echo.
echo [2/3] Mengubah Listen Port 80 menjadi 8080...

REM Replace Listen 80 to Listen 8080
powershell -Command "(Get-Content 'C:\xampp\apache\conf\httpd.conf') -replace '^Listen 80$', 'Listen 8080' | Set-Content 'C:\xampp\apache\conf\httpd.conf'"

REM Replace ServerName localhost:80 to localhost:8080
powershell -Command "(Get-Content 'C:\xampp\apache\conf\httpd.conf') -replace 'ServerName localhost:80', 'ServerName localhost:8080' | Set-Content 'C:\xampp\apache\conf\httpd.conf'"

echo     Port berhasil diubah ke 8080

echo.
echo [3/3] Backup httpd-ssl.conf...
if exist "C:\xampp\apache\conf\extra\httpd-ssl.conf" (
    copy "C:\xampp\apache\conf\extra\httpd-ssl.conf" "C:\xampp\apache\conf\extra\httpd-ssl.conf.backup" >nul
    
    REM Replace port 443 references if needed
    powershell -Command "(Get-Content 'C:\xampp\apache\conf\extra\httpd-ssl.conf') -replace 'localhost:80', 'localhost:8080' | Set-Content 'C:\xampp\apache\conf\extra\httpd-ssl.conf'"
    
    echo     SSL config updated
)

echo.
echo ========================================
echo   PERUBAHAN BERHASIL!
echo ========================================
echo.
echo Apache sekarang akan jalan di port 8080
echo.
echo LANGKAH SELANJUTNYA:
echo 1. Buka XAMPP Control Panel
echo 2. STOP Apache (jika sedang jalan)
echo 3. START Apache lagi
echo 4. Buka browser: http://localhost:8080/narayana
echo.
echo ========================================
echo.
pause
