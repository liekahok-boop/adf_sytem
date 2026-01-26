# 🚀 QUICK START GUIDE - INVESTOR & PROJECT MODULE

## ⚡ Instalasi Cepat (5 Menit)

### STEP 1️⃣: Jalankan Database Migration

**Option A: Klik File Batch (Termudah - Windows)**
```
1. Buka: c:\xampp\htdocs\adf_system\
2. Double-click: setup-investor-project.bat
3. Masukkan password MySQL (kosongkan jika tidak ada)
4. Tunggu sampai selesai
5. Tekan Enter
```

**Option B: Via Terminal**
```bash
cd c:\xampp\htdocs\adf_system
mysql -u root narayana_hotel < database/migration-investor-project.sql
```

**Option C: Via Browser (Jika script di atas error)**
```
1. Login ke admin account
2. Buka: http://localhost:8080/adf_system/install-investor-project.php
3. Klik tombol "Install"
4. Tunggu sampai berhasil
```

---

### STEP 2️⃣: Verifikasi Menu di Sidebar

Login ke aplikasi dan lihat sidebar. Anda harusnya melihat:
- ✅ **Investor** (menu baru)
- ✅ **Project** (menu baru)
- ✅ **Settings** (sudah ada)
  - Kelola User (submenu)

---

### STEP 3️⃣: Test Fitur

#### A. BUAT INVESTOR

```
1. Klik menu: Investor
2. Klik tombol: "Tambah Investor"
3. Isi form:
   - Nama Investor: "PT. Investasi Indonesia"
   - Alamat: "Jl. Sudirman No. 123, Jakarta"
   - Kontak: "021-123456"
   - Email: "investor@example.com"
4. Klik: "Simpan Investor"
5. ✓ Investor berhasil dibuat!
```

#### B. TAMBAH MODAL INVESTOR (USD → IDR Otomatis)

```
1. Di halaman Investor, klik investor yang baru dibuat
2. Klik tombol: "Tambah Transaksi Modal" (+)
3. Isi form:
   - Jumlah USD: 10000
   - Lihat: Otomatis muncul Rp 155,000,000 (10000 × 15500)
   - Kurs akan diambil dari bank indonesia API
   - Tanggal: 2026-01-25 (today)
   - Metode: Transfer Bank
4. Klik: "Simpan Transaksi"
5. ✓ Modal berhasil ditambahkan!
6. Cek dashboard: "Saldo Tersedia" harus Rp 155,000,000
```

#### C. BUAT PROJECT

```
1. Klik menu: Project
2. Klik tombol: "Tambah Project"
3. Isi form:
   - Kode Project: PRJ001
   - Nama Project: "Konstruksi Rumah Tipe A"
   - Lokasi: "Depok, Jawa Barat"
   - Tanggal Mulai: 2026-01-25
   - Tanggal Selesai: 2026-03-25
   - Budget: 100000000
   - Status: "Ongoing"
4. Klik: "Simpan Project"
5. ✓ Project berhasil dibuat!
```

#### D. TAMBAH PENGELUARAN PROJECT (Test Auto-Deduction)

```
1. Di halaman Project, klik project yang baru dibuat
2. Klik tombol: "Tambah Pengeluaran" (+)
3. Isi form:
   - Kategori: "Pembelian Material" ← Pilih dari 4 kategori
   - Tanggal: 2026-01-25
   - Jumlah: 50000000
   - Metode: Tunai
   - Deskripsi: "Beli batu bata dan semen"
   - Status Pengeluaran: **"Approved (Langsung Potong Saldo)"** ← PENTING!
4. Klik: "Simpan Pengeluaran"
5. ✓ Pengeluaran berhasil ditambahkan!

⚠️ LIHAT PERUBAHAN SALDO:
6. Buka menu: Investor
7. Lihat saldo investor:
   - Sebelum: Rp 155,000,000
   - Sesudah: Rp 105,000,000 (otomatis berkurang Rp 50,000,000)
8. ✓ AUTO-DEDUCTION BERHASIL!
```

---

## 📊 Dashboard Overview

### Investor Module Dashboard
```
┌─────────────────────────────────────────────────────┐
│  Total Modal Masuk      │  Total Pengeluaran        │
│  Rp 155,000,000        │  Rp 50,000,000           │
│  (USD 10,000 × 15500)  │  (Project expenses)      │
├─────────────────────────────────────────────────────┤
│  Saldo Tersedia        │  Jumlah Investor         │
│  Rp 105,000,000        │  1                        │
│  (Modal - Pengeluaran) │  (Active)                 │
└─────────────────────────────────────────────────────┘

CHART: Bar chart menunjukkan akumulasi modal per investor
```

### Project Module Dashboard
```
┌─────────────────────────────────────────────────────┐
│  Total Pengeluaran     │  Total Budget             │
│  Rp 50,000,000        │  Rp 100,000,000          │
├─────────────────────────────────────────────────────┤
│  Project Aktif        │  Total Project           │
│  1                    │  1                        │
└─────────────────────────────────────────────────────┘

CHART: Doughnut chart menunjukkan pengeluaran per project
```

---

## 🎯 Alur Lengkap (Dari Awal Sampai Akhir)

