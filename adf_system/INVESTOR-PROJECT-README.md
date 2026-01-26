# 📊 INVESTOR & PROJECT MANAGEMENT SYSTEM

Sistem manajemen investasi dan pengeluaran project yang terintegrasi dengan fitur:
- Modal investor dengan konversi USD → IDR otomatis
- Manajemen project dengan kategori pengeluaran khusus
- Saldo investor otomatis berkurang saat project expense disetujui
- Visualisasi data dengan Chart.js
- Exchange rate API integration

---

## 🎯 Fitur Utama

### 1. MODUL INVESTOR

#### Fitur:
- ✅ **Tambah Investor** - Form input nama investor dan alamat lengkap
- ✅ **Transaksi Modal** - Input uang masuk dalam USD, otomatis konversi ke IDR
- ✅ **Tracking Saldo** - Monitor total modal, pengeluaran, dan saldo tersedia
- ✅ **Grafik Capital** - Visualisasi akumulasi dana dari setiap investor (Chart.js)
- ✅ **Riwayat Transaksi** - Lihat semua transaksi modal per investor

#### Akses:
```
URL: http://localhost:8080/adf_system/modules/investor/index.php
Menu: Sidebar → Investor
```

#### Database Tables:
- `investors` - Data investor
- `investor_capital_transactions` - Transaksi modal masuk (USD + IDR)
- `investor_balances` - Ringkasan saldo per investor
- `exchange_rates` - Riwayat kurs USD → IDR

---

### 2. MODUL PROJECT

#### Fitur:
- ✅ **Buat Project** - Input nama, kode, budget, lokasi, dan durasi project
- ✅ **Buku Kas Project** - Sistem ledger pengeluaran per project
- ✅ **Kategori Pengeluaran** - 4 kategori tetap:
  - Pembelian Material
  - Pembayaran Truk
  - Tiket Kapal
  - Gaji Tukang
- ✅ **Auto Balance Deduction** - Saldo investor otomatis berkurang saat expense disetujui
- ✅ **Progress Tracking** - Lihat persentase pengeluaran vs budget
- ✅ **Grafik Pengeluaran** - Visualisasi pengeluaran per project (Chart.js)

#### Akses:
```
URL: http://localhost:8080/adf_system/modules/project/index.php
Menu: Sidebar → Project
```

#### Database Tables:
- `projects` - Data project
- `project_expenses` - Ledger pengeluaran project
- `project_expense_categories` - Kategori pengeluaran (4 kategori tetap)
- `project_balances` - Ringkasan pengeluaran per project

---

## 🔄 FLOW OTOMATIS: INTEGRASI SALDO

### Skenario: Investor Modal → Project Expense → Saldo Berkurang

```
1. Investor JONI menginvestasikan $10,000 USD
   ↓
2. Sistem konversi ke IDR: 10,000 × 15,500 = IDR 155,000,000
   ↓
3. Saldo JONI: IDR 155,000,000

4. Project "Rumah A" buat expense Rp 50,000,000 (Pembelian Material)
   ↓
5. Expense di-APPROVE oleh manager
   ↓
6. OTOMATIS: Saldo JONI berkurang → IDR 105,000,000
   ↓
7. Saldo dari POOL INVESTOR (shared untuk semua project)
```

### Kode yang Menghandle:
```php
// ProjectManager.php → approveExpense()
// Ketika expense di-approve, otomatis panggil:
updateAllInvestorBalances()
// Ini akan:
// 1. Hitung total capital dari semua transaksi investor
// 2. Hitung total expenses dari semua project
// 3. Update: remaining_balance = total_capital - total_expenses
```

---

## 💱 KURS USD → IDR AUTOMATION

### API Integration (Option B)
Sistem menggunakan **2-tier approach**:

#### Tier 1: Bank Indonesia API (Priority)
```php
// Endpoint: https://api.bi.go.id/v1/rates/USD/IDR/latest
// Free, no API key needed
// Dijalankan otomatis setiap kali transaksi modal dibuat
```

#### Tier 2: OpenExchangeRates API (Fallback)
```php
// Endpoint: https://openexchangerates.org/api/latest.json
// Requires API key (set di config.php)
define('OPENEXCHANGE_API_KEY', 'your_api_key');
```

#### Tier 3: Manual Override
```php
// Admin bisa set kurs manual dari settings
POST /api/exchange-rate-set-manual.php
```

### Testing Kurs:
```
1. Buka: http://localhost:8080/adf_system/modules/investor/index.php
2. Klik "Tambah Investor" → "Tambah Transaksi Modal"
3. Input amount USD → Otomatis konversi ke IDR
4. Kurs ditampilkan di form (dari database)
```

---

## 📁 FILE STRUCTURE

