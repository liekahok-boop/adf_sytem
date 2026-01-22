# 📱 Cara Akses Narayana dari HP

## 🌐 IP Komputer Anda
```
192.168.1.2
```

## 📲 URL untuk HP

### Dashboard Utama:
```
http://192.168.1.2:8080/narayana/
```

### Owner Dashboard (Mobile-Friendly):
```
http://192.168.1.2:8080/narayana/modules/owner/dashboard.php
```

### Login Page:
```
http://192.168.1.2:8080/narayana/login.php
```

## ✅ Checklist Sebelum Akses

- [ ] **HP dan Komputer di WiFi yang Sama**
  - Cek di HP: Settings → WiFi → Nama jaringan
  - Harus sama dengan WiFi komputer

- [ ] **Apache XAMPP Sudah Running**
  - Cek di XAMPP Control Panel
  - Apache harus hijau (running)

- [ ] **Firewall Sudah Dibuka**
  - Jalankan file `open-firewall.bat` **sebagai Administrator**
  - Klik kanan → Run as Administrator

## 🔥 Cara Buka Firewall

### Otomatis (MUDAH):
1. Klik kanan file `open-firewall.bat`
2. Pilih **"Run as Administrator"**
3. Klik "Yes" jika muncul UAC prompt
4. Tunggu sampai selesai

### Manual (Jika batch tidak jalan):
1. Buka **Windows Defender Firewall**
2. Klik **"Advanced settings"**
3. Klik **"Inbound Rules"** → **"New Rule"**
4. Pilih **"Port"** → Next
5. Pilih **TCP** → Specific local ports: **8080**
6. Pilih **"Allow the connection"**
7. Centang semua (Domain, Private, Public)
8. Nama: **Apache Port 8080**
9. Finish

Ulangi untuk port **80** juga.

## 🧪 Test Koneksi

### Dari Komputer:
Test dulu dari browser komputer:
```
http://192.168.1.2:8080/narayana/
```

Jika berhasil di komputer, baru test dari HP.

### Dari HP:
1. Buka browser (Chrome/Safari)
2. Ketik: `http://192.168.1.2:8080/narayana/`
3. Tekan Enter
4. Login dengan username & password

## 📱 Tips Akses Mobile

### Bookmark ke Home Screen (Seperti App):

**Android (Chrome):**
1. Buka dashboard di Chrome
2. Menu (⋮) → Add to Home screen
3. Beri nama "Narayana Owner"
4. Tap Add
5. Sekarang ada icon di home screen!

**iPhone (Safari):**
1. Buka dashboard di Safari
2. Tap tombol Share (kotak dengan panah atas)
3. Scroll down → Add to Home Screen
4. Beri nama "Narayana Owner"
5. Tap Add
6. Sekarang ada icon di home screen!

## ⚠️ Troubleshooting

### "Site can't be reached" / "Tidak dapat dijangkau"

**Penyebab 1: Firewall Memblokir**
- Solusi: Jalankan `open-firewall.bat` sebagai admin

**Penyebab 2: Beda WiFi**
- Solusi: Pastikan HP dan komputer di WiFi yang sama

**Penyebab 3: IP Berubah**
- Solusi: Cek IP terbaru dengan command:
  ```
  ipconfig
  ```
  Cari IPv4 Address

**Penyebab 4: Apache Tidak Running**
- Solusi: Start Apache di XAMPP Control Panel

### Timeout / Loading Lama

**Penyebab: Sinyal WiFi Lemah**
- Solusi: Dekatkan HP ke router WiFi
- Atau restart router

### Halaman Error 404

**Penyebab: Typo URL**
- Periksa lagi URL: http://192.168.1.2:8080/narayana/
- Pastikan ada `:8080` (port)
- Pastikan ada `/narayana/` (dengan slash)

## 🌍 Akses dari Internet (Opsional)

Jika ingin akses dari luar rumah (internet), butuh:
1. **Port Forwarding** di router
2. **Dynamic DNS** (karena IP publik berubah)
3. **SSL Certificate** untuk HTTPS

Ini lebih kompleks dan membutuhkan konfigurasi router.

## 🔒 Keamanan

**Penting untuk Produksi:**
- [ ] Ganti password admin yang kuat
- [ ] Gunakan HTTPS (SSL)
- [ ] Batasi akses IP jika perlu
- [ ] Update sistem secara berkala

---

## 📞 Quick Reference

| Item | Value |
|------|-------|
| IP Komputer | 192.168.1.2 |
| Port | 8080 |
| URL Mobile | http://192.168.1.2:8080/narayana/ |
| Owner Dashboard | http://192.168.1.2:8080/narayana/modules/owner/dashboard.php |

---

**Narayana Hotel Management System** - Mobile Access Guide
