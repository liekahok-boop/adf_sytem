# 🌅 End Shift Feature - Setup & Implementation Guide

## 📋 Ringkasan Fitur

End Shift adalah fitur yang memungkinkan staff untuk:

✅ **Logout dengan laporan otomatis**
- Menampilkan ringkasan transaksi hari ini (income, expense, balance)
- Membuka modal dengan data yang comprehensive

✅ **Lihat semua PO hari ini**
- Menampilkan gallery gambar nota dari semua PO yang dibuat hari ini
- Support multiple images per PO
- Responsive design untuk mobile dan desktop

✅ **Kirim laporan ke WhatsApp**
- Satu klik untuk membuka WhatsApp dengan pesan siap kirim
- Format pesan professional dan comprehensive
- Terintegrasi dengan contact GM/Admin dari settings
- Automatic shift log untuk audit trail

---

## 🚀 Step-by-Step Setup

### **Langkah 1: Jalankan Setup Installer**

1. Buka browser dan akses:
```
http://localhost:8080/adf_system/setup-end-shift.php
```

2. Setup wizard akan:
   - ✓ Create `shift_logs` table
   - ✓ Create `po_images` table  
   - ✓ Add columns ke `business_settings` dan `users`

3. Tunggu hingga semua step success (hijau)

### **Langkah 2: Configure End Shift Settings**

1. Login sebagai **Admin** ke aplikasi
2. Pergi ke **Settings** → **End Shift Configuration**
3. Isi field berikut:

   | Field | Contoh | Keterangan |
   |-------|--------|-----------|
   | WhatsApp Number | +62812345678 | Nomor WA untuk menerima report (format dengan +62 atau 62) |
   | Admin/GM Phone | +62812345678 | Nomor telepon admin untuk reference |
   | Admin/GM Email | admin@hotel.com | Email admin untuk komunikasi |

4. Klik **💾 Save Settings**

### **Langkah 3: Verifikasi Instalasi**

1. Logout dari akun admin
2. Login sebagai staff/user biasa
3. Lihat tombol **🌅 End Shift** di top-right header
4. Test dengan klik tombol untuk verify semuanya berfungsi

---

## 🎯 Cara Penggunaan

### **Untuk Staff:**

```
1. Shift sudah berakhir? Klik tombol 🌅 "End Shift" di top-right
        ↓
2. Modal terbuka menampilkan:
   - Daily report (income, expense, balance)
   - Gallery PO hari ini dengan gambar
        ↓
3. Pilih salah satu:
   a) 📱 "Kirim ke WhatsApp GM/Admin" 
      → WhatsApp membuka dengan pesan siap kirim
      → Sesuaikan message jika perlu
      → Klik Send
   
   b) ✓ "Logout & Selesai"
      → Langsung logout tanpa kirim WhatsApp
        ↓
4. Report tersimpan di database (shift_logs) untuk audit
```

### **Untuk Admin (Settings):**

```
1. Settings → End Shift Configuration
2. Configure WhatsApp number penerima report
3. Lihat shift logs di database untuk tracking
4. Optional: Add to settings menu untuk easy access
```

---

## 📁 File Structure Created

```
adf_system/
│
├── setup-end-shift.php
│   └── Setup wizard untuk initial installation
│
├── api/
│   ├── end-shift.php
│   │   └── Fetch daily report & PO data
│   └── send-whatsapp-report.php
│       └── Generate WhatsApp message
│
├── assets/js/
│   └── end-shift.js
│       └── Modal UI & WhatsApp integration
│
├── modules/settings/
│   └── end-shift.php
│       └── Admin configuration page
│
├── database/
│   └── migration-shift-logs.sql
│       └── Database migration file
│
├── docs/
│   └── END-SHIFT-FEATURE.md
│       └── Complete technical documentation
│
└── includes/
    ├── header.php (MODIFIED)
    │   └── Added End Shift button
    └── footer.php (MODIFIED)
        └── Added end-shift.js script
```

---

## 📊 Database Tables Created

### **shift_logs** - Tracking semua End Shift actions
```sql
CREATE TABLE shift_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,                    -- User yang end shift
    action VARCHAR(50),             -- Action type (end_shift_view, end_shift_wa_send)
    data JSON,                      -- JSON data (report details, timestamp)
    created_at TIMESTAMP,           -- When action occurred
    updated_at TIMESTAMP
);
```

### **po_images** - Reference gambar untuk PO
```sql
CREATE TABLE po_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_id INT,                      -- Reference ke purchase_orders
    image_path VARCHAR(255),        -- Path ke gambar
    is_primary BOOLEAN,             -- Is main image
    created_at TIMESTAMP
);
```

### **Modified Columns**
```sql
ALTER TABLE business_settings ADD COLUMN whatsapp_number VARCHAR(20);
ALTER TABLE users ADD COLUMN phone VARCHAR(20);
```

---

## 🔐 Permissions & Access