```
adf_system/
├── database/
│   └── migration-investor-project.sql    ← Database schema
├── includes/
│   ├── InvestorManager.php               ← Investor CRUD + balance logic
│   ├── ProjectManager.php                ← Project CRUD + expense + auto-deduction
│   └── ExchangeRateManager.php           ← Kurs USD→IDR + API integration
├── modules/
│   ├── investor/
│   │   ├── index.php                     ← Dashboard + list investor
│   │   └── investor-detail.php           ← Detail investor + transaksi
│   └── project/
│       ├── index.php                     ← Dashboard + list project
│       └── project-detail.php            ← Detail project + expenses ledger
├── api/
│   ├── investor-create.php               ← Create investor
│   ├── investor-add-capital.php          ← Add modal transaction
│   ├── investor-summary.php              ← Chart data
│   ├── exchange-rate-get.php             ← Get current kurs
│   ├── exchange-rate-convert.php         ← Convert USD→IDR
│   ├── project-create.php                ← Create project
│   ├── project-add-expense.php           ← Add expense (+ auto deduction)
│   └── project-expense-summary.php       ← Chart data
└── install-investor-project.php          ← Installation script
```

---

## 🚀 SETUP & INSTALLATION

### Step 1: Update Sidebar Menu
✅ SUDAH DONE - Menu "Investor" dan "Project" sudah ditambahkan ke sidebar

### Step 2: Jalankan Database Migration

#### Option A: Via Web Browser
```
1. Login sebagai admin
2. Buka: http://localhost:8080/adf_system/install-investor-project.php
3. Klik "Install"
4. Tunggu hingga selesai
```

#### Option B: Via Terminal/Command Line
```bash
cd c:\xampp\htdocs\adf_system
mysql -u root -p narayana_hotel < database/migration-investor-project.sql
```

#### Option C: Via phpMyAdmin
```
1. Buka: http://localhost:8080/phpmyadmin
2. Login
3. Select database: narayana_hotel
4. Klik "Import"
5. Upload file: database/migration-investor-project.sql
6. Klik "Go"
```

### Step 3: Set Initial Exchange Rate

#### Automatic (First Visit)
```
- Sistem akan otomatis fetch dari Bank Indonesia API
- Disimpan ke database exchange_rates
```

#### Manual Set
```php
// Jalankan query SQL:
INSERT INTO exchange_rates (date_of_rate, time_of_rate, usd_to_idr, source, is_current)
VALUES (NOW(), NOW(), 15500, 'manual_input', 1);
```

### Step 4: Test Functionality

1. **Buat Investor**
   - Buka: `/modules/investor/index.php`
   - Klik: "Tambah Investor"
   - Isi: Nama, Alamat, Kontak
   - Klik: "Simpan"

2. **Tambah Modal**
   - Klik investor di list
   - Klik: "Tambah Transaksi Modal"
   - Isi: Jumlah USD
   - Lihat: Otomatis konversi ke IDR
   - Klik: "Simpan"

3. **Buat Project**
   - Buka: `/modules/project/index.php`
   - Klik: "Tambah Project"
   - Isi: Kode, Nama, Budget, Lokasi
   - Klik: "Simpan"

4. **Tambah Expense (Test Auto-Deduction)**
   - Klik project di list
   - Klik: "Tambah Pengeluaran"
   - Isi: Kategori, Jumlah
   - **Status: "Approved"** ← PENTING untuk trigger auto-deduction
   - Klik: "Simpan"
   - **Cek saldo investor** → Harus berkurang!

---

## 📊 DATABASE SCHEMA

### Tabel: `investors`
```sql
- id: Primary Key
- investor_name: VARCHAR(150)
- investor_address: TEXT
- contact_phone: VARCHAR(20)
- email: VARCHAR(100)
- status: ENUM(active, inactive, suspended)
- notes: TEXT
- created_at, updated_at, created_by
```

### Tabel: `investor_capital_transactions`
```sql
- id: Primary Key
- investor_id: FK
- transaction_date: DATE
- amount_usd: DECIMAL(15,2)
- amount_idr: DECIMAL(15,2)  ← Hasil konversi otomatis
- exchange_rate: DECIMAL(10,4) ← Kurs pada saat transaksi
- status: ENUM(pending, confirmed, cancelled)
- created_by: FK
```

### Tabel: `investor_balances`
```sql
- id: Primary Key
- investor_id: FK UNIQUE
- total_capital_idr: DECIMAL(15,2) ← Sum dari capital_transactions
- total_expenses_idr: DECIMAL(15,2) ← Sum dari project_expenses
- remaining_balance_idr: DECIMAL(15,2) ← capital - expenses
- last_updated: TIMESTAMP
```

