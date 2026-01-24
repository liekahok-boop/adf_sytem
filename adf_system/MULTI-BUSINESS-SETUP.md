# Multi-Business Database Setup Guide

## Arsitektur Multi-Business dengan Database Terpisah

Sistem ini memungkinkan owner mengelola multiple bisnis (hotel, resto, gym, dll) dengan database yang benar-benar terpisah untuk keamanan dan isolasi data.

### Struktur Database

```
narayana_master          → Database utama (autentikasi & mapping)
  ├── users
  ├── business_tenants
  └── user_business_access

narayana_hotel_jepara    → Database bisnis Hotel Jepara
  ├── cash_book
  ├── branches
  ├── divisions
  └── ... (semua tabel operasional)

narayana_resto_semarang  → Database bisnis Resto Semarang
  ├── cash_book
  ├── branches
  └── ... (semua tabel operasional)
```

## Step 1: Buat Database Master

```sql
-- Database master untuk autentikasi
CREATE DATABASE narayana_master CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE narayana_master;

-- Tabel user (tetap di master)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('superadmin', 'owner', 'admin', 'staff') DEFAULT 'staff',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel business tenants (daftar semua bisnis)
CREATE TABLE business_tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_name VARCHAR(100) NOT NULL,
    business_type ENUM('hotel', 'restaurant', 'retail', 'service', 'other') NOT NULL,
    database_name VARCHAR(64) UNIQUE NOT NULL,
    owner_user_id INT NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_user_id) REFERENCES users(id)
);

-- Tabel akses user ke business (many-to-many)
CREATE TABLE user_business_access (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    business_tenant_id INT NOT NULL,
    role_in_business ENUM('owner', 'admin', 'manager', 'staff') DEFAULT 'staff',
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    granted_by INT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (business_tenant_id) REFERENCES business_tenants(id),
    FOREIGN KEY (granted_by) REFERENCES users(id),
    UNIQUE KEY unique_user_business (user_id, business_tenant_id)
);

-- Insert superadmin
INSERT INTO users (username, password, full_name, role) 
VALUES ('superadmin', '$2y$10$...', 'Super Administrator', 'superadmin');
```

## Step 2: Setup Config Multi-Database

Buat file `config/multi-database.php`:

```php
<?php
class MultiDatabaseManager {
    private static $instance = null;
    private $masterConnection = null;
    private $tenantConnection = null;
    private $currentTenant = null;
    
    // Master database config
    const MASTER_HOST = 'localhost';
    const MASTER_DB = 'narayana_master';
    const MASTER_USER = 'root';
    const MASTER_PASS = '';
    
    private function __construct() {
        $this->connectMaster();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Connect ke master database
    private function connectMaster() {
        $dsn = "mysql:host=" . self::MASTER_HOST . ";dbname=" . self::MASTER_DB . ";charset=utf8mb4";
        $this->masterConnection = new PDO($dsn, self::MASTER_USER, self::MASTER_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
    
    // Get master connection
    public function getMaster() {
        return $this->masterConnection;
    }
    
    // Connect ke tenant database berdasarkan business ID
    public function connectTenant($businessId) {
        $stmt = $this->masterConnection->prepare(
            "SELECT database_name, business_name FROM business_tenants WHERE id = ? AND is_active = 1"
        );
        $stmt->execute([$businessId]);
        $tenant = $stmt->fetch();
        
        if (!$tenant) {
            throw new Exception("Business not found or inactive");
        }
        
        $dsn = "mysql:host=" . self::MASTER_HOST . ";dbname=" . $tenant['database_name'] . ";charset=utf8mb4";
        $this->tenantConnection = new PDO($dsn, self::MASTER_USER, self::MASTER_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        $this->currentTenant = $tenant;
        return $this->tenantConnection;
    }
    
    // Get tenant connection
    public function getTenant() {
        if ($this->tenantConnection === null) {
            throw new Exception("No tenant connected. Call connectTenant() first.");
        }
        return $this->tenantConnection;
    }
    
    // Get current tenant info
    public function getCurrentTenant() {
        return $this->currentTenant;
    }
    
    // Get user's accessible businesses
    public function getUserBusinesses($userId) {
        $stmt = $this->masterConnection->prepare("
            SELECT 
                bt.id,
                bt.business_name,
                bt.business_type,
                bt.database_name,
                uba.role_in_business
            FROM business_tenants bt
            INNER JOIN user_business_access uba ON bt.id = uba.business_tenant_id
            WHERE uba.user_id = ? AND bt.is_active = 1
            ORDER BY bt.business_name
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    // Check if user has access to business
    public function hasAccess($userId, $businessId) {
        $stmt = $this->masterConnection->prepare("
            SELECT COUNT(*) as count FROM user_business_access
            WHERE user_id = ? AND business_tenant_id = ?
        ");
        $stmt->execute([$userId, $businessId]);
        return $stmt->fetch()['count'] > 0;
    }
}
```

## Step 3: Modified Auth Class

Update `includes/auth.php`:

