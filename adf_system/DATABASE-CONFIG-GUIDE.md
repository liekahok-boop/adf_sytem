# 🔧 ADF System - Database Configuration & Troubleshooting Guide

## ✅ VERIFIED CONFIGURATION STATUS

### Database Connection Settings
```
DB_HOST     = localhost
DB_USER     = root
DB_PASSWORD = (empty)
DB_CHARSET  = utf8mb4
```

**File Location:** `config/config.php`

---

## 📊 Business Database Mapping

| Business | Business ID | Database | Config File |
|----------|------------|----------|------------|
| Narayana Hotel Jepara | `narayana-hotel` | `adf_narayana_hotel` | `narayana-hotel.php` |
| Ben's Cafe | `bens-cafe` | `adf_benscafe` | `bens-cafe.php` |
| Eat & Meet Restaurant | `eat-meet` | `adf_eat_meet` | `eat-meet.php` |
| Pabrik Kapal | `pabrik-kapal` | `adf_pabrik_kapal` | `pabrik-kapal.php` |
| Furniture Jepara | `furniture-jepara` | `adf_furniture` | `furniture-jepara.php` |
| Karimunjawa Tourism | `karimunjawa-party-boat` | `adf_karimunjawa` | `karimunjawa-party-boat.php` |

**Location:** `config/businesses/*.php`

---

## 📋 Required Tables Per Database

✅ Semua database sudah punya 6 tabel lengkap:

1. **users** - User accounts
2. **settings** - System settings  
3. **divisions** - Division/department master data
4. **categories** - Transaction categories
5. **cash_balance** - Daily balance records
6. **cash_book** - Transaction ledger

---

## 🔄 Database Switching Mechanism

### How It Works

```php
// 1. User login sets active business
$_SESSION['active_business_id'] = 'bens-cafe';

// 2. Database switches automatically
$db = Database::getInstance();
// Automatically connects to adf_benscafe

// 3. Or explicit switch
Database::switchDatabase('adf_eat_meet');
$db = Database::getInstance();
// Now connects to adf_eat_meet
```

### Key Files

- **Database Class:** `config/database.php`
  - `getInstance()` - Get connection (auto-detects business)
  - `switchDatabase($dbName)` - Manually switch database
  
- **Business Helper:** `includes/business_helper.php`
  - `getActiveBusinessId()` - Get current business from session
  - `getActiveBusinessConfig()` - Get business config
  - `setActiveBusinessId($id)` - Set active business

---

## ✅ Data Status

| Database | Users | Divisions | Categories | Transactions |
|----------|-------|-----------|-----------|-------------|
| adf_narayana_hotel | 2 (admin, manager) | 11 | 70 | 0 |
| adf_benscafe | 0 | 11 | 70 | 0 |
| adf_eat_meet | 0 | 11 | 70 | 0 |
| adf_pabrik_kapal | 0 | 11 | 70 | 0 |
| adf_furniture | 0 | 11 | 70 | 0 |
| adf_karimunjawa | 0 | 11 | 70 | 0 |

### Notes:
- 11 divisions copied to all databases (consistent master data)
- 70 categories copied to all databases (consistent master data)
- Users only in narayana_hotel (master database)
- Other databases ready for transaction data

---

## 🐛 Troubleshooting

### Problem: "Table 'adf_benscafe.divisions' doesn't exist"

**Cause:** Missing table in specific database

**Solution:**
```bash
# Create missing table from adf_narayana_hotel template
mysql -u root adf_benscafe < schema-backup.sql

# Or manually create if needed
mysql -u root -e "CREATE TABLE adf_benscafe.divisions LIKE adf_narayana_hotel.divisions;"
```

### Problem: "Access denied for user 'root'@'localhost'"

**Check:**
1. Verify DB_USER in `config/config.php` matches MySQL user
2. Verify DB_PASS is correct (currently empty)
3. Check MySQL is running: `xampp/mysql/bin/mysql -u root -e "SELECT 1;"`