```
INVESTOR MASUK MODAL
    ↓
    USD 10,000 (dikompilasikan otomatis)
    ↓
    Rp 155,000,000 (disimpan di database)
    ↓
    SALDO INVESTOR = Rp 155,000,000

        ↓↓↓ KEMUDIAN ↓↓↓

PROJECT BUAT EXPENSE
    ↓
    Kategori: Pembelian Material
    Jumlah: Rp 50,000,000
    Status: APPROVED ← Trigger auto-deduction!
    ↓
    OTOMATIS UPDATE SALDO:
    Rp 155,000,000 - Rp 50,000,000 = Rp 105,000,000
    ↓
    SALDO INVESTOR BERKURANG! ✓

        ↓↓↓ JIKA ADA PROJECT LAIN ↓↓↓

PROJECT 2 BUAT EXPENSE
    ↓
    Jumlah: Rp 30,000,000
    Status: APPROVED
    ↓
    SALDO BERKURANG LAGI:
    Rp 105,000,000 - Rp 30,000,000 = Rp 75,000,000
```

---

## 💱 Kurs USD → IDR Automatic

### Bagaimana Cara Kerjanya?

```
1. Admin/User input: USD 10,000
2. Sistem otomatis:
   a. Query database: SELECT usd_to_idr FROM exchange_rates 
                      WHERE is_current = 1
   b. Jika tidak ada: Fetch dari API Bank Indonesia
   c. Jika API down: Gunakan fallback rate Rp 15,500/USD
   d. Hitung: 10,000 × 15,500 = Rp 155,000,000
3. Simpan ke database:
   - investor_capital_transactions.amount_usd = 10000
   - investor_capital_transactions.amount_idr = 155000000
   - investor_capital_transactions.exchange_rate = 15500
4. Update saldo: investor_balances.total_capital_idr += 155000000
```

### Update Kurs Manual (Jika API Down)

Buka MySQL dan jalankan:
```sql
-- Update current rate
INSERT INTO exchange_rates 
(date_of_rate, time_of_rate, usd_to_idr, source, is_current)
VALUES (DATE(NOW()), TIME(NOW()), 16000, 'manual_input', 1);

-- Jangan lupa unset previous
UPDATE exchange_rates SET is_current = 0 
WHERE id < (SELECT MAX(id) FROM exchange_rates);
```

---

## 🔄 Kategori Pengeluaran (Tetap)

Tidak bisa ditambah/edit - sudah fixed 4 kategori:

1. **Pembelian Material** (MAT)
   - Untuk beli batu bata, semen, keramik, cat, dll

2. **Pembayaran Truk** (TRUCK)
   - Untuk sewa/bayar truk pengangkut material

3. **Tiket Kapal** (SHIP)
   - Untuk pengiriman via kapal/laut

4. **Gaji Tukang** (LABOR)
   - Untuk bayar buruh dan tukang bangunan

---

## 🔐 Permission Requirement

User harus punya permission agar bisa akses:
- `investor` - untuk akses Investor module
- `project` - untuk akses Project module

Jika dapat error "You do not have permission":
1. Login sebagai admin
2. Buka: Settings → Kelola User
3. Edit user → Berikan permission "investor" dan "project"

---

## 📈 Chart.js Visualization

### Investor Module
```
Bar Chart: Total Modal Per Investor
├─ PT. Investasi Indonesia: Rp 155,000,000
└─ Lainnya...
```

### Project Module  
```
Doughnut Chart: Pengeluaran Per Project
├─ PRJ001 (Rumah Tipe A): Rp 50,000,000
├─ PRJ002 (Rumah Tipe B): Rp 25,000,000
└─ Lainnya...
```

### Project Detail (Belum dibuat, bisa ditambah nanti)
```
Pie Chart: Pengeluaran Per Kategori
├─ Pembelian Material: 60%
├─ Pembayaran Truk: 20%
├─ Tiket Kapal: 10%
└─ Gaji Tukang: 10%
```

---

## 🆘 TROUBLESHOOTING

| Masalah | Solusi |
|---------|--------|
| "Kurs tidak tersedia" | Jalankan: mysql INSERT kurs manual (lihat di atas) |
| Saldo tidak berkurang | Pastikan status expense = "APPROVED" |
| Menu Investor/Project tidak muncul | Jalankan migration dulu, refresh browser |
| Database error saat migration | Pastikan MySQL running, jalankan via Terminal |
| Chart tidak muncul | Refresh browser (Ctrl+F5), cek Console (F12) |
| "Unauthorized" error | Check permission di Settings → Kelola User |

---

## 📞 File Yang Penting

Jika ada error, cek file-file ini:

```
1. Database Schema:
   /database/migration-investor-project.sql
   
2. Business Logic:
   /includes/InvestorManager.php
   /includes/ProjectManager.php
   /includes/ExchangeRateManager.php
   
3. UI/Frontend:
   /modules/investor/index.php
   /modules/project/index.php
   
4. API Endpoints:
   /api/investor-*.php
   /api/project-*.php
   /api/exchange-rate-*.php
   
5. Full Documentation:
   /INVESTOR-PROJECT-README.md
```

---

## ✅ Checklist Setelah Setup

- [ ] Database migration berhasil
- [ ] Menu Investor muncul di sidebar
- [ ] Menu Project muncul di sidebar
- [ ] Buat 1 investor test
- [ ] Tambah modal USD (cek konversi ke IDR)
- [ ] Buat 1 project test
- [ ] Tambah expense dengan status "APPROVED"
- [ ] Cek saldo investor berkurang otomatis
- [ ] Lihat chart di dashboard
- [ ] Cek user permission di Settings

---

**Status**: ✅ SIAP DIGUNAKAN
**Last Updated**: 2026-01-25
**Version**: 1.0.0
