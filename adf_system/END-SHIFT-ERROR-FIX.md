# 🔧 END SHIFT ERROR FIX GUIDE

## ❌ Error: "Gagal mengambil data laporan"

Jika Anda melihat error ini saat click End Shift button, ikuti langkah berikut:

---

## 📋 STEP 1: Check Database Connection

### Test Diagnostics API:
1. **Login ke aplikasi**
2. **Buka URL ini di browser:**
   ```
   http://localhost:8080/adf_system/api/end-shift-diagnostics.php
   ```

3. **Lihat response JSON - harus menunjukkan semua tables OK**

### Example Response (GOOD):
```json
{
  "status": "ok",
  "checks": [
    {"name": "Database Connection", "status": "OK"},
    {"name": "Users Table", "status": "OK", "count": 5},
    {"name": "Cash Book Table", "status": "OK", "count": 15},
    {"name": "Purchase Orders Table", "status": "OK", "count": 8},
    {"name": "Divisions Table", "status": "OK", "count": 3},
    {"name": "Business Settings Table", "status": "OK", "count": 1},
    {"name": "Today Transactions", "status": "OK", "count": 2},
    {"name": "Today POs", "status": "OK", "count": 1}
  ],
  "errors": []
}
```

### Example Response (BAD):
```json
{
  "status": "error",
  "errors": [
    "Cash Book Table: Table 'adf_system.cash_book' doesn't exist"
  ]
}
```

---

## ✅ STEP 2: Run Setup Wizard

Jika ada table yang missing atau error, jalankan setup:

1. **Buka:** `http://localhost:8080/adf_system/setup-end-shift.php`
2. **Tunggu semua step selesai** (harus semua hijau)
3. **Jika ada error, lihat pesan errornya**

---

## 🔍 STEP 3: Check Browser Console

Untuk debug lebih detail:

1. **Di halaman aplikasi, tekan `F12`** (buka Developer Tools)
2. **Click tab "Console"**
3. **Click tombol "End Shift"** lagi
4. **Lihat error message di console**

### Typical Errors:

#### ❌ Error: "Undefined function"
```
Fatal error: Undefined function 'fetchAll'...
```
**Solution:** Check `config/database.php` exists dan benar

#### ❌ Error: "Table doesn't exist"
```
Table 'adf_system.cash_book' doesn't exist
```
**Solution:** Database tables belum dibuat, run setup wizard

#### ❌ Error: "Column doesn't exist"
```
Unknown column 'd.division_name' in 'on clause'
```
**Solution:** Database schema outdated, run migration SQL manual

---

## 🛠️ STEP 4: Manual Database Fix

Jika setup wizard tidak berhasil, jalankan SQL manual:

### Via phpMyAdmin:
1. Buka `http://localhost/phpmyadmin`
2. Pilih database `adf_system`
3. Click **SQL** tab
4. Copy-paste SQL ini:

```sql
-- Create necessary tables
CREATE TABLE IF NOT EXISTS cash_book (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    division_id INT,
    transaction_type VARCHAR(20),
    amount DECIMAL(12,2),
    description TEXT,
    transaction_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS purchase_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_number VARCHAR(50) UNIQUE,
    supplier_id INT,
    total_amount DECIMAL(12,2),
    status VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
);

CREATE TABLE IF NOT EXISTS divisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    division_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS business_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_id INT,
    whatsapp_number VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add columns if not exist
ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20);
ALTER TABLE business_settings ADD COLUMN IF NOT EXISTS whatsapp_number VARCHAR(20);

-- Create shift logs
CREATE TABLE IF NOT EXISTS shift_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50),
    data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create PO images
CREATE TABLE IF NOT EXISTS po_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_id INT,
    image_path VARCHAR(255),
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

5. Click **Execute**
6. Refresh page dan test lagi

---

## 🔎 STEP 5: Check Data Existence

Pastikan ada sample data untuk hari ini:

1. **Buka**: http://localhost:8080/adf_system/modules/cashbook/
2. **Pastikan ada transaksi hari ini**
3. Jika tidak ada, tambah satu transaksi baru

---

## 📝 STEP 6: Enable Debug Mode

Untuk debugging lebih detail, enable error logging:

### Edit `api/end-shift.php`:

Uncomment line ini (jika belum ada):
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

Kemudian check error di:
- Browser console (F12)
- Server error log: `xampp/apache/logs/error.log`

---

## 🆘 Still Having Issues?

### Check This:
1. ✅ Database connection working?
2. ✅ All tables created?
3. ✅ User logged in?
4. ✅ User memiliki access ke dashboard?
5. ✅ Nomor business_id valid?

### Get More Info:

**Terminal Command:**
```bash
cd c:\xampp\htdocs\adf_system
```

**Check MySQL:**
```bash
mysql -u root -p adf_system -e "SHOW TABLES;"
mysql -u root -p adf_system -e "DESCRIBE cash_book;"
mysql -u root -p adf_system -e "SELECT COUNT(*) FROM cash_book WHERE DATE(transaction_date) = CURDATE();"
```

### Contact Admin:
Berikan info ini ke developer:
- Error message dari console (F12)
- Output dari diagnostics API (`api/end-shift-diagnostics.php`)
- Server error log

---

## ✨ Expected Behavior After Fix

✅ Click "End Shift" button di header  
✅ Modal terbuka dengan loading spinner  
✅ Laporan harian muncul dengan data:
- Total income, expense, balance
- List transactions
- PO images gallery
✅ Tombol "Kirim ke WhatsApp" bekerja  
✅ Tombol "Logout" bekerja  
✅ Shift log tersimpan di database  

---

## 🎯 Quick Checklist

- [ ] Database connection OK (test diagnostics API)
- [ ] All tables exist
- [ ] Sample transaction created for today
- [ ] User has dashboard access
- [ ] WhatsApp number configured in settings
- [ ] No errors in browser console (F12)
- [ ] End Shift button visible
- [ ] Modal opens without error
- [ ] Report shows correct data
- [ ] WhatsApp integration works

---

**Jika sudah follow semua steps tapi masih error, hubungi admin dengan:**
- Screenshot error
- Hasil dari `/api/end-shift-diagnostics.php`
- Browser console output (F12)
