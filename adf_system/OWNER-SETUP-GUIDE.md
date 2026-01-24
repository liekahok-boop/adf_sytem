# 🚀 OWNER MONITORING - Setup dari Awal (Step by Step)

## 📋 Tahap 1: DIAGNOSTIC - CEK MASALAH

### Langkah 1.1: Buka Diagnostic Tool
```
http://localhost:8080/narayana/tools/diagnostic-owner.php
```

**Tool ini akan otomatis cek:**
- ✅ Session user (login status)
- ✅ Database user (business_access)  
- ✅ Config businesses.php
- ✅ API owner-branches.php
- ✅ Semua API endpoints owner
- ✅ Memberikan solusi otomatis

### Langkah 1.2: Lihat Hasil Diagnostic
Tool akan menampilkan dengan warna:
- 🟢 **HIJAU** = OK, tidak ada masalah
- 🔴 **MERAH** = ERROR, harus diperbaiki
- 🟠 **ORANGE** = WARNING, perlu perhatian

---

## 📋 Tahap 2: PERBAIKAN BERDASARKAN DIAGNOSTIC

### Jika "User NOT logged in"

**Solusi:**
```
1. Logout dulu: http://localhost:8080/narayana/logout.php
2. Login: http://localhost:8080/narayana/owner-login.php
   Username: admin
   Password: admin
3. Refresh diagnostic tool
```

### Jika "Business Access kosong"

**Solusi A - Via MySQL:**
```sql
-- Update user admin
UPDATE users 
SET business_access = '[1,2,3,4,5,6]' 
WHERE username = 'admin';

-- Cek hasil
SELECT id, username, role, business_access 
FROM users 
WHERE username = 'admin';
```

**Solusi B - Via Tool:**
```
1. Buka: http://localhost:8080/narayana/tools/fix-business-access.php
2. Klik "Fix Admin Business Access"
3. Login ulang
```

### Jika "API owner-branches.php error"

**Cek error message di diagnostic tool, lalu:**
1. Copy error message
2. Buka browser console (F12) → Network tab
3. Refresh page
4. Lihat response API owner-branches.php
5. Screenshot dan laporkan error

---

## 📋 Tahap 3: SETUP USER OWNER

### Opsi A: Pakai User Admin (Recommended untuk Test)

User `admin` sudah ada dan punya all-access. Cukup:
```
1. Login: http://localhost:8080/narayana/owner-login.php
   Username: admin
   Password: admin
2. Langsung bisa monitoring semua bisnis
```

### Opsi B: Buat User Owner Baru

```
1. Login sebagai admin:
   http://localhost:8080/narayana/tools/simple-login.php
   
2. Buka Developer Panel:
   http://localhost:8080/narayana/tools/developer-panel.php
   
3. Tab "Owner Management"
   
4. Isi Form:
   Username: sita
   Password: sita123
   Full Name: Bu Sita
   Email: sita@example.com
   
5. Centang semua 6 bisnis:
   ✅ Ben's Cafe
   ✅ Hotel
   ✅ Eat & Meet Restaurant
   ✅ Pabrik Kapal
   ✅ Furniture
   ✅ Karimunjawa Tourism
   
6. Klik "Create Owner"
   
7. Login dengan user baru:
   http://localhost:8080/narayana/owner-login.php
   Username: sita
   Password: sita123
```

---

## 📋 Tahap 4: TESTING DASHBOARD

### Test Lengkap Dashboard Owner

**4.1. Login**
```
http://localhost:8080/narayana/owner-login.php
Username: admin
Password: admin
```

**4.2. Masuk Owner Portal**
```
http://localhost:8080/narayana/owner-portal.php
```
Harus muncul 3 menu:
- 📊 Dashboard Monitoring
- 📈 Health Report  
- ⚙️ Developer Panel

**4.3. Klik "Dashboard Monitoring"**
```
http://localhost:8080/narayana/modules/owner/dashboard.php
```

**4.4. Cek Dropdown "Select Branch"**
Harus muncul:
- All Branches
- Ben's Cafe - cafe
- Hotel - hotel
- Eat & Meet Restaurant - restaurant
- Pabrik Kapal - manufacture
- Furniture - furniture
- Karimunjawa Tourism - tourism

**4.5. Test Pilih Branch**
- Pilih "Ben's Cafe"
- Data harus berubah hanya showing Ben's Cafe
- Pilih "All Branches"  
- Data harus aggregate semua bisnis