| Role | Can Use | Can Configure |
|------|---------|----------------|
| Admin | ✓ Yes | ✓ Yes |
| GM/Manager | ✓ Yes | ✗ No (Optional) |
| Staff | ✓ Yes | ✗ No |
| Viewer | ✗ No | ✗ No |

---

## 💬 WhatsApp Message Format

Contoh message yang dikirim:

```
*📊 LAPORAN END SHIFT - Narayana Hotel*
📅 25 Jan 2024 17:30
👤 Shift Officer: John Doe

*💰 RINGKASAN TRANSAKSI:*
✅ Total Pemasukan: Rp 5.000.000
❌ Total Pengeluaran: Rp 2.000.000
📈 Saldo Bersih: Rp 3.000.000
🔢 Jumlah Transaksi: 15

*📦 PO HARI INI:*
🔗 Jumlah PO: 3
📸 Lihat detail PO di dashboard

_Laporan otomatis dari sistem_
```

---

## 🔌 API Endpoints

### **GET** `/api/end-shift.php`
Fetch daily report dan PO data

```bash
curl http://localhost:8080/adf_system/api/end-shift.php \
  -H "Cookie: PHPSESSID=YOUR_SESSION"
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "user": { ... },
    "business": { ... },
    "daily_report": { 
      "date": "2024-01-25",
      "total_income": 5000000,
      "total_expense": 2000000,
      "net_balance": 3000000,
      "transaction_count": 15
    },
    "pos_data": { 
      "count": 3,
      "list": [ ... ]
    }
  }
}
```

### **POST** `/api/send-whatsapp-report.php`
Generate WhatsApp URL dan message

```bash
curl -X POST http://localhost:8080/adf_system/api/send-whatsapp-report.php \
  -H "Content-Type: application/json" \
  -d '{
    "total_income": 5000000,
    "total_expense": 2000000,
    "net_balance": 3000000,
    "user_name": "John Doe",
    "transaction_count": 15,
    "po_count": 3,
    "business_name": "Narayana Hotel",
    "admin_phone": "+62812345678"
  }'
```

**Response:**
```json
{
  "status": "success",
  "whatsapp_url": "https://wa.me/62812345678?text=...",
  "message": "Formatted WhatsApp message",
  "phone": "+62812345678"
}
```

---

## 🐛 Troubleshooting

### **Problem: Tombol End Shift tidak muncul**

**Solutions:**
1. Clear browser cache: Ctrl+Shift+Delete
2. Login ulang ke aplikasi
3. Verify user punya permission dashboard
4. Check browser console untuk JavaScript errors

### **Problem: WhatsApp tidak membuka**

**Solutions:**
1. Allow pop-ups untuk domain ini
2. Verify nomor format: gunakan +62... atau 62...
3. Check WhatsApp Web sudah login
4. Try different browser

### **Problem: Data transaksi tidak muncul**

**Solutions:**
1. Pastikan ada transaksi hari ini di Cashbook
2. Check tanggal sistem sesuai
3. Verify user punya akses ke dashboard
4. Check SQL query di browser console (F12)

### **Problem: Database migration error**

**Solutions:**
1. Manual run SQL dari `database/migration-shift-logs.sql`
2. Check MySQL user punya ALTER TABLE permission
3. Verify database exists dan connection working
4. Run setup installer lagi

---

## 🔄 Maintenance & Monitoring

### **Regular Tasks:**

1. **Monitor shift_logs table** - Keep size manageable
   ```sql
   -- Archive old logs (optional)
   DELETE FROM shift_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
   ```

2. **Cleanup PO images** - Remove orphaned records
   ```sql
   -- Find unused image references
   SELECT * FROM po_images WHERE po_id NOT IN (SELECT id FROM purchase_orders);
   ```

3. **Backup WhatsApp numbers** - Keep config safe
   ```sql
   -- Backup settings
   SELECT * FROM business_settings WHERE whatsapp_number IS NOT NULL;
   ```

---

## 📞 Support & Contact

Untuk bantuan lebih lanjut:

1. **Documentation:** [END-SHIFT-FEATURE.md](../docs/END-SHIFT-FEATURE.md)
2. **Settings Page:** [/modules/settings/end-shift.php](../modules/settings/end-shift.php)
3. **Setup Wizard:** [/setup-end-shift.php](../setup-end-shift.php)

---

## ✨ Features Roadmap

### **Current Version (v1.0)**
- ✓ Daily report display
- ✓ PO images gallery
- ✓ WhatsApp integration
- ✓ Shift logging

### **Planned Features (v2.0)**
- [ ] Email report
- [ ] SMS notification
- [ ] PDF attachment
- [ ] WhatsApp Business API
- [ ] Telegram bot support
- [ ] Slack integration
- [ ] Custom templates

---

**Version:** 1.0.0  
**Last Updated:** January 25, 2024  
**Status:** Production Ready ✓
