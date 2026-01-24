<?php
/**
 * ADF System Database Installer
 * Membuat struktur database lengkap dengan sample data
 */

define('APP_ACCESS', true);
require_once 'config/config.php';

// Koneksi langsung ke DB_NAME (adf_system)
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Database Installer</title>";
    echo "<style>
        body{font-family:Arial;padding:20px;background:#1e293b;color:#fff;}
        .container{max-width:800px;margin:0 auto;}
        .success{background:#10b981;padding:15px;border-radius:8px;margin:10px 0;}
        .error{background:#ef4444;padding:15px;border-radius:8px;margin:10px 0;}
        .info{background:#3b82f6;padding:15px;border-radius:8px;margin:10px 0;}
        .btn{display:inline-block;padding:10px 20px;background:#6366f1;color:white;text-decoration:none;border-radius:5px;margin:10px 0;}
        pre{background:#0f172a;padding:15px;border-radius:5px;overflow-x:auto;}
    </style></head><body><div class='container'>";
    
    echo "<h1>🚀 ADF System Database Installer</h1>";
    
    // Step 1: Create Database
    echo "<h2>Step 1: Creating Database</h2>";
    try {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<div class='success'>✅ Database '" . DB_NAME . "' created/exists</div>";
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    }
    
    // Step 2: Use Database
    echo "<h2>Step 2: Switching to Database</h2>";
    $pdo->exec("USE " . DB_NAME);
    echo "<div class='success'>✅ Using database: " . DB_NAME . "</div>";
    
    // Step 3: Create Tables
    echo "<h2>Step 3: Creating Tables</h2>";
    
    $sqlFile = __DIR__ . '/database.sql';
    if (file_exists($sqlFile)) {
        try {
            $sql = file_get_contents($sqlFile);
            
            // Split SQL statements
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    try {
                        $pdo->exec($statement);
                    } catch (Exception $e) {
                        // Ignore table already exists errors
                        if (strpos($e->getMessage(), 'already exists') === false) {
                            echo "<div class='error'>⚠️ " . htmlspecialchars(substr($e->getMessage(), 0, 100)) . "</div>";
                        }
                    }
                }
            }
            echo "<div class='success'>✅ Database tables created successfully</div>";
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error reading SQL file: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='error'>❌ database.sql file not found at: " . $sqlFile . "</div>";
    }
    
    // Step 4: Create Admin User
    echo "<h2>Step 4: Creating Admin User</h2>";
    try {
        // Check if admin exists
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE username = 'admin'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] == 0) {
            $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, full_name, email, role, is_active, business_access) 
                VALUES (?, ?, ?, ?, ?, 1, ?)
            ");
            $stmt->execute([
                'admin',
                $adminPassword,
                'Administrator',
                'admin@adf.local',
                'admin',
                json_encode([1, 2, 3, 4, 5, 6]) // All businesses
            ]);
            echo "<div class='success'>✅ Admin user created<br>";
            echo "<strong>Username:</strong> admin<br>";
            echo "<strong>Password:</strong> admin123<br>";
            echo "<strong>Role:</strong> admin</div>";
        } else {
            echo "<div class='info'>ℹ️ Admin user already exists</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    }
    
    // Step 5: Success
    echo "<h2>✅ Installation Complete!</h2>";
    echo "<div class='success'>
        <p>Database is ready to use.</p>
        <p><strong>Login Credentials:</strong></p>
        <pre>
Username: admin
Password: admin123
        </pre>
    </div>";
    
    echo "<div class='info'>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ol>";
    echo "<li><a href='login.php' class='btn'>Go to Login</a></li>";
    echo "<li>Login with admin credentials above</li>";
    echo "<li>Start using the system!</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "</div></body></html>";
    
} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>
