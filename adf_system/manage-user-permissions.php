<?php
/**
 * Manage User Permissions - Pilih User untuk Setiap Menu
 * Hanya admin yang bisa akses
 */

session_start();
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

$auth = new Auth();

// Check if user is admin
if (!$auth->isLoggedIn() || $auth->getRole() !== 'admin') {
    http_response_code(403);
    die('<h2>❌ Hanya Admin yang dapat mengakses halaman ini!</h2>');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Create user_permissions table if not exists
    $create_table_sql = "CREATE TABLE IF NOT EXISTS user_permissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        permission VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_permission (user_id, permission),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    try {
        $db->exec($create_table_sql);
    } catch (Exception $e) {
        // Table might already exist
    }
    
    // List of all permissions
    $permissions = [
        'dashboard' => '📊 Dashboard',
        'cashbook' => '📔 Buku Kas',
        'divisions' => '🏢 Divisi',
        'frontdesk' => '🚪 Front Desk',
        'sales_invoice' => '🧾 Sales Invoice',
        'users' => '👥 Kelola User',
        'reports' => '📈 Laporan',
        'procurement' => '📦 Procurement',
        'settings' => '⚙️ Settings',
        'investor' => '💼 Investor',
        'project' => '🎯 Project'
    ];
    
    // Get all users
    $users_query = "SELECT id, username, full_name, role FROM users ORDER BY role DESC, full_name ASC";
    $stmt = $db->prepare($users_query);
    $stmt->execute();
    $all_users = $stmt->fetchAll();
    
    // Get current permissions
    $current_perms = [];
    $perms_query = "SELECT user_id, GROUP_CONCAT(permission) as permissions FROM user_permissions GROUP BY user_id";
    $stmt = $db->prepare($perms_query);
    $stmt->execute();
    $perms_result = $stmt->fetchAll();
    
    foreach ($perms_result as $perm) {
        $current_perms[$perm['user_id']] = explode(',', $perm['permissions']);
    }
    
    // Handle form submission
    $message = '';
    $message_type = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Clear all permissions first
            $clear_query = "DELETE FROM user_permissions";
            $db->exec($clear_query);
            
            // Add selected permissions
            $insert_query = "INSERT INTO user_permissions (user_id, permission) VALUES (?, ?)";
            $stmt = $db->prepare($insert_query);
            
            $total_added = 0;
            
            foreach ($all_users as $user) {
                $user_id = $user['id'];
                $user_perms = isset($_POST['permissions'][$user_id]) ? $_POST['permissions'][$user_id] : [];
                
                foreach ($user_perms as $perm) {
                    if (isset($permissions[$perm])) {
                        $stmt->execute([$user_id, $perm]);
                        $total_added++;
                    }
                }
            }
            
            $message = "✅ Permission berhasil diperbarui! (" . $total_added . " permission ditambahkan)";
            $message_type = "success";
            
            // Refresh to get updated data
            header("Location: manage-user-permissions.php");
            exit;
            
        } catch (Exception $e) {
            $message = "❌ Error: " . $e->getMessage();
            $message_type = "error";
        }
    }
    
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User Permissions</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header h1 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .header p {
            color: #666;
            font-size: 0.95rem;
        }
        
        .message {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        
        .message.success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .message.error {
            background: #fee2e2;
            color: #7f1d1d;
            border-left: 4px solid #ef4444;
        }
        
        .form-wrapper {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #f3f4f6;
            border-bottom: 2px solid #e5e7eb;
        }
        
        th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #374151;
            font-size: 0.9rem;
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        tr:hover {
            background: #f9fafb;
        }
        
        .user-cell {
            font-weight: 600;
            color: #333;
        }
        
        .user-info {
            font-size: 0.85rem;
            color: #666;
        }
        
        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: #dbeafe;
            color: #1e40af;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .role-badge.admin {
            background: #fecaca;
            color: #991b1b;
        }
        
        .permission-cell {
            max-width: 500px;
        }
        
        .permission-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        
        .permission-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            background: #f3f4f6;
            border-radius: 6px;
        }
        
        .permission-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #667eea;
        }
        
        .permission-item label {
            cursor: pointer;
            user-select: none;
            font-size: 0.9rem;
        }
        
        .form-actions {
            padding: 2rem;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }
        
        button {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }
        
        .btn-secondary:hover {
            background: #d1d5db;
        }
        
        .select-all-row {
            background: #f0f9ff;
        }
        
        .select-all-row th {
            background: #e0f2fe;
            font-weight: 700;
        }
        
        .quick-actions {
            padding: 1rem;
            background: #f0f9ff;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .quick-actions button {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            background: #dbeafe;
            color: #1e40af;
        }
        
        .quick-actions button:hover {
            background: #bfdbfe;
        }
        
        .menu-section {
            margin-bottom: 2rem;
        }
        
        .menu-section h3 {
            color: #667eea;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
            padding-left: 0.5rem;
        }
        
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🔐 Kelola User Permissions</h1>
            <p>Pilih user mana yang dapat mengakses setiap menu</p>
        </div>
        
        <!-- Message -->
        <?php if ($message): ?>
        <div class="message <?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <!-- Form -->
        <form method="POST" class="form-wrapper">
            <!-- Quick Actions -->
            <div class="quick-actions">
                <button type="button" class="select-all-for-user" onclick="selectAllForUser(this)">
                    ✓ Pilih Semua User untuk Menu
                </button>
                <button type="button" class="clear-all-user" onclick="clearAllUsers(this)">
                    ✕ Hapus Semua User
                </button>
            </div>
            
            <!-- Table -->
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 200px;">👤 User</th>
                            <?php foreach ($permissions as $key => $label): ?>
                            <th style="width: 120px; text-align: center;"><?php echo $label; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_users as $user): ?>
                        <tr data-user-id="<?php echo $user['id']; ?>">
                            <td>
                                <div class="user-cell"><?php echo $user['full_name']; ?></div>
                                <div class="user-info">
                                    @<?php echo $user['username']; ?> 
                                    <span class="role-badge <?php echo strtolower($user['role']); ?>">
                                        <?php echo strtoupper($user['role']); ?>
                                    </span>
                                </div>
                            </td>
                            
                            <?php foreach ($permissions as $key => $label): ?>
                            <td style="text-align: center;">
                                <input 
                                    type="checkbox" 
                                    name="permissions[<?php echo $user['id']; ?>][]" 
                                    value="<?php echo $key; ?>"
                                    <?php echo isset($current_perms[$user['id']]) && in_array($key, $current_perms[$user['id']]) ? 'checked' : ''; ?>
                                    class="permission-check"
                                >
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Form Actions -->
            <div class="form-actions">
                <a href="<?php echo BASE_URL; ?>/index.php" class="btn-secondary">← Kembali</a>
                <button type="submit" class="btn-primary">💾 Simpan Permission</button>
            </div>
        </form>
    </div>
    
    <script>
        // Select all users for a specific menu
        function selectAllForUser(btn) {
            const checkboxes = document.querySelectorAll('.permission-check');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            
            checkboxes.forEach(cb => {
                cb.checked = !allChecked;
            });
            
            btn.textContent = !allChecked ? '✕ Hapus Semua User' : '✓ Pilih Semua User untuk Menu';
        }
        
        // Clear all users
        function clearAllUsers(btn) {
            const checkboxes = document.querySelectorAll('.permission-check');
            checkboxes.forEach(cb => cb.checked = false);
            
            document.querySelector('.select-all-for-user').textContent = '✓ Pilih Semua User untuk Menu';
        }
    </script>
</body>
</html>

<?php
} catch (Exception $e) {
    die('<h2 style="color: red;">Error: ' . $e->getMessage() . '</h2>');
}
?>
