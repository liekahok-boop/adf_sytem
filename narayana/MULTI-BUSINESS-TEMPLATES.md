# Multi-Business System dengan Template Custom Per Bisnis

## Konsep: Adaptive Business System

Sistem ini memungkinkan owner punya berbagai jenis bisnis dengan tampilan dan fitur yang berbeda:

```
Owner Dashboard
├── 🏨 Narayana Hotel Jepara     → Template Hotel (Room, Reservation, Housekeeping)
├── 🍽️ Warung Makan Jepara       → Template Restaurant (Menu, Orders, Kitchen)
├── 🏋️ Fitness Center Semarang  → Template Gym (Members, Classes, PT)
├── 🏪 Minimarket Pati           → Template Retail (Inventory, POS, Stock)
└── ☕ Kopi Kenangan Kudus       → Template Cafe (Menu, Orders, Barista)
```

## Arsitektur System

### 1. Business Templates

```
config/
  business-templates/
    hotel.json          → Config untuk bisnis hotel
    restaurant.json     → Config untuk bisnis resto
    gym.json            → Config untuk bisnis gym/fitness
    retail.json         → Config untuk bisnis retail/minimarket
    cafe.json           → Config untuk bisnis cafe/coffee shop
    default.json        → Config default (cashbook only)
```

### 2. Template Config Structure

```json
// config/business-templates/hotel.json
{
  "template_name": "hotel",
  "display_name": "Hotel Management",
  "icon": "🏨",
  "color_primary": "#4338ca",
  "color_secondary": "#1e1b4b",
  
  "enabled_modules": [
    "cashbook",
    "frontdesk",
    "housekeeping",
    "reservations",
    "reports",
    "settings"
  ],
  
  "dashboard_layout": {
    "show_occupancy": true,
    "show_reservations": true,
    "show_room_status": true,
    "show_revenue": true,
    "chart_type": "revenue"
  },
  
  "terminology": {
    "customer": "Guest",
    "transaction": "Booking",
    "item": "Room",
    "location": "Building"
  },
  
  "custom_fields": [
    {"name": "room_number", "type": "text"},
    {"name": "check_in", "type": "date"},
    {"name": "check_out", "type": "date"}
  ],
  
  "navigation": [
    {"label": "Dashboard", "icon": "home", "url": "index.php"},
    {"label": "Front Desk", "icon": "users", "url": "modules/frontdesk/"},
    {"label": "Rooms", "icon": "grid", "url": "modules/frontdesk/manage-rooms.php"},
    {"label": "Reservations", "icon": "calendar", "url": "modules/frontdesk/reservasi.php"},
    {"label": "Cashbook", "icon": "book", "url": "modules/cashbook/"},
    {"label": "Reports", "icon": "file-text", "url": "modules/reports/"},
    {"label": "Settings", "icon": "settings", "url": "modules/settings/"}
  ]
}
```

```json
// config/business-templates/restaurant.json
{
  "template_name": "restaurant",
  "display_name": "Restaurant Management",
  "icon": "🍽️",
  "color_primary": "#f97316",
  "color_secondary": "#ea580c",
  
  "enabled_modules": [
    "cashbook",
    "menu",
    "orders",
    "kitchen",
    "tables",
    "reports",
    "settings"
  ],
  
  "dashboard_layout": {
    "show_today_orders": true,
    "show_menu_items": true,
    "show_table_status": true,
    "show_revenue": true,
    "chart_type": "sales"
  },
  
  "terminology": {
    "customer": "Diner",
    "transaction": "Order",
    "item": "Menu Item",
    "location": "Table"
  },
  
  "navigation": [
    {"label": "Dashboard", "icon": "home", "url": "index.php"},
    {"label": "Orders", "icon": "shopping-cart", "url": "modules/orders/"},
    {"label": "Menu", "icon": "book-open", "url": "modules/menu/"},
    {"label": "Kitchen", "icon": "target", "url": "modules/kitchen/"},
    {"label": "Tables", "icon": "grid", "url": "modules/tables/"},
    {"label": "Cashbook", "icon": "dollar-sign", "url": "modules/cashbook/"},
    {"label": "Reports", "icon": "trending-up", "url": "modules/reports/"},
    {"label": "Settings", "icon": "settings", "url": "modules/settings/"}
  ]
}
```

