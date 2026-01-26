<?php
/**
 * Seed Admin Permissions - Auto-assign all permissions to admin user
 * Jalankan 1x untuk setup awal
 */

session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();

// Cek apakah sudah login
if (!$auth->isLoggedIn()) {
    // Untuk install pertama kali, bypass login check
    $bypass_login = isset($_GET['bypass']) && $_GET['bypass'] === 'install_first_time';
    if (!$bypass_login) {
        die('<h2>❌ Anda harus login dulu!</h2><p><a href="' . BASE_URL . '/login.php">Login di sini</a></p>');
    }
}

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<h1>🔐 Seed Admin Permissions</h1>";
    
    // Create user_permissions table if not exists
    $create_table_sql = "CREATE TABLE IF NOT EXISTS user_permissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        permission VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_permission (user_id, permission),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    $db->exec($create_table_sql);
    echo "<p>✓ Tabel user_permissions siap</p>";
    
    // Define all permissions
    $all_permissions = [
        'dashboard',
        'cashbook',
        'divisions',
        'frontdesk',
        'sales_invoice',
        'users',
        'reports',
        'procurement',
        'settings',
        'investor',
        'project'
    ];
    
    // Find admin user
    $admin_query = "SELECT id FROM users WHERE role = 'admin' LIMIT 1";
    $stmt = $db->prepare($admin_query);
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if (!$admin) {
        die("<p>❌ Admin user tidak ditemukan di database!</p>");
    }
    
    $admin_id = $admin['id'];
    
    // Clear existing permissions for admin
    $clear_stmt = $db->prepare("DELETE FROM user_permissions WHERE user_id = ?");
    $clear_stmt->execute([$admin_id]);
    
    // Insert all permissions
    $insert_stmt = $db->prepare("INSERT INTO user_permissions (user_id, permission) VALUES (?, ?)");
    
    $inserted_count = 0;
    foreach ($all_permissions as $perm) {
        try {
            $insert_stmt->execute([$admin_id, $perm]);
            $inserted_count++;
        } catch (Exception $e) {
            // Duplicate, skip
        }
    }
    
    echo "<h2 style='color: green;'>✅ Selesai!</h2>";
    echo "<p>Admin user mendapat akses ke <strong>" . $inserted_count . " menu</strong>:</p>";
    echo "<ul>";
    foreach ($all_permissions as $perm) {
        echo "<li>✓ " . ucfirst(str_replace('_', ' ', $perm)) . "</li>";
    }
    echo "</ul>";
    
    echo "<hr>";
    echo "<p><strong>Langkah berikutnya:</strong></p>";
    echo "<ol>";
    echo "<li>Buka: <a href='" . BASE_URL . "/manage-user-permissions.php' target='_blank'><strong>Manage User Permissions</strong></a></li>";
    echo "<li>Assign permissions ke user lain sesuai kebutuhan</li>";
    echo "<li>Refresh browser dan login ulang</li>";
    echo "</ol>";
    
    echo "<p><a href='" . BASE_URL . "/index.php' style='padding: 10px 20px; background: #3b82f6; color: white; text-decoration: none; border-radius: 5px;'>← Kembali ke Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Error!</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
