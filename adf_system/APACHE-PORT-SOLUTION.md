# 🔧 SOLUSI APACHE PORT ISSUE

## Masalah
```
Apache will not start - Port 80 sudah digunakan!
```

## Solusi - Pilih Salah Satu:

### ✅ OPSI 1: Ubah Port Apache ke 8080 (RECOMMENDED)

**Step 1: Edit konfigurasi Apache**
- Buka: `C:\xampp\apache\conf\httpd.conf`
- Cari: `Listen 80`
- Ubah menjadi: `Listen 8080`

**Step 2: Buka XAMPP Control Panel**
- Buka: `C:\xampp\xampp-control.exe`
- Klik tombol **"Start"** di Apache

**Step 3: Test aplikasi**
- Akses: `http://localhost:8080/adf_system/`

---

### ✅ OPSI 2: Gunakan PHP Built-in Server (SIMPLE)

**Step 1: Buka Command Prompt/PowerShell**
```
cd C:\xampp\htdocs\adf_system
C:\xampp\php\php.exe -S localhost:8080
```

**Step 2: Akses aplikasi**
- URL: `http://localhost:8080/`
- ✅ Sudah bisa akses aplikasi!

---

### ✅ OPSI 3: Cari & Kill Aplikasi yang Pakai Port 80

**PowerShell (as Admin):**
```powershell
netstat -ano | findstr ":80 " | findstr "LISTENING"
taskkill /PID [PID_dari_hasil_diatas] /F
```

---

## UNTUK SEKARANG - QUICK TEST

**Saya sudah start PHP server di port 8080 untuk Anda!**

Akses aplikasi:
```
http://localhost:8080/adf_system/
```

### Untuk test End Shift feature:
1. Buka browser
2. Masuk: `http://localhost:8080/adf_system/`
3. **LOGIN** (jika belum)
4. Klik tombol **"End Shift"** (pink button)
5. Buka Console (F12) untuk lihat hasilnya

---

## ⚠️ PENTING

- PHP server berjalan di background
- Jangan tutup PowerShell window tempat server running!
- Untuk stop server: Tekan `Ctrl + C` di PowerShell

---

Sudah ready! Sekarang coba akses aplikasi dan test End Shift feature! 🚀
