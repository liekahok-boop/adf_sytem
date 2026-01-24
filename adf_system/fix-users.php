<?php
require_once 'config/config.php';
require_once 'config/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Fix Users Table</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#1e293b;color:#fff;}
.success{background:#10b981;padding:15px;border-radius:8px;margin:10px 0;}
.error{background:#ef4444;padding:15px;border-radius:8px;margin:10px 0;}
.info{background:#3b82f6;padding:15px;border-radius:8px;margin:10px 0;}
.btn{display:inline-block;padding:10px 20px;background:#6366f1;color:white;text-decoration:none;border-radius:5px;margin:10px 5px 10px 0;}
</style></head><body>";

echo "<h2>🔧 Fix Users Table - Database: " . DB_NAME . "</h2><hr>";

try {
    // Step 1: Drop users table dengan force
    echo "<h3>Step 1: Force drop users table and tablespace</h3>";
    try {
        // Disable foreign key checks
        $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        // Try to discard tablespace first
        try {
            $conn->exec("ALTER TABLE users DISCARD TABLESPACE");
            echo "<div class='info'>ℹ️ Tablespace discarded</div>";
        } catch (Exception $e) {
            echo "<div class='info'>ℹ️ No tablespace to discard (this is OK)</div>";
        }
        
        // Drop table
        $conn->exec("DROP TABLE IF EXISTS users");
        echo "<div class='success'>✅ Users table dropped</div>";
        
        // Re-enable foreign key checks
        $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
        
    } catch (Exception $e) {
        echo "<div class='info'>ℹ️ " . $e->getMessage() . "</div>";
    }
    
    // Step 2: Create fresh users table
    echo "<h3>Step 2: Create fresh users table</h3>";
    $sql = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        full_name VARCHAR(100),
        role ENUM('admin', 'manager', 'staff') DEFAULT 'staff',
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_username (username),
        INDEX idx_role (role)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "<div class='success'>✅ Users table created successfully!</div>";
    
    // Step 3: Insert admin user
    echo "<h3>Step 3: Insert admin user</h3>";
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, email, full_name, role, is_active) 
            VALUES ('admin', ?, 'admin@narayana.com', 'Administrator', 'admin', 1)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$password]);
    
    echo "<div class='success'>✅ Admin user created successfully!</div>";
    
    // Step 4: Verify
    echo "<h3>Step 4: Verify user</h3>";
    $user = $db->fetchOne("SELECT username, email, full_name, role FROM users WHERE username = 'admin'");
    if ($user) {
        echo "<div class='info'>";
        echo "<strong>✓ Username:</strong> {$user['username']}<br>";
        echo "<strong>✓ Email:</strong> {$user['email']}<br>";
        echo "<strong>✓ Name:</strong> {$user['full_name']}<br>";
        echo "<strong>✓ Role:</strong> {$user['role']}<br>";
        echo "</div>";
    }
    
    // Step 5: Test password
    echo "<h3>Step 5: Test password verification</h3>";
    $userRow = $db->fetchAll("SELECT password FROM users WHERE username = 'admin'");
    if (!empty($userRow)) {
        $storedPassword = $userRow[0]['password'];
        if (password_verify('admin123', $storedPassword)) {
            echo "<div class='success'>✅ Password verification works!</div>";
        } else {
            echo "<div class='error'>❌ Password verification failed!</div>";
        }
    } else {
        echo "<div class='error'>❌ User not found!</div>";
    }
    
    echo "<hr>";
    echo "<div class='success' style='font-size:1.2em;'>";
    echo "<strong>🎉 Setup Complete!</strong><br><br>";
    echo "You can now login with:<br>";
    echo "<strong>Username:</strong> admin<br>";
    echo "<strong>Password:</strong> admin123<br>";
    echo "</div>";
    
    echo "<a href='index.php' class='btn' style='font-size:1.1em;'>🔑 Login Now</a>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Fatal Error: " . $e->getMessage() . "</div>";
    echo "<p>Error Details:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</body></html>";
?>
