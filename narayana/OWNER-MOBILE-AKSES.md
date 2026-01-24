# 📱 Akses Owner Dashboard dari HP/Tablet

## 🚀 3 Langkah Cepat

### 1️⃣ Buka Firewall (WAJIB!)
```
Klik kanan file: open-firewall.bat
Pilih: "Run as Administrator"
```

### 2️⃣ Pastikan Apache Running
```
Buka XAMPP Control Panel
Apache harus HIJAU (running)
```

### 3️⃣ Akses dari HP
```
Buka browser HP
Ketik: http://192.168.1.2:8080/narayana/owner-login.php
```

---

## 📱 URL untuk Owner Dashboard

### Login Owner (Khusus Owner):
```
http://192.168.1.2:8080/narayana/owner-login.php
```

### Dashboard Owner:
```
http://192.168.1.2:8080/narayana/modules/owner/dashboard.php
```

### Login Staff (General):
```
http://192.168.1.2:8080/narayana/login.php
```

---

## ✅ Syarat Penting

**1. WiFi Harus Sama**
- HP dan komputer harus terhubung ke WiFi yang SAMA
- Cek nama WiFi di HP: Settings → WiFi
- Harus sama dengan WiFi komputer

**2. IP Address Komputer**
- IP komputer Anda saat ini: **192.168.1.2**
- Jika ganti WiFi, IP bisa berubah
- Cek IP terbaru: Buka `check-ip.bat`

**3. Firewall Terbuka**
- Wajib jalankan `open-firewall.bat` sebagai admin
- Hanya perlu 1x saja
- Kalau masih gagal, restart komputer

---

## 🧪 Test Koneksi

### Step 1: Test dari Komputer
Buka browser komputer, ketik:
```
http://192.168.1.2:8080/narayana/owner-login.php
```

✅ Jika berhasil → lanjut ke Step 2
❌ Jika gagal → cek XAMPP Apache sudah running

### Step 2: Test dari HP
1. Pastikan HP terhubung WiFi yang sama
2. Buka browser (Chrome/Safari)
3. Ketik: `http://192.168.1.2:8080/narayana/owner-login.php`
4. Login dengan username owner

---

## 📲 Simpan ke Home Screen (Seperti App)

### Android (Chrome):
1. Buka owner dashboard di Chrome
2. Tap menu (⋮) → **Add to Home screen**
3. Nama: **Narayana Owner**
4. Tap **Add**
5. ✅ Sekarang ada icon di home screen!

### iPhone (Safari):
1. Buka owner dashboard di Safari
2. Tap tombol **Share** (📤)
3. Scroll down → **Add to Home Screen**
4. Nama: **Narayana Owner**
5. Tap **Add**
6. ✅ Sekarang ada icon di home screen!

---

## 🔥 Jika Firewall Tidak Terbuka (Manual)

1. Tekan `Win + R`
2. Ketik: `wf.msc` → Enter
3. Klik **Inbound Rules** → **New Rule**
4. Pilih **Port** → Next
5. **TCP** → Specific ports: **8080** → Next
6. **Allow the connection** → Next
7. Centang semua (Domain, Private, Public) → Next
8. Name: **Apache 8080** → Finish
9. Ulangi untuk port **80**

---

## ⚠️ Troubleshooting

### Problem: "Site can't be reached"

**Solusi 1: Cek Firewall**
```
Jalankan: open-firewall.bat (sebagai admin)
Restart browser HP
```

**Solusi 2: Cek IP Address**
```
IP mungkin berubah
Jalankan: check-ip.bat
Update URL dengan IP baru
```

**Solusi 3: Cek WiFi**
```
HP dan komputer HARUS di WiFi yang sama
Bukan data seluler!
```

**Solusi 4: Restart Apache**
```
XAMPP Control Panel:
1. Stop Apache
2. Start Apache lagi
```

### Problem: "Connection refused"

**Apache tidak running**
```
Buka XAMPP Control Panel
Klik Start pada Apache
Tunggu sampai hijau
```

### Problem: IP berubah-ubah

**Gunakan IP Statis (Opsional)**
```
1. Buka Network Settings
2. WiFi → Properties
3. Edit IP Settings → Manual
4. Set IP: 192.168.1.2
5. Gateway: 192.168.1.1
6. DNS: 8.8.8.8
```

---

## 💡 Tips Mobile Friendly

Owner Dashboard sudah dirancang mobile-friendly:
- ✅ Responsive design
- ✅ Touch-friendly buttons
- ✅ Pull-to-refresh
- ✅ Smooth animations
- ✅ Easy navigation

**Untuk pengalaman terbaik:**
- Gunakan Chrome di Android
- Gunakan Safari di iPhone
- Simpan ke home screen
- Gunakan mode landscape untuk chart

---

## 🔐 Keamanan

**PENTING:**
- Owner dashboard hanya bisa diakses oleh user dengan role "owner"
- Pastikan password owner kuat
- Jangan share link owner-login.php ke staff
- Untuk staff, gunakan login.php biasa

**Login Credentials:**
```
Owner:
- Username: (sesuai database)
- Password: (sesuai database)
- Role: owner

Staff:
- URL berbeda: login.php
- Tidak bisa akses owner dashboard
```

---

## 📞 Bantuan Lebih Lanjut

Jika masih ada masalah:
1. Cek log Apache di XAMPP
2. Restart komputer
3. Coba browser lain di HP
4. Pastikan tidak ada VPN aktif
5. Coba nonaktifkan antivirus sementara

---

**Happy Monitoring! 📊**
