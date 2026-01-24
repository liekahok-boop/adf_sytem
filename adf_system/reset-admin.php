<?php
/**
 * Reset admin password & create tables if not exist
 */

// DIRECT CONNECTION - No config/database class that might auto-switch
try {
    // Use hardcoded values for adf_system database
    $pdo = new PDO(
        "mysql:host=localhost;dbname=adf_system;charset=utf8mb4",
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Create users table if not exists
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100),
        email VARCHAR(100),
        role ENUM('admin', 'manager', 'staff', 'owner') DEFAULT 'staff',
        is_active TINYINT(1) DEFAULT 1,
        business_access JSON DEFAULT NULL,
        phone VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_username (username),
        INDEX idx_role (role)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($createTableSQL);
    echo "✅ Users table ready<br>";
    
    // Delete existing admin
    $pdo->exec("DELETE FROM users WHERE username = 'admin'");
    echo "✅ Old admin removed<br>";
    
    // Password hash for 'admin123' using bcrypt
    // This is: password_hash('admin123', PASSWORD_DEFAULT)
    $passwordHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    
    // Insert new admin
    $stmt = $pdo->prepare("
        INSERT INTO users (username, password, full_name, email, role, is_active, business_access)
        VALUES (?, ?, ?, ?, ?, 1, ?)
    ");
    
    $stmt->execute([
        'admin',
        $passwordHash,
        'Administrator',
        'admin@adf.local',
        'admin',
        json_encode([1, 2, 3, 4, 5, 6])
    ]);
    
    echo "✅ Admin user created<br>";
    echo "<strong>Username:</strong> admin<br>";
    echo "<strong>Password:</strong> admin123<br>";
    echo "<br>";
    echo "<a href='login.php' class='btn'>Go to Login →</a>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Admin</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #1e293b; color: #fff; }
        .btn { display: inline-block; padding: 10px 20px; background: #6366f1; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
    <h2>Admin Account Setup</h2>
</body>
</html>
