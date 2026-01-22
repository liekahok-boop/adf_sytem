# 🚀 CARA MENGATASI KONFLIK PORT 80

Port 80 sudah dipakai aplikasi **Dinara**, jadi kita ubah Apache XAMPP ke **port 8080**.

---

## ✅ CARA OTOMATIS (Paling Mudah!)

### Langkah 1: Ganti Port Apache
1. Buka folder: `C:\xampp\htdocs\narayana`
2. Klik kanan file: **`change-port-to-8080.bat`**
3. Pilih: **"Run as Administrator"**
4. Tunggu sampai selesai

### Langkah 2: Restart Apache
1. Buka **XAMPP Control Panel**
2. **STOP** Apache (klik Stop)
3. **START** Apache lagi (klik Start)
4. Apache sekarang jalan di **port 8080** ✅

### Langkah 3: Buka Aplikasi
**Klik 2x file:** `open-app.bat`

Atau buka browser manual:
```
http://localhost:8080/narayana
```

---

## 🔧 CARA MANUAL (Jika Batch File Gagal)

### Edit httpd.conf:
1. Buka: `C:\xampp\apache\conf\httpd.conf` dengan Notepad
2. Cari baris: `Listen 80`
3. Ubah jadi: `Listen 8080`
4. Cari baris: `ServerName localhost:80`
5. Ubah jadi: `ServerName localhost:8080`
6. Save file
7. Restart Apache di XAMPP Control Panel

---

## 📱 URL Aplikasi Setelah Ganti Port:

### Di PC:
```
http://localhost:8080/narayana
```

### Di HP (sama WiFi):
```
http://192.168.1.X:8080/narayana
```
*(ganti X dengan IP PC Anda - cek dengan `ipconfig`)*

---

## 🔄 Kalau Mau Balik ke Port 80 Lagi:

1. Tutup aplikasi **Dinara** dulu
2. Buka: `C:\xampp\apache\conf\httpd.conf.backup`
3. Copy isinya, paste ke `httpd.conf`
4. Restart Apache

---

## ⚠️ PENTING:

Setelah ganti port, **semua URL berubah:**
- ❌ ~~http://localhost/narayana~~ (tidak jalan lagi)
- ✅ **http://localhost:8080/narayana** (URL baru)

Config aplikasi sudah di-update otomatis untuk support port 8080! ✅

---

**Installer juga tetap jalan di:**
```
http://localhost:8080/narayana/installer.php
```
