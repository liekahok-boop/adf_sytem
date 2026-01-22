# 🏢 Owner Monitoring System - Multi Branch

## 📱 Dashboard Owner yang Mobile-Friendly

Sistem monitoring untuk owner bisnis yang dapat diakses dari ponsel untuk memantau transaksi dan occupancy room dari berbagai cabang.

## ✨ Fitur Utama

### 1. **Mobile-First Dashboard**
- ✅ Responsive design optimal untuk smartphone
- ✅ Pull-to-refresh untuk update data
- ✅ Auto-refresh setiap 2 menit
- ✅ Dark mode support
- ✅ Fast loading dengan optimized API

### 2. **Multi-Branch Support**
- ✅ Owner bisa pilih cabang yang ingin di-monitor
- ✅ Admin bisa assign akses cabang ke owner
- ✅ Real-time switching antar cabang
- ✅ Monitoring semua cabang sekaligus (all branches)

### 3. **Real-Time Monitoring**
- 📊 Pemasukan & pengeluaran hari ini
- 📊 Pemasukan & pengeluaran bulan ini
- 📊 Perbandingan dengan bulan sebelumnya (%)
- 📊 Grafik 7 hari terakhir
- 🏠 Occupancy rate kamar
- 📝 Transaksi terakhir real-time

### 4. **Room Occupancy**
- Total kamar tersedia
- Kamar terisi (occupied)
- Occupancy rate (%)
- Check-in & check-out hari ini

## 🚀 Instalasi

### 1. Jalankan Installer
Buka di browser:
```
http://localhost/narayana/install-owner-system.php
```

Installer akan:
- ✅ Membuat tabel `branches`
- ✅ Membuat tabel `owner_branch_access`
- ✅ Menambah role `owner` ke tabel users
- ✅ Menambah kolom `branch_id` ke tabel terkait
- ✅ Membuat 3 cabang default

### 2. Buat User Owner
1. Login sebagai admin
2. Buka **Settings → Kelola User**
3. Tambah user baru dengan role **"Owner (Read-Only)"**
4. Simpan user

### 3. Kelola Cabang
1. Buka **Settings → Kelola Cabang**
2. Tambah, edit, atau hapus cabang
3. Setiap cabang punya kode unik (contoh: HQ, CBG001)

### 4. Assign Akses Owner ke Cabang
```sql
-- Berikan akses owner ke cabang tertentu
INSERT INTO owner_branch_access (user_id, branch_id, granted_by)
VALUES (
    (SELECT id FROM users WHERE username = 'owner1'),
    (SELECT id FROM branches WHERE branch_code = 'HQ'),
    (SELECT id FROM users WHERE username = 'admin')
);
```

## 📱 Cara Menggunakan Owner Dashboard

### Login Owner
```
URL: http://localhost/narayana/login.php
Username: [username_owner]
Password: [password_owner]
```

### Akses Dashboard
Setelah login, owner akan diarahkan ke:
```
http://localhost/narayana/modules/owner/dashboard.php
```

### Fitur Dashboard:
1. **Pilih Cabang** - Dropdown di bagian atas untuk switch cabang
2. **Statistics Cards** - 4 kartu menampilkan:
   - Pemasukan hari ini
   - Pengeluaran hari ini
   - Pemasukan bulan ini
   - Pengeluaran bulan ini
3. **Occupancy Bar** - Progress bar occupancy room
4. **Weekly Chart** - Grafik 7 hari terakhir
5. **Recent Transactions** - Daftar transaksi terbaru
6. **Refresh Button** - Tombol refresh manual di header
7. **Bottom Navigation** - Menu cepat di bawah (mobile)

### Akses dari Ponsel:
1. Buka browser di HP (Chrome/Safari)
2. Masukkan URL: `http://[IP_SERVER]/narayana/`
3. Login dengan akun owner
4. Dashboard akan otomatis responsive!

**Tips Akses dari HP:**
- Tambahkan bookmark ke home screen
- Gunakan mode full screen browser
- Enable pull-to-refresh

## 🔧 Struktur Database

### Tabel: `branches`
```sql
- id (PK)
- branch_code (UNIQUE) - Kode cabang unik
- branch_name - Nama cabang
- address - Alamat
- city - Kota
- phone - Telepon
- email - Email
- is_active - Status aktif (1/0)
- created_at
- updated_at
```

### Tabel: `owner_branch_access`
```sql
- id (PK)
- user_id (FK to users)
- branch_id (FK to branches)
- granted_at - Kapan akses diberikan
- granted_by (FK to users) - Admin yang memberikan akses
```

