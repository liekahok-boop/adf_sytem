<?php
/**
 * Test Database Connection & Simple Owner System Installer
 */

echo "<h1>🔧 Owner System Installer - Test & Install</h1>";
echo "<hr>";

// Step 1: Test config file
echo "<h3>Step 1: Testing Config File...</h3>";
if (file_exists('config/config.php')) {
    echo "✅ config.php found<br>";
    require_once 'config/config.php';
    echo "✅ config.php loaded<br>";
    echo "Database: " . DB_NAME . "<br>";
    echo "Host: " . DB_HOST . "<br>";
} else {
    die("❌ config/config.php not found!");
}

echo "<hr>";

// Step 2: Test database connection
echo "<h3>Step 2: Testing Database Connection...</h3>";
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful!<br>";
} catch(PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage());
}

echo "<hr>";

// Step 3: Create branches table
echo "<h3>Step 3: Creating branches table...</h3>";
try {
    $sql = "CREATE TABLE IF NOT EXISTS branches (
        id INT PRIMARY KEY AUTO_INCREMENT,
        branch_code VARCHAR(20) UNIQUE NOT NULL,
        branch_name VARCHAR(100) NOT NULL,
        address TEXT,
        city VARCHAR(100),
        phone VARCHAR(20),
        email VARCHAR(100),
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_active (is_active),
        INDEX idx_code (branch_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $conn->exec($sql);
    echo "✅ Table 'branches' created successfully!<br>";
} catch(PDOException $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "ℹ️ Table 'branches' already exists<br>";
    } else {
        echo "⚠️ Error: " . $e->getMessage() . "<br>";
    }
}

echo "<hr>";

// Step 4: Create owner_branch_access table
echo "<h3>Step 4: Creating owner_branch_access table...</h3>";
try {
    $sql = "CREATE TABLE IF NOT EXISTS owner_branch_access (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        branch_id INT NOT NULL,
        granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        granted_by INT,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_branch (user_id, branch_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $conn->exec($sql);
    echo "✅ Table 'owner_branch_access' created successfully!<br>";
} catch(PDOException $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "ℹ️ Table 'owner_branch_access' already exists<br>";
    } else {
        echo "⚠️ Error: " . $e->getMessage() . "<br>";
    }
}

echo "<hr>";

// Step 5: Modify users table
echo "<h3>Step 5: Adding 'owner' role to users table...</h3>";
try {
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $enumStr = $row['Type'];
    
    if (strpos($enumStr, 'owner') === false) {
        $sql = "ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'manager', 'staff', 'owner') DEFAULT 'staff'";
        $conn->exec($sql);
        echo "✅ Role 'owner' added successfully!<br>";
    } else {
        echo "ℹ️ Role 'owner' already exists<br>";
    }
} catch(PDOException $e) {
    echo "⚠️ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Step 6: Add branch_id to cash_book
echo "<h3>Step 6: Adding branch_id to cash_book table...</h3>";
try {
    $result = $conn->query("SHOW COLUMNS FROM cash_book LIKE 'branch_id'");
    if ($result->rowCount() == 0) {
        $sql = "ALTER TABLE cash_book ADD COLUMN branch_id INT DEFAULT 1 AFTER id";
        $conn->exec($sql);
        echo "✅ Column 'branch_id' added to cash_book!<br>";
    } else {
        echo "ℹ️ Column 'branch_id' already exists in cash_book<br>";
    }
} catch(PDOException $e) {
    echo "⚠️ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Step 7: Add branch_id to frontdesk tables
echo "<h3>Step 7: Adding branch_id to frontdesk tables...</h3>";
$frontdeskTables = ['frontdesk_rooms', 'frontdesk_reservations', 'frontdesk_room_types', 'frontdesk_buildings'];

foreach ($frontdeskTables as $table) {
    try {
        $checkTable = $conn->query("SHOW TABLES LIKE '$table'");
        if ($checkTable->rowCount() > 0) {
            $result = $conn->query("SHOW COLUMNS FROM $table LIKE 'branch_id'");
            if ($result->rowCount() == 0) {
                $sql = "ALTER TABLE $table ADD COLUMN branch_id INT DEFAULT 1 AFTER id";
                $conn->exec($sql);
                echo "✅ Column 'branch_id' added to $table!<br>";
            } else {
                echo "ℹ️ Column 'branch_id' already exists in $table<br>";
            }
        } else {
            echo "⚠️ Table '$table' does not exist (skip)<br>";
        }
    } catch(PDOException $e) {
        echo "⚠️ Error on $table: " . $e->getMessage() . "<br>";
    }
}

echo "<hr>";

// Step 8: Insert default branches
echo "<h3>Step 8: Creating default branches...</h3>";
try {
    $check = $conn->query("SELECT COUNT(*) as count FROM branches");
    $count = $check->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($count == 0) {
        $sql = "INSERT INTO branches (branch_code, branch_name, address, city, phone, email, is_active) VALUES
            ('HQ', 'Kantor Pusat', 'Alamat Kantor Pusat', 'Jakarta', '021-12345678', 'hq@narayana.com', 1),
            ('CBG001', 'Cabang 1 - Bandung', 'Jl. Merdeka No. 1', 'Bandung', '022-12345678', 'bandung@narayana.com', 1),
            ('CBG002', 'Cabang 2 - Surabaya', 'Jl. Pahlawan No. 2', 'Surabaya', '031-12345678', 'surabaya@narayana.com', 1)";
        
        $conn->exec($sql);
        echo "✅ 3 default branches created!<br>";
        echo "<ul>";
        echo "<li>HQ - Kantor Pusat (Jakarta)</li>";
        echo "<li>CBG001 - Cabang 1 Bandung</li>";
        echo "<li>CBG002 - Cabang 2 Surabaya</li>";
        echo "</ul>";
    } else {
        echo "ℹ️ Already have $count branches in database<br>";
    }
} catch(PDOException $e) {
    echo "⚠️ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>🎉 Installation Complete!</h2>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Go to <a href='modules/settings/users.php'>Settings → Kelola User</a> to create owner user</li>";
echo "<li>Select role 'Owner (Read-Only)' when creating user</li>";
echo "<li>Go to <a href='modules/settings/branches.php'>Settings → Kelola Cabang</a> to manage branches</li>";
echo "<li>Owner can login and access dashboard at <a href='modules/owner/dashboard.php'>Owner Dashboard</a></li>";
echo "</ol>";
echo "<br>";
echo "<a href='index.php' style='padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>🏠 Back to Dashboard</a>";
?>
