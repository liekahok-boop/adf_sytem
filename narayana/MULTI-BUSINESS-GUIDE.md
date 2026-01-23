# 🏢 Multi-Business System dengan Business Access Control

## Overview

Sistem Narayana sekarang mendukung **multiple businesses dengan database terpisah** untuk setiap bisnis. Setiap user dapat diberikan akses ke satu atau lebih bisnis menggunakan sistem **business access control**.

## 🗄️ Struktur Database

### Database Terpisah Per Bisnis

Setiap bisnis memiliki database MySQL terpisah:

1. **narayana_benscafe** - Ben's Cafe
2. **narayana_hotel** - Hotel
3. **narayana_eatmeet** - Eat & Meet Restaurant  
4. **narayana_pabrikkapal** - Pabrik Kapal
5. **narayana_furniture** - Furniture
6. **narayana_karimunjawa** - Karimunjawa Tourism

### Mengapa Database Terpisah?

✅ **Isolasi Data** - Data setiap bisnis benar-benar terpisah
✅ **Keamanan** - Tidak ada risiko data leak antar bisnis
✅ **Backup Independen** - Backup per bisnis tanpa mempengaruhi yang lain
✅ **Skalabilitas** - Mudah menambah bisnis baru
✅ **Performa** - Query lebih cepat karena data lebih kecil per database

## 👤 Sistem Business Access Control

### Field `business_access` di Tabel `users`

Setiap user memiliki field `business_access` (TEXT/JSON) yang menyimpan array ID bisnis yang dapat diakses:

```sql
ALTER TABLE users ADD COLUMN business_access TEXT DEFAULT NULL;
```

**Contoh isi field:**
```json
[1, 2, 5]  -- User dapat akses bisnis ID 1 (Ben's Cafe), 2 (Hotel), 5 (Furniture)
```

### Fungsi Helper: `getUserAvailableBusinesses()`

File: `includes/business_access.php`

```php
function getUserAvailableBusinesses($userId = null) {
    // Returns array of businesses user can access
    // Format: [
    //   ['id' => 1, 'name' => 'Ben\'s Cafe', 'database' => 'narayana_benscafe'],
    //   ['id' => 2, 'name' => 'Hotel', 'database' => 'narayana_hotel']
    // ]
}
```

### Role-Based Access

- **Admin**: Akses ke SEMUA bisnis (tidak perlu business_access)
- **Owner**: Akses berdasarkan business_access field
- **Staff**: Akses berdasarkan business_access field

## 🎯 Penggunaan untuk Developer

### 1. Cek Bisnis yang Dapat Diakses User

```php
require_once 'includes/business_access.php';

$userId = $_SESSION['user_id'];
$businesses = getUserAvailableBusinesses($userId);

// Loop through accessible businesses
foreach ($businesses as $business) {
    echo "Business: " . $business['name'];
    echo "Database: " . $business['database'];
}
```

### 2. Query Data dari Business Database Tertentu

```php
require_once 'config/database.php';

// Connect to specific business database
$businessDb = new Database('narayana_hotel');

// Query data
$transactions = $businessDb->fetchAll(
    "SELECT * FROM cash_book WHERE transaction_date = ?",
    [date('Y-m-d')]
);
```

### 3. Aggregate Data dari Multiple Businesses

```php
require_once 'includes/business_access.php';

$totalIncome = 0;

// Get accessible businesses
$businesses = getUserAvailableBusinesses($_SESSION['user_id']);

// Loop and aggregate
foreach ($businesses as $business) {
    $db = new Database($business['database']);
    
    $result = $db->fetchOne(
        "SELECT SUM(amount) as total FROM cash_book 
         WHERE transaction_type = 'income' AND transaction_date = ?",
        [date('Y-m-d')]
    );
    
    $totalIncome += (float)$result['total'];
}

echo "Total Income from All Businesses: Rp " . number_format($totalIncome);
```

## 🔧 Tools & Maintenance

### 1. Sync Tables Across All Databases

File: `tools/sync-all-tables.php`

Menyalin semua tabel dari database utama ke semua business databases:

```bash
# Jalankan di browser
http://localhost/narayana/tools/sync-all-tables.php
```

**Kapan digunakan:**
- Setelah menambah tabel baru di main database
- Setelah mengubah struktur tabel
- Ketika ada missing tables di business database

### 2. Create Purchase Orders Tables

File: `tools/create-purchase-orders-tables.php`

Membuat tabel purchase_orders_header dan purchase_orders_detail di semua database:

```bash
http://localhost/narayana/tools/create-purchase-orders-tables.php
```

## 👥 Manage User Business Access (untuk Owner)

### Via Web Interface

1. Login sebagai Owner
2. Buka Owner Dashboard
3. Klik icon **Users** di header
4. Pilih bisnis untuk setiap user dengan checkbox
5. Perubahan otomatis disimpan

