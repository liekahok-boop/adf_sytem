# 🎯 Panduan Cepat untuk Owner

## Akses Dashboard Owner

URL: `http://localhost/narayana/modules/owner/dashboard.php`

### Login
- Username: owner (sesuai data Anda)
- Password: (password owner)

## 📊 Fitur Dashboard

### 1. Pilih Bisnis (Branch Selector)
- **All Branches**: Lihat gabungan semua bisnis
- **Pilih Spesifik**: Lihat data 1 bisnis saja

### 2. Statistik Hari Ini
- 💰 Total Pemasukan
- 💸 Total Pengeluaran
- Jumlah transaksi

### 3. Statistik Bulan Ini
- Pendapatan bulan ini
- Perbandingan dengan bulan lalu (%)
- Trend naik/turun

### 4. Grafik Trend
Pilih periode:
- **7 Days**: Trend 1 minggu terakhir
- **30 Days**: Trend bulan ini (1-31)
- **12 Months**: Trend tahun ini (Jan-Des)

### 5. Occupancy (untuk Hotel)
- Total kamar
- Kamar terisi
- Kamar tersedia
- Kamar maintenance
- Check-in/out hari ini

### 6. Transaksi Terbaru
- 10 transaksi terakhir dari semua bisnis
- Termasuk nama bisnis

## 👥 Kelola Akses User

### Cara Akses
1. Klik icon **👥 Users** di header dashboard
2. Atau buka: `http://localhost/narayana/modules/owner/manage-user-access.php`

### Cara Mengatur Akses
1. Lihat daftar semua user
2. Centang bisnis yang boleh diakses user tersebut
3. Perubahan **otomatis tersimpan**

### Contoh Penggunaan
- **Manager Hotel**: Centang hanya "Hotel"
- **Manager Multi**: Centang "Hotel" + "Ben's Cafe" + "Eat&Meet"
- **Finance**: Centang semua bisnis untuk laporan keuangan

## 🔄 Refresh Data

### Manual Refresh
Klik icon **🔄 Refresh** di pojok kanan atas

### Auto Refresh
Dashboard otomatis refresh saat:
- Ganti periode grafik
- Ganti pilihan bisnis
- Reload halaman

### Pull to Refresh (Mobile)
Tarik layar ke bawah untuk refresh (seperti Instagram)

## 📱 Akses Mobile

Dashboard sudah **mobile-responsive**:
- Bisa diakses dari HP/tablet
- Touch-friendly buttons
- Scroll lancar
- Pull to refresh

### Shortcut Mobile
1. Buka di browser mobile
2. Tap menu browser (⋮)
3. Pilih "Add to Home Screen"
4. Icon muncul di home screen HP

## ⚙️ Pengaturan Lanjutan

### Tambah Bisnis Baru
1. Buat database baru
2. Edit `config/businesses.php`
3. Jalankan `tools/sync-all-tables.php`
4. Grant akses user via UI

### Tambah User Baru
1. Buka User Management
2. Create user di database
3. Set business_access via UI

### Backup Data
Setiap bisnis punya database terpisah:
- `narayana_benscafe`
- `narayana_hotel`
- `narayana_eatmeet`
- `narayana_pabrikkapal`
- `narayana_furniture`
- `narayana_karimunjawa`

Export masing-masing via phpMyAdmin

## ❓ FAQ

### Q: Kenapa saya tidak lihat bisnis tertentu?
**A:** Cek field `business_access` di tabel users. Pastikan ID bisnis ada di list.

### Q: Data tidak muncul?
**A:** 
1. Cek koneksi database
2. Refresh browser (Ctrl+F5)
3. Cek console browser untuk error
4. Pastikan business_access tidak NULL

### Q: Cara tambah user baru?
**A:** 
```sql
INSERT INTO users (username, password, full_name, role, business_access) 
VALUES ('newuser', MD5('password'), 'New User', 'staff', '[1,2]');
```

### Q: Grafik tidak update?
**A:** Klik icon refresh atau ganti periode grafik.

## 🎯 Tips & Tricks

### 1. Monitoring Real-time
- Buka dashboard di tab terpisah
- Refresh berkala untuk data terbaru
- Gunakan "All Branches" untuk overview

### 2. Analisis Per Bisnis
- Ganti ke bisnis spesifik
- Lihat grafik trend
- Bandingkan dengan bisnis lain

### 3. Manage Team
- Berikan akses terbatas untuk staff
- Owner/Manager dapat akses multiple
- Finance dapat akses semua untuk laporan

### 4. Mobile Access
- Add to home screen untuk akses cepat
- Notifikasi via WhatsApp/Telegram (coming soon)
- Dashboard bisa dibuka di mana saja

## 📞 Butuh Bantuan?

Baca dokumentasi lengkap:
- `MULTI-BUSINESS-GUIDE.md` - Dokumentasi teknis
- `PORT-8080-GUIDE.md` - Setup XAMPP
- `INSTALL.md` - Installation guide

---

**Quick Links:**
- Dashboard: http://localhost/narayana/modules/owner/dashboard.php
- User Management: http://localhost/narayana/modules/owner/manage-user-access.php
- Main App: http://localhost/narayana/

**Last Updated:** <?= date('Y-m-d H:i:s') ?>