### Kolom Tambahan di Tabel Lain:
- `cash_book.branch_id` - Link transaksi ke cabang
- `frontdesk_rooms.branch_id` - Link room ke cabang
- `frontdesk_reservations.branch_id` - Link reservasi ke cabang
- `frontdesk_room_types.branch_id` - Link room type ke cabang
- `frontdesk_buildings.branch_id` - Link building ke cabang

## 📡 API Endpoints

### 1. Get Branches (Owner/Admin)
```
GET /api/owner-branches.php
Response: {
    "success": true,
    "branches": [...],
    "count": 3
}
```

### 2. Get Statistics
```
GET /api/owner-stats.php?branch_id=1
Response: {
    "success": true,
    "today": {...},
    "month": {...}
}
```

### 3. Get Occupancy
```
GET /api/owner-occupancy.php?branch_id=1
Response: {
    "success": true,
    "total_rooms": 50,
    "occupied_rooms": 35,
    "occupancy_rate": 70.0
}
```

### 4. Get Weekly Chart Data
```
GET /api/owner-weekly-chart.php?branch_id=1
Response: {
    "success": true,
    "labels": [...],
    "income": [...],
    "expense": [...]
}
```

### 5. Get Recent Transactions
```
GET /api/owner-recent-transactions.php?branch_id=1&limit=10
Response: {
    "success": true,
    "transactions": [...],
    "count": 10
}
```

## 🔐 Permissions & Security

### Role: Owner
- ✅ **Dapat Akses:**
  - Dashboard (read-only)
  - Reports (view only)
  - Owner monitoring dashboard
  
- ❌ **Tidak Dapat:**
  - Tambah/edit/hapus transaksi
  - Tambah/edit/hapus data master
  - Kelola user
  - Kelola settings

### Auth Check di API:
```php
if ($currentUser['role'] !== 'owner' && $currentUser['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}
```

## 🎨 Design Features

### Mobile Optimizations:
- **Touch-friendly**: Semua button minimal 44x44px
- **Readable text**: Minimum font-size 14px
- **Fast loading**: API calls dioptimalkan
- **Pull to refresh**: Native-like UX
- **Bottom navigation**: Easy thumb reach
- **Fixed header**: Always visible

### Performance:
- **Auto-refresh**: Every 2 minutes
- **Lazy loading**: Data dimuat on-demand
- **Caching**: Browser cache untuk assets
- **Minified**: CSS inline untuk fast load

## 📊 Use Cases

### 1. Monitoring Harian
Owner cek dashboard setiap pagi untuk lihat:
- Pemasukan kemarin
- Occupancy rate
- Transaksi mencurigakan

### 2. Monitoring Multi-Cabang
Owner punya 3 cabang:
- Switch dropdown untuk lihat performa tiap cabang
- Compare data antar cabang
- Identifikasi cabang yang perlu perhatian

### 3. Mobile Monitoring
Owner sedang travelling:
- Akses dari HP kapan saja
- Real-time update
- Pull-to-refresh untuk data terbaru

## 🔄 Update & Maintenance

### Menambah Cabang Baru:
1. Buka **Settings → Kelola Cabang**
2. Klik **"Tambah Cabang"**
3. Isi form (kode, nama, alamat, dll)
4. Simpan

### Memberikan Akses Owner:
```sql
INSERT INTO owner_branch_access (user_id, branch_id, granted_by)
VALUES ([owner_user_id], [new_branch_id], [admin_id]);
```

### Menonaktifkan Cabang:
Edit cabang dan uncheck "Aktif"

## 🐛 Troubleshooting

### Owner tidak bisa akses dashboard?
1. Cek role user = 'owner'
2. Cek ada akses di `owner_branch_access`
3. Cek cabang is_active = 1

### Data tidak muncul?
1. Cek kolom `branch_id` ada di tabel
2. Cek transaksi punya `branch_id` yang benar
3. Cek API response di browser console

### Dashboard tidak responsive?
1. Clear browser cache
2. Pastikan meta viewport ada di header
3. Test di browser mobile mode

## 📞 Support

Jika ada pertanyaan atau butuh bantuan, hubungi developer:
- Email: admin@narayana.com
- WhatsApp: [Nomor WA dari settings]

## 🎉 What's Next?

Fitur yang akan datang:
- [ ] Push notifications untuk transaksi besar
- [ ] Export reports ke PDF dari mobile
- [ ] Grafik perbandingan antar cabang
- [ ] Dashboard analitik lanjutan
- [ ] Forecast & predictions
- [ ] Custom alerts & thresholds

---

**Developed with ❤️ for Narayana Hotel Management System**
