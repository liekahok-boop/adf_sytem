# 🎯 MULAI DARI SINI - PANDUAN SEDERHANA

## ⚠️ JANGAN BINGUNG! Ikuti urutan ini:

### LANGKAH 1: Buka Link Utama
```
http://localhost:8080/narayana/home.php
```

### LANGKAH 2: Pilih Yang Mana?

#### 🟢 UNTUK PAKAI SISTEM SEHARI-HARI → Klik "System Login"
- Login: `admin` / Password: `admin`
- Atau: `staff1` / Password: `staff123`
- Ini untuk input transaksi, lihat cashbook, dll

#### 🔵 UNTUK OWNER LIHAT LAPORAN → Klik "Owner Dashboard"  
- Login: `rob` / Password: `owner123`
- Atau: `admin` / Password: `admin` (admin bisa lihat semua)
- Ini untuk monitor bisnis, lihat grafik, laporan

#### 🔴 UNTUK SETUP/SETTING → Klik "Developer Panel"
- Login: `devadmin` / Password: `dev123`
- Atau: `admin` / Password: `admin`
- Ini untuk tambah user, setting akses, dll

---

## 📊 BISNIS YANG SUDAH ADA:

1. **Narayana Hotel** (ID: 1)
2. **Bens Cafe** (ID: 2)

---

## 👥 USER YANG SUDAH ADA:

| Username | Password   | Role    | Bisa Akses Bisnis |
|----------|------------|---------|-------------------|
| admin    | admin      | admin   | Semua bisnis      |
| staff1   | staff123   | staff   | Narayana & Bens   |
| rob      | owner123   | owner   | Bens Cafe saja    |
| devadmin | dev123     | admin   | Semua bisnis      |

---

## 🚀 SKENARIO PENGGUNAAN:

### Scenario 1: Saya mau input transaksi Bens Cafe
1. Buka: http://localhost:8080/narayana/login.php
2. Login: `admin` / `admin`
3. Pilih bisnis: **Bens Cafe**
4. Klik menu sesuai kebutuhan (Cashbook, dll)

### Scenario 2: Saya mau lihat laporan semua bisnis (sebagai owner)
1. Buka: http://localhost:8080/narayana/owner-login.php
2. Login: `admin` / `admin` (atau `rob` / `owner123`)
3. Lihat dashboard dengan grafik dan laporan

### Scenario 3: Saya mau tambah user baru atau setting
1. Buka: http://localhost:8080/narayana/tools/developer-panel.php
2. Login: `devadmin` / `dev123`
3. Gunakan tools yang tersedia

---

## ❓ FAQ:

**Q: Kenapa ada 3 login berbeda?**
A: Karena fungsinya beda:
- System Login = untuk kerja sehari-hari
- Owner Login = untuk owner monitor bisnis
- Developer Panel = untuk setup dan maintenance

**Q: Saya login tapi menu kosong?**
A: Pastikan sudah pilih bisnis dulu setelah login!

**Q: Pemilihan bisnis membingungkan?**
A: Bisnis yang muncul sesuai dengan akses user Anda:
- admin → lihat semua bisnis
- rob → hanya lihat Bens Cafe
- staff1 → lihat Narayana & Bens

**Q: Data bisnis kosong?**
A: Data sudah saya setup:
- Tabel `businesses` sudah dibuat
- Narayana Hotel dan Bens Cafe sudah terdaftar
- Users sudah punya business_access

---

## 🎯 REKOMENDASI MULAI:

**CARA PALING MUDAH:**
1. Buka: http://localhost:8080/narayana/home.php
2. Klik: "System Login"
3. Login: `admin` / `admin`
4. Pilih: **Bens Cafe** atau **Narayana Hotel**
5. Coba menu yang ada

**Jika masih bingung, coba ini:**
```
http://localhost:8080/narayana/tools/test-system.php
```
(Saya akan buat file test untuk cek semua setup)

---

## 📞 BUTUH BANTUAN?

Jika masih bingung, screenshot layar yang Anda lihat dan tunjukkan ke saya. Saya akan bantu step-by-step!

---

✅ **STATUS SAAT INI:**
- Database narayana: ✅ Ada
- Tabel businesses: ✅ Sudah dibuat
- Data bisnis: ✅ Narayana Hotel & Bens Cafe sudah ada
- Users: ✅ admin, staff1, rob, devadmin sudah ada
- Authentication: ✅ Sudah diperbaiki
- Preferences table: ✅ Sudah ada
