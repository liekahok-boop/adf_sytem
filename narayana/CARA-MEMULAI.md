# 🚀 CARA MEMULAI - 4 Bisnis Owner

## Bisnis Anda:
1. 🏨 **Narayana Hotel** - Hotel management
2. 🍽️ **Eat & Meet** - Restaurant/Cafe  
3. ⛵ **Pabrik Kapal** - Shipyard/Manufacturing
4. 🪑 **Furniture Jepara** - Furniture business

---

## LANGKAH 1: Switch ke Bisnis yang Mau Dikerjakan

### Via Browser (TERMUDAH):
Buka: http://localhost:8080/narayana/tools/business-switcher.php

Klik tombol bisnis yang mau dikerjakan!

### Via Command Line:
```bash
# Hotel
C:\xampp\php\php.exe tools/switch-business.php narayana-hotel

# Restaurant
C:\xampp\php\php.exe tools/switch-business.php eat-meet

# Pabrik Kapal
C:\xampp\php\php.exe tools/switch-business.php pabrik-kapal

# Furniture
C:\xampp\php\php.exe tools/switch-business.php furniture-jepara
```

---

## LANGKAH 2: Login & Develop

1. **Buka:** http://localhost:8080/narayana/
2. **Login:**
   - Username: admin
   - Password: admin123
3. **Mulai coding!**

---

## WORKFLOW DEVELOPMENT

### Scenario: Develop Hotel dulu, terus Restaurant

```bash
# 1. Switch ke Hotel
C:\xampp\php\php.exe tools/switch-business.php narayana-hotel

# 2. Coding hotel features...
# Edit modules, add features, etc

# 3. Commit progress
git add .
git commit -m "Hotel: tambah fitur booking kamar (WIP)"

# 4. Switch ke Restaurant
C:\xampp\php\php.exe tools/switch-business.php eat-meet

# 5. Coding restaurant features...
# Edit modules, add menu system, etc

# 6. Commit progress
git add .
git commit -m "Restaurant: tambah menu management (WIP)"

# 7. Balik ke Hotel lagi kapan saja!
C:\xampp\php\php.exe tools/switch-business.php narayana-hotel
```

---

## FEATURES PER BISNIS

### 🏨 Narayana Hotel
- **Warna:** Biru (#4338ca)
- **Modules:** Frontdesk, Rooms, Reservations, Cashbook, Reports
- **Cashbook Kolom:** Room #, Guest Name
- **Database:** narayana (existing)

### 🍽️ Eat & Meet Restaurant
- **Warna:** Orange (#f59e0b)
- **Modules:** Cashbook, Reports (Menu, Orders, Kitchen - nanti tambah)
- **Cashbook Kolom:** Table #, Order #, Server Name
- **Database:** narayana (shared)

### ⛵ Pabrik Kapal
- **Warna:** Cyan (#0891b2)
- **Modules:** Cashbook, Reports (Projects, Production - nanti tambah)
- **Cashbook Kolom:** Project Code, Ship Name, Supplier/Client
- **Database:** narayana (shared)

### 🪑 Furniture Jepara  
- **Warna:** Brown (#92400e)
- **Modules:** Cashbook, Reports (Products, Orders - nanti tambah)
- **Cashbook Kolom:** Order #, Product Name, Customer
- **Database:** narayana (shared)

---

## TIPS DEVELOPMENT

### Cek Bisnis Aktif
```bash
type config\active-business.php
```

### List Semua Bisnis
```bash
C:\xampp\php\php.exe tools/switch-business.php
```

### Lihat Semua Config
```bash
dir config\businesses\
```

---

## FAQ

**Q: Data cashbook bisa pisah per bisnis?**
A: Ya! Meski 1 database, data bisa di-filter by branch atau nanti bisa add `business_type` column.

**Q: Bisa pindah-pindah saat development?**
A: Bisa! Switch kapan aja, progress tersimpan di git.

**Q: Kalau mau add module khusus per bisnis?**  
A: Buat folder modules/restaurant/, modules/manufacturing/, dll. Enabled via config.

**Q: Warna dashboard beda?**
A: Ya! Setiap bisnis punya warna tema sendiri di config.

---

## NEXT STEPS

1. ✅ Switch ke bisnis pertama (Hotel atau yang lain)
2. ✅ Login ke system
3. ✅ Mulai develop features
4. ✅ Commit progress
5. ✅ Switch ke bisnis lain
6. ✅ Repeat!

**Happy Coding!** 🎉
