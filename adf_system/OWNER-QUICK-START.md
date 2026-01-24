# 🚀 Quick Start - Owner Dashboard

## Instalasi Cepat (5 Menit)

### 1️⃣ Install Database
Buka browser:
```
http://localhost/narayana/install-owner-system.php
```
Klik install dan tunggu sampai selesai ✅

### 2️⃣ Buat User Owner
1. Login sebagai `admin`
2. Buka **Settings → Kelola User**
3. Tambah user baru:
   - Username: `owner1`
   - Password: `owner123`
   - Role: **Owner (Read-Only)**
   - Aktif: ✓
4. Simpan

### 3️⃣ Kelola Cabang
Buka **Settings → Kelola Cabang** untuk:
- Lihat cabang default (HQ, Bandung, Surabaya)
- Tambah cabang baru sesuai kebutuhan
- Edit informasi cabang

### 4️⃣ Berikan Akses Owner
Jalankan SQL ini (ganti `owner1` dengan username owner Anda):
```sql
-- Berikan akses ke semua cabang
INSERT INTO owner_branch_access (user_id, branch_id, granted_by)
SELECT 
    (SELECT id FROM users WHERE username = 'owner1'),
    b.id,
    (SELECT id FROM users WHERE username = 'admin')
FROM branches b
WHERE b.is_active = 1;
```

### 5️⃣ Login Owner & Akses Dashboard
1. Logout dari admin
2. Login dengan:
   - Username: `owner1`
   - Password: `owner123`
3. Otomatis diarahkan ke Owner Dashboard! 🎉

## 📱 Akses dari HP

### Cara 1: Via IP Lokal (Same WiFi)
```
http://[IP-KOMPUTER]/narayana/modules/owner/dashboard.php
```
Contoh: `http://192.168.1.100/narayana/modules/owner/dashboard.php`

**Cara cari IP komputer:**
- Windows: Buka CMD → ketik `ipconfig` → lihat IPv4 Address
- Mac/Linux: Buka Terminal → ketik `ifconfig` → lihat inet

### Cara 2: Bookmark ke Home Screen
1. Buka dashboard di mobile browser
2. Chrome: Menu → Add to Home Screen
3. Safari: Share → Add to Home Screen
4. Sekarang bisa buka seperti native app! 📱

## 🎯 Fitur Dashboard Owner

| Fitur | Deskripsi |
|-------|-----------|
| 📊 Statistics | Pemasukan & pengeluaran hari ini + bulan ini |
| 🏠 Occupancy | Rate okupansi kamar real-time |
| 📈 Chart | Grafik 7 hari terakhir |
| 📝 Transactions | Daftar transaksi terbaru |
| 🔄 Refresh | Pull-to-refresh & auto-refresh 2 menit |
| 🏢 Branch Switch | Pilih cabang yang ingin di-monitor |

## ⚡ Tips & Tricks

### Mobile Tips:
- **Pull down** di bagian atas untuk refresh data
- **Bookmark** ke home screen untuk akses cepat
- **Landscape mode** untuk lihat chart lebih jelas
- Dashboard **auto-refresh** setiap 2 menit

### Monitoring Tips:
- Cek dashboard setiap pagi untuk review kemarin
- Compare data antar cabang via dropdown
- Perhatikan trend di weekly chart
- Monitor occupancy rate untuk optimasi pricing

## 🔐 Keamanan

Owner hanya punya akses **READ-ONLY**:
- ✅ Bisa lihat dashboard & laporan
- ✅ Bisa switch antar cabang
- ❌ **TIDAK BISA** tambah/edit/hapus transaksi
- ❌ **TIDAK BISA** kelola data master
- ❌ **TIDAK BISA** ubah settings

## 📞 Butuh Bantuan?

Lihat dokumentasi lengkap: `OWNER-DASHBOARD-GUIDE.md`

---

**Narayana Hotel Management System** - Owner Dashboard v1.0
