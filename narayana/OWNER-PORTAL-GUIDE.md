# 🏢 OWNER PORTAL - Panduan Lengkap

## 📍 Lokasi Login Owner

### **Login Khusus Owner**
```
http://localhost:8080/narayana/owner-login.php
```
- Halaman login khusus untuk Owner/Admin
- Auto-redirect ke Owner Portal setelah login
- Hanya menerima role: **owner** dan **admin**

### **Login Biasa (Multi-role)**
```
http://localhost:8080/narayana/login.php
```
- Login untuk semua role (admin, manager, staff, owner)
- Owner/Admin → redirect ke Owner Portal
- Staff/Manager → redirect ke Business Dashboard

---

## 🔐 Default Credentials

**Admin (All Access)**
- Username: `admin`
- Password: `admin`
- Role: `admin`
- Access: Semua 6 bisnis

**Owner Sita (contoh)**
- Username: `sita`
- Password: `sita123`
- Role: `owner`
- Access: Bisnis yang dipilih saat dibuat

---

## 🎯 Owner Portal Menu

Setelah login, Anda masuk ke:
```
http://localhost:8080/narayana/owner-portal.php
```

### **3 Menu Utama:**

#### 1. 📊 **Dashboard Monitoring**
- **URL:** `modules/owner/dashboard.php`
- **Fitur:**
  - Real-time monitoring SEMUA bisnis sekaligus
  - Dropdown pilih bisnis atau "All Branches"
  - Statistik hari ini, bulan ini, tahun ini
  - Chart income/expense per periode
  - Recent transactions dari semua bisnis
  - Occupancy rate (untuk hotel)

#### 2. 📈 **Health Report**
- **URL:** `modules/owner/health-report.php`
- **Fitur:**
  - Analisa kesehatan finansial bisnis
  - Perbandingan performa antar bisnis
  - Trend analysis
  - Warning system untuk masalah keuangan

#### 3. ⚙️ **Developer Panel** (Admin Only)
- **URL:** `tools/developer-panel.php`
- **Fitur:**
  - Kelola owner users (create, edit, delete)
  - Kelola system users (admin, manager, staff)
  - Business management (add, edit business config)
  - System settings (logo, nama developer, dll)
  - Developer tools (backup, sync, dll)

---

## 🆕 Sistem Baru vs Lama

### ❌ **Sistem Lama (TIDAK DIPAKAI LAGI)**
- Login langsung redirect ke `modules/owner/dashboard.php`
- Tidak ada menu utama
- File test/debug berserakan
- Sulit navigasi

### ✅ **Sistem Baru (SEKARANG)**
- Login → **Owner Portal** (menu utama)
- 3 menu terorganisir
- Navigasi jelas
- User-friendly

---

## 📋 Alur Lengkap Owner

```
1. Buka browser → owner-login.php
2. Login (username: admin, password: admin)
3. Masuk Owner Portal (owner-portal.php)
4. Pilih menu:
   ├─ Dashboard Monitoring → Lihat semua bisnis
   ├─ Health Report → Analisa kesehatan
   └─ Developer Panel → Kelola users & bisnis
5. Logout → logout.php
```

---

## 🔧 Cara Buat Owner Baru

1. **Login sebagai Admin**
   ```
   http://localhost:8080/narayana/tools/simple-login.php
   ```

2. **Buka Developer Panel**
   ```
   http://localhost:8080/narayana/tools/developer-panel.php
   ```

3. **Tab "Owner Management"**
   - Klik tab pertama
   - Isi form:
     - Username: (unique)
     - Password: (min 6 karakter)
     - Full Name: Nama lengkap owner
     - Email: (optional)
   - **Centang bisnis yang boleh dikelola**
   - Klik "Create Owner"

4. **Owner baru bisa login**
   ```
   http://localhost:8080/narayana/owner-login.php
   ```

---

## 🎨 Fitur Dashboard Monitoring

### **Dropdown Select Branch**
- **All Branches** → Aggregate semua bisnis
- **Ben's Cafe** → Data Ben's Cafe saja
- **Hotel** → Data Hotel saja
- **Eat & Meet** → Data Restaurant saja
- **Pabrik Kapal** → Data Manufacture saja
- **Furniture** → Data Furniture saja
- **Karimunjawa Tourism** → Data Tourism saja

### **Real-time Stats**
- Income hari ini, bulan ini, tahun ini
- Expense hari ini, bulan ini, tahun ini
- Net profit/loss
- Growth percentage
- Comparison dengan periode sebelumnya

### **Chart & Grafik**
- Line chart: Trend 7 hari, bulan ini, tahun ini
- Pie chart: Division income breakdown
- Bar chart: Monthly comparison

### **Recent Transactions**
- 10 transaksi terakhir dari bisnis yang dipilih
- Filter by date
- Export to Excel/PDF (coming soon)

---

## 🐛 Troubleshooting

### ❌ Dropdown "Select Branch" kosong
**Solusi:**
1. Pastikan user sudah login
2. Cek business_access di database:
   ```sql
   SELECT id, username, role, business_access 
   FROM users 
   WHERE username = 'admin';
   ```
3. Harus ada: `business_access = [1,2,3,4,5,6]`
4. Jika kosong, update manual:
   ```sql
   UPDATE users 
   SET business_access = '[1,2,3,4,5,6]' 
   WHERE username = 'admin';
   ```

### ❌ Login berhasil tapi redirect ke halaman salah
**Solusi:**
1. Pastikan role = 'owner' atau 'admin'
2. Cek session: http://localhost:8080/narayana/tools/check-session.php
3. Logout dan login ulang

### ❌ Data bisnis tidak muncul
**Solusi:**
1. Pastikan database bisnis ada (6 database)
2. Cek config/businesses.php
3. Test API: http://localhost:8080/narayana/tools/test-owner-branches.html

---

## 📞 Support

Untuk masalah teknis, hubungi developer via:
- Developer Panel → System Settings → Developer WhatsApp

---

**Version:** 2.0 (Multi-Business Owner Portal)  
**Last Updated:** January 23, 2026
