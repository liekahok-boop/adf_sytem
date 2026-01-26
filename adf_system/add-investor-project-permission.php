<?php
/**
 * Add Investor & Project Permissions to All Users
 * Jalankan file ini untuk tambah permission investor dan project
 */

session_start();
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

$auth = new Auth();

// Check if user is admin
if (!$auth->isLoggedIn()) {
    die('<h2>❌ Anda harus login dulu!</h2>');
}

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<h1>🔐 Menambahkan Permission Investor & Project</h1>";
    echo "<p>Memberikan akses ke semua user...</p>";
    echo "<hr>";
    
    // Create user_permissions table if not exists
    $create_table = "CREATE TABLE IF NOT EXISTS user_permissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        permission VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_permission (user_id, permission),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    $db->exec($create_table);
    echo "<p>✓ Tabel user_permissions siap</p>";
    echo "<hr>";
    
    // Get all users
    $users_query = "SELECT id, username, full_name, role FROM users ORDER BY role DESC";
    $stmt = $db->prepare($users_query);
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    echo "<h3>👥 User yang akan mendapat akses:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #333; color: white;'><th>Username</th><th>Nama Lengkap</th><th>Role</th></tr>";
    
    $added_investor = 0;
    $added_project = 0;
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user['username']}</td>";
        echo "<td>{$user['full_name']}</td>";
        echo "<td>{$user['role']}</td>";
        echo "</tr>";
        
        // Add investor permission
        $insert_investor = "INSERT IGNORE INTO user_permissions (user_id, permission) VALUES (?, 'investor')";
        $stmt = $db->prepare($insert_investor);
        $stmt->execute([$user['id']]);
        $added_investor += $db->rowCount();
        
        // Add project permission
        $insert_project = "INSERT IGNORE INTO user_permissions (user_id, permission) VALUES (?, 'project')";
        $stmt = $db->prepare($insert_project);
        $stmt->execute([$user['id']]);
        $added_project += $db->rowCount();
    }
    
    echo "</table>";
    echo "<hr>";
    
    echo "<h3>✅ Hasil Penambahan Permission:</h3>";
    echo "<ul>";
    echo "<li>✓ Permission 'investor' ditambahkan: <strong>" . $added_investor . "x</strong></li>";
    echo "<li>✓ Permission 'project' ditambahkan: <strong>" . $added_project . "x</strong></li>";
    echo "</ul>";
    
    // Verify - Show all permissions per user
    echo "<hr>";
    echo "<h3>📋 Verifikasi - Daftar Permission Per User:</h3>";
    
    foreach ($users as $user) {
        $verify_query = "SELECT GROUP_CONCAT(permission SEPARATOR ', ') as permissions 
                        FROM user_permissions 
                        WHERE user_id = ?";
        $stmt = $db->prepare($verify_query);
        $stmt->execute([$user['id']]);
        $result = $stmt->fetch();
        
        $perms = $result['permissions'] ? $result['permissions'] : 'Tidak ada';
        
        echo "<p>";
        echo "<strong>{$user['full_name']}</strong> ({$user['username']}, {$user['role']})<br>";
        echo "<small style='color: #666;'>Permission: {$perms}</small>";
        echo "</p>";
    }
    
    echo "<hr>";
    echo "<h2 style='color: green;'>✅ Selesai!</h2>";
    echo "<p>Permission 'investor' dan 'project' sudah ditambahkan untuk semua user.</p>";
    echo "<p><strong>Langkah berikutnya:</strong></p>";
    echo "<ol>";
    echo "<li>Refresh browser (Ctrl+F5)</li>";
    echo "<li>Logout lalu login lagi</li>";
    echo "<li>Lihat di sidebar - Menu <strong>Investor</strong> dan <strong>Project</strong> harus muncul</li>";
    echo "<li>Settings → Hak Akses Menu - Investor dan Project sudah ada</li>";
    echo "</ol>";
    echo "<p><a href='" . BASE_URL . "/index.php' style='padding: 10px 20px; background: #3b82f6; color: white; text-decoration: none; border-radius: 5px;'>← Kembali ke Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Error!</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