```json
// config/business-templates/gym.json
{
  "template_name": "gym",
  "display_name": "Gym & Fitness Center",
  "icon": "🏋️",
  "color_primary": "#dc2626",
  "color_secondary": "#991b1b",
  
  "enabled_modules": [
    "cashbook",
    "members",
    "classes",
    "trainers",
    "equipment",
    "reports",
    "settings"
  ],
  
  "dashboard_layout": {
    "show_active_members": true,
    "show_today_classes": true,
    "show_membership_revenue": true,
    "show_attendance": true,
    "chart_type": "membership"
  },
  
  "terminology": {
    "customer": "Member",
    "transaction": "Membership",
    "item": "Package",
    "location": "Branch"
  },
  
  "navigation": [
    {"label": "Dashboard", "icon": "home", "url": "index.php"},
    {"label": "Members", "icon": "users", "url": "modules/members/"},
    {"label": "Classes", "icon": "calendar", "url": "modules/classes/"},
    {"label": "Personal Trainers", "icon": "user-check", "url": "modules/trainers/"},
    {"label": "Equipment", "icon": "activity", "url": "modules/equipment/"},
    {"label": "Cashbook", "icon": "dollar-sign", "url": "modules/cashbook/"},
    {"label": "Reports", "icon": "bar-chart", "url": "modules/reports/"},
    {"label": "Settings", "icon": "settings", "url": "modules/settings/"}
  ]
}
```

```json
// config/business-templates/retail.json
{
  "template_name": "retail",
  "display_name": "Retail Store / Minimarket",
  "icon": "🏪",
  "color_primary": "#16a34a",
  "color_secondary": "#15803d",
  
  "enabled_modules": [
    "cashbook",
    "pos",
    "inventory",
    "products",
    "suppliers",
    "reports",
    "settings"
  ],
  
  "dashboard_layout": {
    "show_today_sales": true,
    "show_low_stock": true,
    "show_best_sellers": true,
    "show_revenue": true,
    "chart_type": "sales"
  },
  
  "terminology": {
    "customer": "Customer",
    "transaction": "Sale",
    "item": "Product",
    "location": "Store"
  },
  
  "navigation": [
    {"label": "Dashboard", "icon": "home", "url": "index.php"},
    {"label": "POS", "icon": "shopping-bag", "url": "modules/pos/"},
    {"label": "Products", "icon": "package", "url": "modules/products/"},
    {"label": "Inventory", "icon": "archive", "url": "modules/inventory/"},
    {"label": "Suppliers", "icon": "truck", "url": "modules/suppliers/"},
    {"label": "Cashbook", "icon": "dollar-sign", "url": "modules/cashbook/"},
    {"label": "Reports", "icon": "trending-up", "url": "modules/reports/"},
    {"label": "Settings", "icon": "settings", "url": "modules/settings/"}
  ]
}
```

## Implementation

### 1. Business Template Manager

```php
<?php
// config/business-template-manager.php

class BusinessTemplateManager {
    private $templatesPath = __DIR__ . '/business-templates/';
    private $activeTemplate = null;
    
    public function loadTemplate($businessType) {
        $templateFile = $this->templatesPath . $businessType . '.json';
        
        if (!file_exists($templateFile)) {
            $templateFile = $this->templatesPath . 'default.json';
        }
        
        $config = json_decode(file_get_contents($templateFile), true);
        $this->activeTemplate = $config;
        
        return $config;
    }
    
    public function getActiveTemplate() {
        return $this->activeTemplate;
    }
    
    public function isModuleEnabled($moduleName) {
        if (!$this->activeTemplate) {
            return false;
        }
        return in_array($moduleName, $this->activeTemplate['enabled_modules']);
    }
    
    public function getNavigation() {
        return $this->activeTemplate['navigation'] ?? [];
    }
    
    public function getDashboardLayout() {
        return $this->activeTemplate['dashboard_layout'] ?? [];
    }
    
    public function getTerminology($key) {
        return $this->activeTemplate['terminology'][$key] ?? ucfirst($key);
    }
    
    public function getColors() {
        return [
            'primary' => $this->activeTemplate['color_primary'] ?? '#4338ca',
            'secondary' => $this->activeTemplate['color_secondary'] ?? '#1e1b4b'
        ];
    }
    
    public function getIcon() {
        return $this->activeTemplate['icon'] ?? '🏢';
    }
}
```

