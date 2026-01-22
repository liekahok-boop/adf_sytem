# Development Workflow: Multi-Business dengan Shared Core

## Konsep: Modular System dengan Shared Core

```
CORE MODULES (Sama untuk semua bisnis)
├── Cashbook (Buku Kas Besar) ✅ Universal
├── Authentication & Users
├── Settings & Configuration
├── Reports (Basic)
└── Branches & Divisions

BUSINESS-SPECIFIC MODULES (Berbeda per bisnis)
├── Hotel → Frontdesk, Rooms, Reservations
├── Restaurant → Menu, Orders, Kitchen, Tables
├── Gym → Members, Classes, Trainers
├── Retail → POS, Inventory, Products
└── ... (dll)
```

## Strategy: Git Branch per Bisnis

### Setup Structure

```
Repository: narayana-business-system
├── main (Core system - shared)
├── branch: narayana-hotel (Hotel Jepara)
├── branch: warung-pakbudi (Restaurant)
├── branch: fitness-zone (Gym)
└── branch: minimarket-212 (Retail)
```

### Cara Kerja

**1. Core Development di `main` branch**
```bash
git checkout main
# Develop core modules (cashbook, auth, settings)
# Ini akan jadi base untuk semua bisnis
```

**2. Business-Specific Development**
```bash
# Mulai bisnis hotel
git checkout narayana-hotel
# Develop fitur hotel (frontdesk, rooms, etc)

# Pindah ke bisnis restaurant
git checkout warung-pakbudi  
# Develop fitur restaurant (menu, orders, etc)

# Balik ke hotel lagi
git checkout narayana-hotel
# Continue development hotel
```

**3. Sync Core Updates ke All Businesses**
```bash
# Update core di main
git checkout main
git add modules/cashbook/
git commit -m "Update cashbook: add new feature"

# Merge ke semua business branches
git checkout narayana-hotel
git merge main  # Core updates masuk ke hotel

git checkout warung-pakbudi
git merge main  # Core updates masuk ke restaurant

# dst...
```

## Alternative: Config-Based Multi-Business (Simpler)

Jika tidak mau ribet dengan git branching, pakai **single codebase + config switching**:

### Setup Directory Structure

```
narayana/
├── config/
│   ├── businesses/
│   │   ├── narayana-hotel.php      (Config Hotel)
│   │   ├── warung-pakbudi.php      (Config Restaurant)
│   │   ├── fitness-zone.php        (Config Gym)
│   │   └── minimarket-212.php      (Config Retail)
│   └── active-business.php         (Pointer ke bisnis aktif)
│
├── modules/
│   ├── cashbook/                   (CORE - Always active)
│   ├── auth/                       (CORE - Always active)
│   ├── settings/                   (CORE - Always active)
│   ├── reports/                    (CORE - Always active)
│   │
│   ├── hotel/                      (Business-specific)
│   │   ├── frontdesk/
│   │   ├── rooms/
│   │   └── reservations/
│   │
│   ├── restaurant/                 (Business-specific)
│   │   ├── menu/
│   │   ├── orders/
│   │   └── kitchen/
│   │
│   ├── gym/                        (Business-specific)
│   │   ├── members/
│   │   ├── classes/
│   │   └── trainers/
│   │
│   └── retail/                     (Business-specific)
│       ├── pos/
│       ├── inventory/
│       └── products/
│
└── index.php (Load based on active business)
```

### Config Files

```php
<?php
// config/businesses/narayana-hotel.php
return [
    'business_id' => 'narayana-hotel',
    'business_name' => 'Narayana Hotel Jepara',
    'business_type' => 'hotel',
    'database' => 'narayana_hotel_jepara',
    
    'enabled_modules' => [
        'cashbook',      // Core
        'auth',          // Core
        'settings',      // Core
        'reports',       // Core
        'hotel/frontdesk',
        'hotel/rooms',
        'hotel/reservations'
    ],
    
    'theme' => [
        'color' => '#4338ca',
        'icon' => '🏨'
    ]
];
```