### Problem: "Unknown database 'adf_[business]'"

**Check:**
1. Verify database exists: `SHOW DATABASES LIKE 'adf_%';`
2. Verify business config maps to correct database
3. Verify ACTIVE_BUSINESS_ID is set in session

### Problem: Changes in one business appear in another

**Check:**
1. Verify `Database::switchDatabase()` is called correctly
2. Verify `ACTIVE_BUSINESS_ID` constant is set
3. Check session: `echo $_SESSION['active_business_id'];`

---

## 🔍 Verification Commands

### Check All Databases Exist
```bash
mysql -u root -e "SHOW DATABASES LIKE 'adf_%';"
```

### Check Tables in Database
```bash
mysql -u root adf_benscafe -e "SHOW TABLES;"
```

### Check Table Structure
```bash
mysql -u root adf_narayana_hotel -e "SHOW CREATE TABLE divisions\G"
```

### Count Records
```bash
mysql -u root adf_narayana_hotel -e "SELECT 'divisions' as table_name, COUNT(*) as count FROM divisions UNION ALL SELECT 'categories', COUNT(*) FROM categories;"
```

### Test Connection
```bash
mysql -u root -e "SELECT DATABASE(), USER(), VERSION();"
```

---

## 🚀 Maintenance Tasks

### Backup All Databases
```bash
# Backup single database
mysqldump -u root adf_narayana_hotel > backup_narayana_hotel.sql

# Backup all adf_* databases
for db in adf_*; do
  mysqldump -u root $db > backup_$db.sql
done
```

### Restore Database
```bash
mysql -u root adf_benscafe < backup_benscafe.sql
```

### Create New Business (Template)

1. Create new database:
   ```bash
   mysql -u root -e "CREATE DATABASE adf_newbusiness CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

2. Copy structure from template:
   ```bash
   mysqldump -u root --no-data adf_narayana_hotel | mysql -u root adf_newbusiness
   ```

3. Create config file: `config/businesses/newbusiness.php`
   ```php
   <?php
   return [
       'business_id' => 'newbusiness',
       'name' => 'New Business Name',
       'database' => 'adf_newbusiness',
       'enabled_modules' => [...]
   ];
   ```

4. Add to `config/businesses.php` if using legacy config

---

## 📝 Configuration Files Reference

### Main Config
- **File:** `config/config.php`
- **Contains:** DB_HOST, DB_USER, DB_PASS, BASE_URL, SESSION settings

### Business Configs
- **Location:** `config/businesses/`
- **Pattern:** `{business-id}.php`
- **Contains:** business name, database name, enabled modules, theme

### Database Class
- **File:** `config/database.php`
- **Pattern:** Singleton with database switching support

### Business Helper
- **File:** `includes/business_helper.php`
- **Functions:** getActiveBusinessId(), setActiveBusinessId(), getActiveBusinessConfig()

---

## ✅ Final Checklist

- [x] All 6 databases created and connected
- [x] All required tables exist in all databases
- [x] Business configs correctly mapped to databases
- [x] Divisions and categories copied to all databases
- [x] Database switching mechanism functional
- [x] User authentication working
- [x] Session management working
- [x] Error handling in place

---

## 📞 Quick Reference

**Default Login:**
```
Username: admin
Password: admin
Business: Narayana Hotel
```

**Test URLs:**
- Login: `http://localhost:8080/adf_system/login.php`
- Dashboard: `http://localhost:8080/adf_system/index.php`
- Settings: `http://localhost:8080/adf_system/modules/settings/`

**Key Files for Debugging:**
- Database issues: `config/database.php`
- Business issues: `config/businesses.php` or `config/businesses/[id].php`
- Session issues: `includes/auth.php`
- Multi-business issues: `includes/business_helper.php`

---

**Last Updated:** January 24, 2026
**Status:** ✅ All Verified & Ready for Production