### 2. Modified Auth with Template Loading

```php
<?php
// includes/auth.php (tambahan)

class Auth {
    private $templateManager;
    
    public function setActiveBusiness($businessId) {
        // ... kode sebelumnya ...
        
        // Load business info
        $master = $this->dbManager->getMaster();
        $stmt = $master->prepare("SELECT * FROM business_tenants WHERE id = ?");
        $stmt->execute([$businessId]);
        $business = $stmt->fetch();
        
        // Load template sesuai business type
        $this->templateManager = new BusinessTemplateManager();
        $template = $this->templateManager->loadTemplate($business['business_type']);
        
        $_SESSION['active_business'] = $business;
        $_SESSION['business_template'] = $template;
        $_SESSION['template_manager'] = $this->templateManager;
        
        return true;
    }
    
    public function getTemplate() {
        return $_SESSION['business_template'] ?? null;
    }
    
    public function getTemplateManager() {
        return $_SESSION['template_manager'] ?? new BusinessTemplateManager();
    }
}
```

### 3. Dynamic Header with Template

```php
<?php
// includes/header-dynamic.php

$auth = new Auth();
$business = $auth->getActiveBusiness();
$template = $auth->getTemplate();
$colors = $template['color_primary'] ?? '#4338ca';
$icon = $template['icon'] ?? '🏢';
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $business['business_name'] ?? 'Dashboard' ?></title>
    <style>
        :root {
            --primary-color: <?= $template['color_primary'] ?>;
            --secondary-color: <?= $template['color_secondary'] ?>;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        }
        
        .business-icon {
            font-size: 2rem;
        }
    </style>
</head>
<body>
    <!-- Dynamic Navigation -->
    <nav class="sidebar">
        <div class="business-header">
            <span class="business-icon"><?= $icon ?></span>
            <h2><?= htmlspecialchars($business['business_name']) ?></h2>
            <p><?= $template['display_name'] ?></p>
        </div>
        
        <ul class="nav-menu">
            <?php foreach ($template['navigation'] as $nav): ?>
                <li>
                    <a href="<?= BASE_URL ?>/<?= $nav['url'] ?>">
                        <i data-feather="<?= $nav['icon'] ?>"></i>
                        <span><?= $nav['label'] ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</body>
</html>
```

### 4. Dynamic Dashboard

```php
<?php
// index.php (Dynamic Dashboard)

require_once 'config/config.php';
require_once 'includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

$business = $auth->getActiveBusiness();
$template = $auth->getTemplate();
$layout = $template['dashboard_layout'];

include 'includes/header-dynamic.php';
?>

<div class="dashboard">
    <h1>Dashboard - <?= $business['business_name'] ?></h1>
    
    <!-- Conditional Widgets Based on Template -->
    
    <?php if ($layout['show_occupancy'] ?? false): ?>
        <!-- Hotel Occupancy Widget -->
        <div class="widget occupancy">
            <h3>Room Occupancy</h3>
            <!-- ... -->
        </div>
    <?php endif; ?>
    
    <?php if ($layout['show_today_orders'] ?? false): ?>
        <!-- Restaurant Orders Widget -->
        <div class="widget orders">
            <h3>Today's Orders</h3>
            <!-- ... -->
        </div>
    <?php endif; ?>
    
    <?php if ($layout['show_active_members'] ?? false): ?>
        <!-- Gym Members Widget -->
        <div class="widget members">
            <h3>Active Members</h3>
            <!-- ... -->
        </div>
    <?php endif; ?>
    
    <?php if ($layout['show_today_sales'] ?? false): ?>
        <!-- Retail Sales Widget -->
        <div class="widget sales">
            <h3>Today's Sales</h3>
            <!-- ... -->
        </div>
    <?php endif; ?>
    
    <!-- Universal Widgets -->
    <div class="widget revenue">
        <h3><?= $template['terminology']['transaction'] ?? 'Transaction' ?> Chart</h3>
        <canvas id="revenueChart"></canvas>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
```

### 5. Module Guard

