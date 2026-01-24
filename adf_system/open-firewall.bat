@echo off
echo ========================================
echo   Membuka Firewall untuk XAMPP Apache
echo ========================================
echo.

echo Menambahkan rule firewall untuk port 8080...
netsh advfirewall firewall add rule name="Apache Port 8080" dir=in action=allow protocol=TCP localport=8080

echo.
echo Menambahkan rule firewall untuk port 80...
netsh advfirewall firewall add rule name="Apache Port 80" dir=in action=allow protocol=TCP localport=80

echo.
echo ========================================
echo   SELESAI!
echo ========================================
echo.
echo Firewall sudah dibuka untuk Apache.
echo Sekarang HP bisa akses:
echo   http://192.168.1.2:8080/narayana/
echo.
pause
