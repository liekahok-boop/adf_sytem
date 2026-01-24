<?php
require_once 'config/config.php';
require_once 'config/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Setup Admin</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#1e293b;color:#fff;}
.success{background:#10b981;padding:15px;border-radius:8px;margin:10px 0;}
.error{background:#ef4444;padding:15px;border-radius:8px;margin:10px 0;}
table{width:100%;border-collapse:collapse;margin:20px 0;background:#334155;}
th,td{padding:10px;border:1px solid #475569;text-align:left;}
th{background:#475569;}
.btn{display:inline-block;padding:10px 20px;background:#6366f1;color:white;text-decoration:none;border-radius:5px;margin:10px 0;}
</style></head><body>";

echo "<h2>🔧 Setup User Admin - Narayana DB</h2>";
echo "<p>Database: <strong>" . DB_NAME . "</strong></p><hr>";

// Reset password untuk user admin
if (isset($_GET['reset'])) {
    try {
        $newPassword = 'admin123';
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $sql = "UPDATE users SET password = ? WHERE username = 'admin'";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$hashedPassword]);
        
        if ($stmt->rowCount() > 0) {
            echo "<div class='success'>✅ Password admin berhasil di-reset!<br>";
            echo "<strong>Username:</strong> admin<br>";
            echo "<strong>Password:</strong> admin123</div>";
        } else {
            echo "<div class='error'>❌ User admin tidak ditemukan!</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    }
}

// Tampilkan semua user
echo "<h3>📋 Daftar User di Database:</h3>";
try {
    $users = $db->fetchAll("SELECT id, username, email, full_name, role, is_active, created_at FROM users");
    
    if (count($users) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Nama Lengkap</th><th>Role</th><th>Active</th><th>Created</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td><strong>{$user['username']}</strong></td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['full_name']}</td>";
            echo "<td>{$user['role']}</td>";
            echo "<td>" . ($user['is_active'] ? '✅ Aktif' : '❌ Nonaktif') . "</td>";
            echo "<td>{$user['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<a href='?reset=1' class='btn'>🔄 Reset Password Admin</a> ";
        echo "<a href='index.php' class='btn'>🔑 Login Sekarang</a>";
        
    } else {
        echo "<div class='error'>❌ Tidak ada user di database!</div>";
        echo "<p>Membuat user admin baru...</p>";
        
        // Create admin user
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, email, full_name, role, is_active) 
                VALUES ('admin', ?, 'admin@narayana.com', 'Administrator', 'admin', 1)";
        $conn->prepare($sql)->execute([$password]);
        
        echo "<div class='success'>✅ User admin berhasil dibuat!<br>";
        echo "<strong>Username:</strong> admin<br>";
        echo "<strong>Password:</strong> admin123</div>";
        echo "<a href='index.php' class='btn'>🔑 Login Sekarang</a>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    
    // Cek apakah tabel users ada
    echo "<p>Mencoba membuat tabel users...</p>";
    try {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100),
            full_name VARCHAR(100),
            role ENUM('admin', 'manager', 'staff') DEFAULT 'staff',
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->exec($sql);
        echo "<div class='success'>✅ Tabel users berhasil dibuat!</div>";
        
        // Insert admin
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, email, full_name, role, is_active) 
                VALUES ('admin', ?, 'admin@narayana.com', 'Administrator', 'admin', 1)";
        $conn->prepare($sql)->execute([$password]);
        
        echo "<div class='success'>✅ User admin berhasil dibuat!<br>";
        echo "<strong>Username:</strong> admin<br>";
        echo "<strong>Password:</strong> admin123</div>";
        echo "<a href='setup-admin.php' class='btn'>🔄 Refresh</a> ";
        echo "<a href='index.php' class='btn'>🔑 Login Sekarang</a>";
        
    } catch (Exception $e2) {
        echo "<div class='error'>❌ Error membuat tabel: " . $e2->getMessage() . "</div>";
    }
}

echo "</body></html>";
?>