```php
<?php
// config/businesses/warung-pakbudi.php
return [
    'business_id' => 'warung-pakbudi',
    'business_name' => 'Warung Pak Budi',
    'business_type' => 'restaurant',
    'database' => 'narayana_warung_pakbudi',
    
    'enabled_modules' => [
        'cashbook',      // Core (sama)
        'auth',          // Core (sama)
        'settings',      // Core (sama)
        'reports',       // Core (sama)
        'restaurant/menu',
        'restaurant/orders',
        'restaurant/kitchen'
    ],
    
    'theme' => [
        'color' => '#f97316',
        'icon' => '🍽️'
    ]
];
```

### Business Switcher

```php
<?php
// config/active-business.php
// Ganti ini untuk switch bisnis saat development

return 'narayana-hotel';  // <-- Change this to switch business

// Options:
// - 'narayana-hotel'
// - 'warung-pakbudi'
// - 'fitness-zone'
// - 'minimarket-212'
```

### Automatic Loading

```php
<?php
// config/config.php

// Load active business config
$activeBusiness = require __DIR__ . '/active-business.php';
$businessConfig = require __DIR__ . '/businesses/' . $activeBusiness . '.php';

// Set constants
define('ACTIVE_BUSINESS', $activeBusiness);
define('BUSINESS_NAME', $businessConfig['business_name']);
define('BUSINESS_TYPE', $businessConfig['business_type']);
define('DB_NAME', $businessConfig['database']);

// Store in global
$GLOBALS['business_config'] = $businessConfig;
```

### Helper untuk Check Module

```php
<?php
// includes/functions.php

function isModuleEnabled($moduleName) {
    $config = $GLOBALS['business_config'];
    return in_array($moduleName, $config['enabled_modules']);
}

function getBusinessConfig($key = null) {
    $config = $GLOBALS['business_config'];
    return $key ? ($config[$key] ?? null) : $config;
}
```

## Development Workflow

### Scenario 1: Develop Hotel, kemudian Pause untuk Develop Restaurant

```bash
# 1. Lagi develop hotel
# Edit: config/active-business.php
return 'narayana-hotel';

# 2. Commit progress hotel
git add .
git commit -m "Hotel: add room management feature (WIP)"

# 3. Switch ke restaurant
# Edit: config/active-business.php
return 'warung-pakbudi';

# 4. Develop restaurant
# ... develop menu, orders, etc ...

# 5. Commit progress restaurant
git add .
git commit -m "Restaurant: add menu management (WIP)"

# 6. Balik ke hotel lagi
# Edit: config/active-business.php
return 'narayana-hotel';

# Continue development hotel dari terakhir commit
```

### Scenario 2: Update Core Cashbook (All businesses affected)

```bash
# Edit modules/cashbook/index.php
# Add new feature

git add modules/cashbook/
git commit -m "Core: update cashbook - add category filter"

# Semua business dapat update ini karena cashbook = core module
```

## CLI Tool untuk Switch Business (Optional)

Buat helper script untuk switch lebih mudah:

```php
<?php
// tools/switch-business.php

if ($argc < 2) {
    echo "Usage: php switch-business.php <business-id>\n";
    echo "Available businesses:\n";
    $files = glob(__DIR__ . '/../config/businesses/*.php');
    foreach ($files as $file) {
        $id = basename($file, '.php');
        echo "  - $id\n";
    }
    exit(1);
}

$businessId = $argv[1];
$businessFile = __DIR__ . '/../config/businesses/' . $businessId . '.php';

if (!file_exists($businessFile)) {
    echo "Error: Business '$businessId' not found!\n";
    exit(1);
}

// Update active business
$activeFile = __DIR__ . '/../config/active-business.php';
file_put_contents($activeFile, "<?php\nreturn '$businessId';\n");

$config = require $businessFile;
echo "✓ Switched to: {$config['business_name']}\n";
echo "  Type: {$config['business_type']}\n";
echo "  Database: {$config['database']}\n";
echo "  Modules: " . count($config['enabled_modules']) . " enabled\n";
```

