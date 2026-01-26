<?php
/**
 * Setup Complete Menu Permissions
 * Jalankan file ini SEKALI untuk setup semua hak akses menu
 */

session_start();
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

$auth = new Auth();

// Check if user is admin
if (!$auth->isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
    die('<h2>❌ Admin access required!</h2><p>Login as admin first.</p>');
}

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<h1>🔐 Setting Up Complete Menu Permissions</h1>";
    echo "<p>Assigning permissions to all admin users...</p>";
    echo "<hr>";
    
    // Define all available permissions
    $permissions = [
        'dashboard' => 'Dashboard - Monitoring real-time accounting',
        'cashbook' => 'Cash Book - Buku Kas Besar',
        'divisions' => 'Divisions - Per Divisi',
        'frontdesk' => 'Front Desk - Hotel front desk operations',
        'sales_invoice' => 'Sales Invoice - Kelola invoice penjualan',
        'users' => 'Users - Kelola User & permissions',
        'reports' => 'Reports - Laporan keuangan',
        'procurement' => 'Procurement - PO & Suppliers',
        'settings' => 'Settings - Pengaturan sistem',
        'investor' => 'Investor - Manajemen investor & modal',
        'project' => 'Project - Manajemen project & pengeluaran'
    ];
    
    // Get all admin users
    $admin_query = "SELECT id, username, full_name FROM users WHERE role = 'admin'";
    $stmt = $db->prepare($admin_query);
    $stmt->execute();
    $admins = $stmt->fetchAll();
    
    if (empty($admins)) {
        echo "<p style='color: red;'>❌ No admin users found!</p>";
        echo "<p>Please create an admin user first.</p>";
        exit;
    }
    
    echo "<h3>Found " . count($admins) . " Admin User(s):</h3>";
    echo "<ul>";
    foreach ($admins as $admin) {
        echo "<li>{$admin['full_name']} ({$admin['username']})</li>";
    }
    echo "</ul>";
    echo "<hr>";
    
    // Create user_permissions table if not exists
    $create_table = "CREATE TABLE IF NOT EXISTS user_permissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        permission VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_permission (user_id, permission),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user (user_id),
        INDEX idx_permission (permission)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->exec($create_table);
    echo "<p>✓ user_permissions table ready</p>";
    
    // Assign all permissions to all admin users
    $count = 0;
    foreach ($admins as $admin) {
        foreach ($permissions as $perm => $desc) {
            $insert_query = "INSERT IGNORE INTO user_permissions (user_id, permission)
                            VALUES (?, ?)";
            $stmt = $db->prepare($insert_query);
            $stmt->execute([$admin['id'], $perm]);
            $count++;
        }
    }
    
    echo "<h3>✅ Permission Assignment Summary:</h3>";
    echo "<p>Total permissions assigned: <strong>$count</strong></p>";
    
    // Display assigned permissions
    echo "<h3>📋 All Available Permissions:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%; margin-top: 20px;'>";
    echo "<tr style='background: #333; color: white;'>";
    echo "<th>Permission Code</th>";
    echo "<th>Description</th>";
    echo "</tr>";
    
    foreach ($permissions as $code => $desc) {
        echo "<tr>";
        echo "<td><code>$code</code></td>";
        echo "<td>$desc</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verify permissions were set
    echo "<h3>✅ Verification:</h3>";
    foreach ($admins as $admin) {
        $verify_query = "SELECT COUNT(*) as count FROM user_permissions WHERE user_id = ?";
        $stmt = $db->prepare($verify_query);
        $stmt->execute([$admin['id']]);
        $result = $stmt->fetch();
        echo "<p>✓ {$admin['full_name']}: {$result['count']} permissions assigned</p>";
    }
    
    echo "<hr>";
    echo "<h2 style='color: green;'>✅ Setup Complete!</h2>";
    echo "<p>All admin users now have access to all menus.</p>";
    echo "<p><a href='" . BASE_URL . "/index.php'>← Back to Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Error!</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p><a href='javascript:history.back()'>← Go Back</a></p>";
}
?>