URL: `http://localhost/narayana/modules/owner/manage-user-access.php`

### Via SQL (Manual)

```sql
-- Berikan akses user ID 5 ke bisnis 1, 2, dan 3
UPDATE users 
SET business_access = '[1,2,3]' 
WHERE id = 5;

-- Berikan akses ke semua bisnis (untuk admin)
UPDATE users 
SET business_access = '[1,2,3,4,5,6]' 
WHERE id = 1;

-- Hapus akses (user tidak bisa akses bisnis apapun)
UPDATE users 
SET business_access = NULL 
WHERE id = 10;
```

## 📊 Owner Monitoring Dashboard

Dashboard owner otomatis menampilkan data dari semua bisnis yang accessible.

### Fitur Dashboard:

1. **Branch Selector** - Pilih bisnis spesifik atau "All Branches"
2. **Today Stats** - Income/expense hari ini (aggregated)
3. **Month Stats** - Income/expense bulan ini dengan perbandingan bulan lalu
4. **Occupancy** - Status kamar dari semua hotel businesses
5. **Chart** - Trend 7 hari / 30 hari / 12 bulan
6. **Recent Transactions** - Transaksi terbaru dari semua businesses

### API Endpoints yang Digunakan:

- `api/owner-branches.php` - List bisnis accessible
- `api/owner-stats.php` - Aggregate statistics
- `api/owner-occupancy.php` - Aggregate room occupancy
- `api/owner-chart-data.php` - Chart data aggregated
- `api/owner-recent-transactions.php` - Recent transactions dari semua bisnis

## 🚀 Menambah Bisnis Baru

### Langkah 1: Buat Database Baru

```sql
CREATE DATABASE narayana_newbusiness;
```

### Langkah 2: Tambahkan ke Config

Edit `config/businesses.php`:

```php
$BUSINESSES = [
    // ... existing businesses ...
    [
        'id' => 7,
        'name' => 'New Business Name',
        'database' => 'narayana_newbusiness',
        'type' => 'retail', // hotel, restaurant, manufacture, retail, tourism
        'active' => true
    ]
];
```

### Langkah 3: Sync Tables

Jalankan `tools/sync-all-tables.php` untuk copy semua tabel ke database baru.

### Langkah 4: Grant User Access

Via UI atau SQL:

```sql
-- Tambahkan ID 7 ke business_access user
UPDATE users 
SET business_access = JSON_ARRAY_APPEND(business_access, '$', 7)
WHERE id = 1;
```

## ⚠️ Best Practices

### DO ✅

1. **Selalu gunakan `getUserAvailableBusinesses()`** sebelum query business data
2. **Validate business access** sebelum mengizinkan user akses data
3. **Run sync-all-tables.php** setelah perubahan struktur database
4. **Backup tiap database** secara independen
5. **Test dengan user yang berbeda role** untuk validasi access control

### DON'T ❌

1. **Jangan hardcode database name** di query
2. **Jangan skip business access check** untuk owner/staff
3. **Jangan modify business_access** tanpa validasi
4. **Jangan query database** yang tidak ada di business_access
5. **Jangan lupa sync tables** ke semua databases setelah perubahan

## 🐛 Troubleshooting

### Problem: User tidak bisa lihat data bisnis

**Solution:**
1. Cek field `business_access` di tabel users
2. Pastikan ID bisnis ada di array business_access
3. Cek apakah database bisnis exist
4. Cek apakah tabel ada di database bisnis (run sync-all-tables.php)

### Problem: Missing table in business database

**Solution:**
```bash
# Run sync tool
http://localhost/narayana/tools/sync-all-tables.php
```

### Problem: Purchase orders error

**Solution:**
```bash
# Create PO tables in all databases
http://localhost/narayana/tools/create-purchase-orders-tables.php
```

### Problem: Dashboard tidak load data

**Solution:**
1. Cek console browser untuk error
2. Cek network tab untuk failed API calls
3. Validasi business_access field tidak NULL
4. Test API endpoint langsung di browser

## 📝 Change Log

### Version 2.0 - Multi-Business with Access Control

- ✅ Separate database per business (6 databases)
- ✅ business_access field in users table
- ✅ getUserAvailableBusinesses() helper function
- ✅ Owner dashboard with multi-business aggregation
- ✅ User management UI for business access
- ✅ Auto-sync tools for table management
- ✅ Business access validation middleware
- ✅ API endpoints updated for multi-business support

## 📞 Support

Jika ada pertanyaan atau issue:

1. Cek dokumentasi ini terlebih dahulu
2. Cek file `PORT-8080-GUIDE.md` untuk setup
3. Cek `INSTALL.md` untuk installation guide
4. Contact: Developer Team

---

**Last Updated:** <?= date('Y-m-d H:i:s') ?>

**System Version:** 2.0 - Multi-Business Edition