**Usage:**
```bash
# Switch to hotel
php tools/switch-business.php narayana-hotel

# Switch to restaurant
php tools/switch-business.php warung-pakbudi

# Switch to gym
php tools/switch-business.php fitness-zone
```

## Git Strategy untuk Multi-Business Development

### Option A: Single Repo dengan Tag per Business

```bash
# Development hotel
git add .
git commit -m "Hotel: add booking feature"
git tag hotel-v1.0

# Development restaurant
git add .
git commit -m "Restaurant: add menu feature"
git tag restaurant-v0.5

# Development gym
git add .
git commit -m "Gym: add member feature"
git tag gym-v0.3
```

### Option B: Separate Repos per Business

```
narayana-core/          (Shared core)
├── modules/cashbook/
├── modules/auth/
└── modules/settings/

narayana-hotel/         (Hotel-specific)
├── .git/
├── modules/hotel/
└── requires: narayana-core

warung-pakbudi/         (Restaurant-specific)
├── .git/
├── modules/restaurant/
└── requires: narayana-core
```

## Database Management

### Development Databases

```sql
-- Create multiple databases for testing
CREATE DATABASE narayana_hotel_jepara;
CREATE DATABASE narayana_warung_pakbudi;
CREATE DATABASE narayana_fitness_zone;
CREATE DATABASE narayana_minimarket_212;

-- Each has same schema for core tables (cashbook, users, etc)
-- But different data and business-specific tables
```

### Database Switcher in Config

```php
// Automatically use correct database based on active business
$businessConfig = getBusinessConfig();
define('DB_NAME', $businessConfig['database']);
```

## Example: Cashbook dengan Sedikit Perubahan per Business

```php
<?php
// modules/cashbook/index.php

$businessType = getBusinessConfig('business_type');

// Core cashbook functionality (sama untuk semua)
$transactions = getCashbookTransactions();

// Business-specific customization (sedikit berbeda)
switch($businessType) {
    case 'hotel':
        // Tambah kolom: room_number, guest_name
        $columns = ['date', 'description', 'room_number', 'guest_name', 'amount', 'type'];
        break;
        
    case 'restaurant':
        // Tambah kolom: table_number, waiter_name
        $columns = ['date', 'description', 'table_number', 'waiter_name', 'amount', 'type'];
        break;
        
    case 'gym':
        // Tambah kolom: member_id, package_name
        $columns = ['date', 'description', 'member_id', 'package_name', 'amount', 'type'];
        break;
        
    default:
        // Default columns (retail, cafe, etc)
        $columns = ['date', 'description', 'category', 'amount', 'type'];
}

// Display cashbook dengan kolom yang sesuai
displayCashbook($transactions, $columns);
```

## Quick Reference Commands

```bash
# Check active business
cat config/active-business.php

# Switch business (manual)
echo "<?php\nreturn 'warung-pakbudi';" > config/active-business.php

# Switch business (CLI tool)
php tools/switch-business.php warung-pakbudi

# List available businesses
ls config/businesses/

# Commit current progress
git add .
git commit -m "[$(cat config/active-business.php | grep return | cut -d"'" -f2)] Your message"

# See which business last modified
git log --oneline | head -n 10
```

## Summary

**Konsep:**
✅ **Core modules sama** (cashbook, auth, settings) dengan sedikit customization
✅ **Business-specific modules** terpisah (hotel, restaurant, gym, retail)
✅ **Easy switching** dengan config file atau CLI tool
✅ **Git-friendly** untuk pause & resume project
✅ **Database per business** untuk data isolation

**Workflow:**
1. Set active business di config
2. Develop features
3. Commit progress
4. Switch ke bisnis lain
5. Develop features bisnis lain
6. Switch balik kapan saja
7. Continue dari commit terakhir

**Simple & Flexible** untuk development multi-business yang belum selesai!

Mau saya setup sistem ini sekarang? Atau mau tanya dulu?