---

## 📋 Tahap 5: TROUBLESHOOTING UMUM

### Masalah 1: Dropdown Bisnis Kosong

**Cek 1: Session**
```
http://localhost:8080/narayana/tools/check-session.php
```
Harus ada: `business_access: [1,2,3,4,5,6]`

**Cek 2: API**
```
http://localhost:8080/narayana/api/owner-branches.php
```
Harus return JSON dengan branches array

**Cek 3: Browser Console**
```
1. Tekan F12
2. Tab "Console"
3. Refresh page
4. Lihat error merah
5. Screenshot dan laporkan
```

### Masalah 2: Data Tidak Muncul

**Cek Database:**
```sql
-- Cek database bisnis ada
SHOW DATABASES LIKE 'narayana_%';

-- Harus muncul:
-- narayana_benscafe
-- narayana_hotel
-- narayana_eatmeet
-- narayana_pabrikkapal
-- narayana_furniture
-- narayana_karimunjawa

-- Cek data cashbook
SELECT COUNT(*) FROM narayana_benscafe.cash_book;
```

Jika database kosong, insert sample data dulu.

### Masalah 3: Login Gagal

**Cek Password:**
```sql
SELECT id, username, role, 
       password, 
       MD5('admin') as expected_hash 
FROM users 
WHERE username = 'admin';
```

**Fix Password:**
```sql
UPDATE users 
SET password = MD5('admin') 
WHERE username = 'admin';
```

### Masalah 4: Redirect Error

**Cek file:**
```
owner-login.php → harus redirect ke owner-portal.php
owner-portal.php → harus ada 3 menu
modules/owner/dashboard.php → harus load API
```

---

## 📋 Tahap 6: VERIFIKASI AKHIR

### Checklist Lengkap:

```
□ 1. Login berhasil (owner-login.php)
□ 2. Masuk Owner Portal (owner-portal.php)
□ 3. Menu muncul 3 items
□ 4. Klik Dashboard Monitoring
□ 5. Dropdown "Select Branch" muncul 7 options
□ 6. Pilih "All Branches" → data aggregate
□ 7. Pilih "Ben's Cafe" → data spesifik
□ 8. Chart muncul (line chart)
□ 9. Stats cards muncul (Today, Month, Year)
□ 10. Recent transactions muncul

JIKA SEMUA ✅ = SISTEM OK!
JIKA ADA ❌ = JALANKAN DIAGNOSTIC TOOL
```

---

## 🛠️ TOOLS YANG TERSEDIA

### 1. Diagnostic Tool (Utama)
```
http://localhost:8080/narayana/tools/diagnostic-owner.php
```
Cek semua fungsi otomatis + solusi

### 2. Check Session
```
http://localhost:8080/narayana/tools/check-session.php
```
Lihat isi session user

### 3. Test API Branches
```
http://localhost:8080/narayana/tools/test-owner-branches.html
```
Test API owner-branches.php

### 4. Create Owner
```
http://localhost:8080/narayana/tools/test-api-create.html
```
Buat user owner baru

### 5. Developer Panel
```
http://localhost:8080/narayana/tools/developer-panel.php
```
Manage users & bisnis

---

## 🚨 JIKA MASIH BERMASALAH

### Langkah Terakhir:

1. **Jalankan Diagnostic Tool**
   ```
   http://localhost:8080/narayana/tools/diagnostic-owner.php
   ```

2. **Screenshot Hasil Diagnostic**
   - Semua section merah
   - Error message

3. **Cek Browser Console**
   - F12 → Console tab
   - Screenshot error merah

4. **Cek Network Tab**
   - F12 → Network tab
   - Refresh page
   - Cek response API owner-branches.php
   - Screenshot

5. **Berikan Info:**
   - Screenshot diagnostic
   - Screenshot browser console
   - Screenshot network response
   - Jelaskan langkah yang sudah dilakukan

---

## 📞 QUICK REFERENCE

**Login Owner:**
```
http://localhost:8080/narayana/owner-login.php
admin / admin
```

**Owner Portal:**
```
http://localhost:8080/narayana/owner-portal.php
```

**Dashboard:**
```
http://localhost:8080/narayana/modules/owner/dashboard.php
```

**Diagnostic:**
```
http://localhost:8080/narayana/tools/diagnostic-owner.php
```

---

**Version:** 2.0  
**Last Updated:** January 23, 2026  
**Status:** Ready for Testing