### Tabel: `projects`
```sql
- id: Primary Key
- project_code: VARCHAR(50) UNIQUE
- project_name: VARCHAR(150)
- location: VARCHAR(200)
- budget_idr: DECIMAL(15,2)
- status: ENUM(planning, ongoing, on_hold, completed)
- start_date, end_date: DATE
```

### Tabel: `project_expenses`
```sql
- id: Primary Key
- project_id: FK
- expense_category_id: FK
- expense_date: DATE
- amount_idr: DECIMAL(15,2)
- status: ENUM(draft, submitted, approved, rejected, paid)
- approved_by: FK
- approved_at: TIMESTAMP
```

### Tabel: `project_expense_categories`
```
1. Pembelian Material
2. Pembayaran Truk
3. Tiket Kapal
4. Gaji Tukang
```

### Tabel: `exchange_rates`
```sql
- id: Primary Key
- date_of_rate: DATE
- usd_to_idr: DECIMAL(10,4)
- source: ENUM(api_bank_indonesia, api_openexchange, manual_input)
- is_current: TINYINT(1) ← Flag untuk rate terbaru
```

---

## 🔐 PERMISSIONS

Kedua modul memerlukan permission:
```php
// Di database user_permissions:
INSERT INTO user_permissions VALUES
  (user_id, 'investor'),
  (user_id, 'project');
```

---

## 📈 CHART.JS VISUALIZATIONS

### 1. Investor Module - Capital Chart
```javascript
// Bar chart: Total modal per investor
type: 'bar'
label: 'Total Modal Masuk (IDR)'
```

### 2. Project Module - Expense Chart
```javascript
// Doughnut chart: Pengeluaran per project
type: 'doughnut'
label: 'Total Pengeluaran (IDR)'
```

### 3. Project Detail - Category Breakdown
```javascript
// Pie chart: Pengeluaran per kategori dalam 1 project
type: 'pie'
categories: [Pembelian Material, Pembayaran Truk, Tiket Kapal, Gaji Tukang]
```

---

## 🔧 API ENDPOINTS

### Investor APIs
```
POST   /api/investor-create.php              - Create investor
POST   /api/investor-add-capital.php         - Add modal transaction
GET    /api/investor-summary.php             - Chart data
```

### Exchange Rate APIs
```
GET    /api/exchange-rate-get.php            - Get current rate
POST   /api/exchange-rate-convert.php        - Convert USD→IDR
POST   /api/exchange-rate-set-manual.php     - Manual rate (admin only)
```

### Project APIs
```
POST   /api/project-create.php               - Create project
POST   /api/project-add-expense.php          - Add expense
GET    /api/project-expense-summary.php      - Chart data
POST   /api/project-approve-expense.php      - Approve expense (trigger deduction)
```

---

## 🐛 TROUBLESHOOTING

### 1. "Kurs tidak tersedia"
```
✓ Jalankan: /api/exchange-rate-get.php
✓ Jika masih error, set manual rate:
  INSERT INTO exchange_rates VALUES
  (DATE_FORMAT(NOW(), '%Y-%m-%d'), TIME(NOW()), 15500, 'manual_input', 1);
```

### 2. Saldo tidak berkurang saat expense dibuat
```
✓ Pastikan status expense = "APPROVED"
✓ Buka browser console (F12) → Check for errors
✓ Cek database: investor_balances → updated?
```

### 3. Koneksi database error
```
✓ Pastikan XAMPP MySQL running
✓ Cek config/database.php → correct credentials
✓ Jalankan: composer require mysql/mysql
```

### 4. Investasi tidak tampil di chart
```
✓ Refresh page (Ctrl+F5)
✓ Buka browser console → Check for JavaScript errors
✓ Cek: jQuery dan Chart.js sudah loaded?
```

---

## 📝 NOTES & FUTURE ENHANCEMENTS

### Current Implementation:
- ✅ Single pool investor saldo (shared semua project)
- ✅ USD → IDR conversion automatic
- ✅ Bank Indonesia API integration
- ✅ Chart.js visualization
- ✅ Permission-based access control

### Potential Enhancements:
- [ ] Multi-currency support (EUR, SGD, etc)
- [ ] Investor payment schedule (piutang/hutang)
- [ ] Project milestone tracking
- [ ] Expense approval workflow
- [ ] PDF report generation
- [ ] Email notifications
- [ ] Bulk import (Excel)
- [ ] Mobile app support

---

## 📞 SUPPORT

Untuk bantuan:
1. Check database schema: `database/migration-investor-project.sql`
2. Review class methods: `includes/InvestorManager.php`, `ProjectManager.php`
3. Test API endpoints dengan Postman
4. Check browser console (F12) untuk JavaScript errors

---

**Terakhir diupdate:** 2026-01-25
**Versi:** 1.0.0
**Status:** ✅ PRODUCTION READY
