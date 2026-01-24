# Quick Start Guide - Multi Business Setup

## 🎯 Bisnis Owner

Owner memiliki 4 bisnis berbeda:

1. **🏨 Narayana Hotel** - Hotel management
2. **🍽️ Eat & Meet** - Restaurant/Cafe
3. **⛵ Pabrik Kapal Indonesia** - Shipyard/Manufacturing
4. **🪑 Furniture Jepara** - Furniture manufacturing

## 📋 Step-by-Step Setup

### Step 1: Setup Databases untuk Semua Bisnis

Buka terminal di folder `narayana`:

```bash
php tools/setup-all-businesses.php
```

Script ini akan:
- ✅ Buat database terpisah untuk setiap bisnis
- ✅ Install schema (tabel, struktur)
- ✅ Insert data default (admin user, categories)
- ✅ Setup branch & divisions

**Databases yang dibuat:**
- `narayana_hotel` → Narayana Hotel
- `narayana_eat_meet` → Eat & Meet Restaurant
- `narayana_pabrik_kapal` → Pabrik Kapal
- `narayana_furniture` → Furniture Jepara

### Step 2: Switch ke Bisnis yang Mau Dikerjakan

**Cara 1: Via Browser (Recommended)**
```
http://localhost:8080/narayana/tools/business-switcher.php
```
Klik tombol bisnis yang mau dikerjakan.

**Cara 2: Via Terminal**
```bash
# Narayana Hotel
php tools/switch-business.php narayana-hotel

# Eat & Meet Restaurant
php tools/switch-business.php eat-meet

# Pabrik Kapal
php tools/switch-business.php pabrik-kapal

# Furniture
php tools/switch-business.php furniture-jepara
```

**Cara 3: Via Batch File (Double Click)**
```bash
switch-business.bat narayana-hotel
```

### Step 3: Login & Mulai Development

1. Buka: `http://localhost:8080/narayana/`
2. Login dengan:
   - **Username:** admin
   - **Password:** admin123
3. Mulai develop sesuai bisnis yang aktif!

## 🔄 Workflow Development

### Scenario: Develop Hotel dulu, lalu Restaurant

```bash
# 1. Set active: Hotel
php tools/switch-business.php narayana-hotel

# 2. Develop fitur hotel
# - Edit modules/frontdesk/
# - Add room management
# - etc...

# 3. Commit progress
git add .
git commit -m "Hotel: add room booking feature (WIP)"

# 4. Switch ke Restaurant
php tools/switch-business.php eat-meet

# 5. Develop fitur restaurant
# - Edit modules/ (create menu system)
# - Add order management
# - etc...

# 6. Commit progress
git add .
git commit -m "Restaurant: add menu management (WIP)"

# 7. Kapan aja bisa balik ke Hotel
php tools/switch-business.php narayana-hotel
# Continue dari commit terakhir!
```

## 📊 Database Structure

Setiap bisnis punya database sendiri dengan tabel yang sama (core):

```sql
-- Core tables (sama untuk semua bisnis)
users
branches
divisions
expense_categories
cash_book
cash_book_logs
procurement_suppliers
procurement_purchase_orders
sales_invoices
settings

-- Future: Business-specific tables
-- Hotel: frontdesk_rooms, frontdesk_reservations
-- Restaurant: menu_items, orders, tables
-- Pabrik Kapal: projects, production, materials
-- Furniture: products, orders, workshop
```

## 🎨 Customization per Business

### 1. Buku Kas (Cashbook)

Setiap bisnis ada kolom tambahan di cashbook:

**Hotel:**
- Room Number
- Guest Name

**Restaurant:**
- Table Number
- Order Number
- Waiter Name

**Pabrik Kapal:**
- Project Code
- Ship Name
- Supplier/Client

**Furniture:**
- Order Number
- Product Name
- Customer Name

### 2. Dashboard Widgets

Setiap bisnis tampil widget yang berbeda:

**Hotel:** Occupancy, Reservations, Revenue, Rooms
**Restaurant:** Daily Sales, Orders, Best Sellers
**Pabrik Kapal:** Projects, Production, Materials
**Furniture:** Orders, Production, Inventory

### 3. Warna Tema

- Hotel: Biru (#4338ca)
- Restaurant: Orange (#f59e0b)
- Pabrik Kapal: Cyan (#0891b2)
- Furniture: Brown (#92400e)

## 🛠️ Development Tips

### Check Active Business
```bash
cat config/active-business.php
```

### List All Businesses
```bash
php tools/switch-business.php
```

### Quick Switch Commands
```bash
# Buat alias di PowerShell profile
Set-Alias sb "php tools/switch-business.php"

# Usage
sb narayana-hotel
sb eat-meet
sb pabrik-kapal
sb furniture-jepara
```

## 📝 Modules Development

### Core Modules (Shared - Ada di semua bisnis)
- ✅ Cashbook (Buku Kas Besar)
- ✅ Auth (Login, Users)
- ✅ Settings (Pengaturan)
- ✅ Reports (Laporan)
- ✅ Divisions (Divisi)
- ✅ Procurement (Pengadaan)
- ✅ Sales (Penjualan)

### Business-Specific Modules (Perlu dibuat per bisnis)

**Hotel (narayana-hotel):**
- [ ] Frontdesk (Check-in/out) - ✅ Already exists
- [ ] Room Management - ✅ Already exists
- [ ] Reservations - ✅ Already exists
- [ ] Housekeeping

**Restaurant (eat-meet):**
- [ ] Menu Management
- [ ] Order System
- [ ] Kitchen Display
- [ ] Table Management
- [ ] Waiters Management

**Pabrik Kapal (pabrik-kapal):**
- [ ] Project Management
- [ ] Production Planning
- [ ] Material Tracking
- [ ] Ship Building Progress
- [ ] Client Orders

**Furniture (furniture-jepara):**
- [ ] Product Catalog
- [ ] Custom Orders
- [ ] Workshop Management
- [ ] Inventory (Wood, Materials)
- [ ] Delivery Tracking

## 🔍 Troubleshooting

### Database Connection Error
```php
// Check config/config.php
// Make sure DB_NAME matches active business database
```

### Module Not Found
```php
// Check if module enabled in business config
// config/businesses/<business-id>.php
'enabled_modules' => [...]
```

### Wrong Business Active
```bash
# Switch to correct business
php tools/switch-business.php <correct-business-id>
```

## 🚀 Production Deployment

Saat production, deploy each business separately:

```
Server 1: narayana_hotel database + hotel modules
Server 2: narayana_eat_meet database + restaurant modules
Server 3: narayana_pabrik_kapal database + manufacturing modules
Server 4: narayana_furniture database + furniture modules
```

Atau semua dalam 1 server dengan database terpisah (seperti sekarang).

## 📞 Support

Jika ada masalah:
1. Check active business: `cat config/active-business.php`
2. Check database connection
3. Run setup ulang: `php tools/setup-all-businesses.php`
4. Check git history: `git log`

---

Happy Coding! 🎉