```php
<?php
// includes/module-guard.php

function requireModule($moduleName) {
    $auth = new Auth();
    $templateManager = $auth->getTemplateManager();
    
    if (!$templateManager->isModuleEnabled($moduleName)) {
        header('HTTP/1.0 403 Forbidden');
        die("Module '$moduleName' not available for this business type.");
    }
}

// Usage di setiap module
// modules/frontdesk/index.php
require_once '../../includes/module-guard.php';
requireModule('frontdesk'); // Will block if business type is not 'hotel'
```

## Setup New Business dengan Template

```php
<?php
// tools/create-business-with-template.php

function createBusinessWithTemplate($data) {
    $businessName = $data['business_name'];
    $businessType = $data['business_type']; // hotel, restaurant, gym, retail, cafe
    $ownerUserId = $data['owner_user_id'];
    
    // 1. Create business tenant
    $result = createNewBusiness($businessName, $businessType, $ownerUserId);
    
    if (!$result['success']) {
        return $result;
    }
    
    // 2. Load template and setup modules
    $templateManager = new BusinessTemplateManager();
    $template = $templateManager->loadTemplate($businessType);
    
    // 3. Initialize only enabled modules
    foreach ($template['enabled_modules'] as $module) {
        initializeModule($result['business_id'], $module);
    }
    
    // 4. Apply custom branding
    applyBranding($result['business_id'], [
        'colors' => [
            'primary' => $template['color_primary'],
            'secondary' => $template['color_secondary']
        ],
        'icon' => $template['icon']
    ]);
    
    return $result;
}
```

## Business Selector dengan Template Preview

```html
<!-- Business selector di owner dashboard -->
<div class="business-grid">
    <?php foreach ($businesses as $business): ?>
        <?php 
            $template = loadTemplatePreview($business['business_type']);
            $colors = $template['color_primary'];
            $icon = $template['icon'];
        ?>
        <div class="business-card" onclick="switchBusiness(<?= $business['id'] ?>)">
            <div class="business-icon" style="background: <?= $colors ?>">
                <?= $icon ?>
            </div>
            <h3><?= $business['business_name'] ?></h3>
            <p><?= $template['display_name'] ?></p>
            <span class="business-type"><?= ucfirst($business['business_type']) ?></span>
        </div>
    <?php endforeach; ?>
</div>
```

## Contoh Real World

```
Owner: Budi Santoso

Bisnis 1: 🏨 Narayana Hotel Jepara
  → Template: hotel
  → Menu: Dashboard, Front Desk, Rooms, Reservations, Housekeeping, Cashbook, Reports
  → Warna: Biru (#4338ca)
  → Database: narayana_hotel_jepara

Bisnis 2: 🍽️ Warung Pak Budi
  → Template: restaurant  
  → Menu: Dashboard, Orders, Menu, Kitchen, Tables, Cashbook, Reports
  → Warna: Orange (#f97316)
  → Database: narayana_warung_pakbudi

Bisnis 3: 🏋️ Fitness Zone Semarang
  → Template: gym
  → Menu: Dashboard, Members, Classes, Trainers, Equipment, Cashbook, Reports
  → Warna: Merah (#dc2626)
  → Database: narayana_fitness_semarang

Bisnis 4: 🏪 Minimarket 212
  → Template: retail
  → Menu: Dashboard, POS, Products, Inventory, Suppliers, Cashbook, Reports
  → Warna: Hijau (#16a34a)
  → Database: narayana_minimarket_212
```

## Keuntungan System

✅ **Flexible** - Satu sistem untuk berbagai jenis bisnis
✅ **Professional** - Setiap bisnis punya tampilan sesuai industrinya
✅ **Scalable** - Tinggal tambah template baru untuk bisnis baru
✅ **Efficient** - Modul yang tidak perlu tidak di-load
✅ **Customizable** - Gampang customize per template
✅ **User Friendly** - Owner tidak bingung karena tampilan sesuai bisnisnya

## Migration Plan

Untuk implement system ini:

1. Buat struktur template (JSON files)
2. Update auth system untuk load template
3. Buat header/navigation dynamic
4. Buat dashboard dynamic dengan conditional widgets
5. Add module guards
6. Test dengan 2-3 template berbeda
7. Deploy!

Butuh berapa lama? ~2-3 hari development + testing.