```php
<?php
require_once __DIR__ . '/../config/multi-database.php';

class Auth {
    private $dbManager;
    private $currentBusinessId = null;
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->dbManager = MultiDatabaseManager::getInstance();
    }
    
    // Login dengan username/password (cek di master DB)
    public function login($username, $password) {
        $master = $this->dbManager->getMaster();
        $stmt = $master->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // Get user's accessible businesses
            $businesses = $this->dbManager->getUserBusinesses($user['id']);
            $_SESSION['businesses'] = $businesses;
            
            return true;
        }
        return false;
    }
    
    // Set active business untuk session
    public function setActiveBusiness($businessId) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        // Check access
        if (!$this->dbManager->hasAccess($_SESSION['user_id'], $businessId)) {
            return false;
        }
        
        // Connect to tenant database
        $this->dbManager->connectTenant($businessId);
        $_SESSION['active_business_id'] = $businessId;
        $_SESSION['active_business'] = $this->dbManager->getCurrentTenant();
        
        return true;
    }
    
    // Get active business
    public function getActiveBusiness() {
        return $_SESSION['active_business'] ?? null;
    }
    
    // Get all accessible businesses
    public function getUserBusinesses() {
        return $_SESSION['businesses'] ?? [];
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role'],
            'full_name' => $_SESSION['full_name']
        ];
    }
    
    public function logout() {
        session_unset();
        session_destroy();
    }
}
```

## Step 4: Business Selector di Dashboard

Tambahkan business selector di header:

```php
<?php
$auth = new Auth();
$businesses = $auth->getUserBusinesses();
$activeBusiness = $auth->getActiveBusiness();
?>

<!-- Business Selector -->
<div class="business-selector">
    <label>Active Business:</label>
    <select onchange="switchBusiness(this.value)">
        <?php foreach ($businesses as $business): ?>
            <option value="<?= $business['id'] ?>" 
                    <?= ($activeBusiness && $activeBusiness['id'] == $business['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($business['business_name']) ?> 
                (<?= ucfirst($business['business_type']) ?>)
            </option>
        <?php endforeach; ?>
    </select>
</div>

<script>
function switchBusiness(businessId) {
    window.location.href = 'switch-business.php?business_id=' + businessId;
}
</script>
```

## Step 5: Create New Business Script

Buat `tools/create-business.php`:

```php
<?php
require_once '../config/multi-database.php';

function createNewBusiness($businessName, $businessType, $ownerUserId) {
    $dbManager = MultiDatabaseManager::getInstance();
    $master = $dbManager->getMaster();
    
    try {
        $master->beginTransaction();
        
        // Generate database name
        $dbName = 'narayana_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $businessName));
        
        // 1. Create tenant record
        $stmt = $master->prepare("
            INSERT INTO business_tenants (business_name, business_type, database_name, owner_user_id)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$businessName, $businessType, $dbName, $ownerUserId]);
        $businessId = $master->lastInsertId();
        
        // 2. Grant access to owner
        $stmt = $master->prepare("
            INSERT INTO user_business_access (user_id, business_tenant_id, role_in_business, granted_by)
            VALUES (?, ?, 'owner', ?)
        ");
        $stmt->execute([$ownerUserId, $businessId, $ownerUserId]);
        
        // 3. Create actual database
        $master->exec("CREATE DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // 4. Run schema setup
        $schemaFile = __DIR__ . '/../database.sql';
        $schema = file_get_contents($schemaFile);
        
        // Connect to new database
        $dbManager->connectTenant($businessId);
        $tenant = $dbManager->getTenant();
        $tenant->exec($schema);
        
        $master->commit();
        
        return [
            'success' => true,
            'business_id' => $businessId,
            'database_name' => $dbName
        ];
        
    } catch (Exception $e) {
        $master->rollBack();
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Example usage
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = createNewBusiness(
        $_POST['business_name'],
        $_POST['business_type'],
        $_POST['owner_user_id']
    );
    
    header('Content-Type: application/json');
    echo json_encode($result);
}
?>
```

## Cara Penggunaan

### 1. Setup Initial (Satu kali)
```bash
# Buat database master
mysql -u root -p < setup-master-database.sql

# Update config
# Edit config/multi-database.php dengan credentials database
```

### 2. Buat Bisnis Baru
```php
// Via script atau admin panel
createNewBusiness('Hotel Jepara', 'hotel', $ownerUserId);
createNewBusiness('Resto Semarang', 'restaurant', $ownerUserId);
```

### 3. User Login & Switch Business
```php
// Login
$auth->login('owner1', 'password');

// Pilih bisnis aktif
$auth->setActiveBusiness($businessId);

// Sekarang semua query otomatis ke database bisnis yang aktif
$db = MultiDatabaseManager::getInstance()->getTenant();
```

## Keuntungan Sistem Ini

✅ **Isolasi Data Penuh** - Setiap bisnis database terpisah
✅ **Keamanan Tinggi** - Data tidak bisa bocor antar bisnis
✅ **Backup Mudah** - Backup per bisnis
✅ **Skalabilitas** - Bisa pindah database ke server berbeda
✅ **Multi-tenant Clean** - Satu owner bisa punya banyak bisnis

## Migration dari Single Database

Jika sudah ada data di database lama:

```sql
-- Export data per branch
mysqldump narayana --where="branch_id=1" > hotel_jepara.sql
mysqldump narayana --where="branch_id=2" > resto_semarang.sql

-- Import ke database baru
mysql narayana_hotel_jepara < hotel_jepara.sql
mysql narayana_resto_semarang < resto_semarang.sql
```

## Maintenance

```sql
-- Lihat semua bisnis
SELECT * FROM narayana_master.business_tenants;

-- Lihat akses user
SELECT u.username, bt.business_name, uba.role_in_business
FROM narayana_master.user_business_access uba
JOIN narayana_master.users u ON uba.user_id = u.id
JOIN narayana_master.business_tenants bt ON uba.business_tenant_id = bt.id;

-- Nonaktifkan bisnis
UPDATE narayana_master.business_tenants 
SET is_active = 0 
WHERE id = ?;
```
