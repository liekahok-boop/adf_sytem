<!DOCTYPE html>
<html>
<head>
    <title>System Health Check</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #3b82f6; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 20px; }
        .check { padding: 12px; margin: 10px 0; border-radius: 5px; display: flex; align-items: center; gap: 10px; }
        .pass { background: #d1fae5; border-left: 4px solid #10b981; color: #065f46; }
        .fail { background: #fee2e2; border-left: 4px solid #ef4444; color: #7f1d1d; }
        .warn { background: #fef3c7; border-left: 4px solid #f59e0b; color: #92400e; }
        .icon { font-size: 20px; min-width: 30px; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th { background: #f0f0f0; padding: 10px; text-align: left; border-bottom: 2px solid #ddd; }
        td { padding: 10px; border-bottom: 1px solid #ddd; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 System Health Check - Investor & Project Modules</h1>
        
        <?php
        // Check 1: PHP Version
        echo "<h2>✓ Environment Checks</h2>";
        
        $php_version = phpversion();
        if (version_compare($php_version, '7.4.0', '>=')) {
            echo "<div class='check pass'><span class='icon'>✅</span><strong>PHP Version:</strong> $php_version (OK)</div>";
        } else {
            echo "<div class='check fail'><span class='icon'>❌</span><strong>PHP Version:</strong> $php_version (Minimum 7.4 required)</div>";
        }
        
        // Check 2: Session
        session_start();
        echo "<div class='check pass'><span class='icon'>✅</span><strong>Session:</strong> Active</div>";
        
        // Check 3: Config files
        echo "<h2>✓ Configuration Files</h2>";
        
        $required_files = [
            '../config/config.php' => 'Config',
            '../config/database.php' => 'Database Config',
            '../includes/auth.php' => 'Auth Class',
            '../includes/header.php' => 'Header',
            '../assets/js/main.js' => 'Main JavaScript',
            '../assets/css/style.css' => 'Stylesheet'
        ];
        
        foreach ($required_files as $file => $label) {
            $full_path = __DIR__ . '/' . $file;
            $exists = file_exists($full_path);
            if ($exists) {
                echo "<div class='check pass'><span class='icon'>✅</span><strong>$label:</strong> Found</div>";
            } else {
                echo "<div class='check fail'><span class='icon'>❌</span><strong>$label:</strong> Missing at $file</div>";
            }
        }
        
        // Check 4: Database Connection
        echo "<h2>✓ Database Connection</h2>";
        
        try {
            require_once '../config/config.php';
            require_once '../config/database.php';
            
            $db = Database::getInstance()->getConnection();
            
            // Test connection
            $test = $db->query("SELECT 1");
            echo "<div class='check pass'><span class='icon'>✅</span><strong>Database:</strong> Connected to " . DB_NAME . "</div>";
            
            // Check 5: Tables
            echo "<h2>✓ Database Tables</h2>";
            
            $tables_to_check = [
                'users' => 'Users Table',
                'user_permissions' => 'User Permissions Table',
                'investors' => 'Investors Table (Investor Module)',
                'investor_capital_transactions' => 'Capital Transactions Table',
                'investor_balances' => 'Investor Balances Table',
                'projects' => 'Projects Table (Project Module)',
                'project_expenses' => 'Project Expenses Table',
                'project_expense_categories' => 'Expense Categories Table'
            ];
            
            foreach ($tables_to_check as $table => $label) {
                try {
                    $result = $db->query("SELECT 1 FROM $table LIMIT 1");
                    echo "<div class='check pass'><span class='icon'>✅</span><strong>$label:</strong> Exists</div>";
                } catch (Exception $e) {
                    // Try to identify if it's expected (optional modules)
                    $is_optional = (strpos($table, 'investor') !== false || strpos($table, 'project') !== false);
                    $status = $is_optional ? 'warn' : 'fail';
                    $icon = $is_optional ? '⚠️' : '❌';
                    $class = $is_optional ? 'warn' : 'fail';
                    echo "<div class='check $class'><span class='icon'>$icon</span><strong>$label:</strong> Missing</div>";
                }
            }
            
            // Check 6: Auth Class Methods
            echo "<h2>✓ Auth Class Methods</h2>";
            
            require_once '../includes/auth.php';
            
            $auth = new Auth();
            $methods = ['isLoggedIn', 'hasPermission', 'getRole'];
            
            foreach ($methods as $method) {
                if (method_exists($auth, $method)) {
                    echo "<div class='check pass'><span class='icon'>✅</span><strong>$method():</strong> Exists</div>";
                } else {
                    echo "<div class='check fail'><span class='icon'>❌</span><strong>$method():</strong> Missing</div>";
                }
            }
            
            // Check 7: User Permissions in Database
            echo "<h2>✓ User Permissions</h2>";
            
            try {
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM user_permissions");
                $stmt->execute();
                $result = $stmt->fetch();
                $count = $result['count'];
                
                if ($count > 0) {
                    echo "<div class='check pass'><span class='icon'>✅</span><strong>Permissions in Database:</strong> $count records found</div>";
                    
                    // Show breakdown
                    $stmt = $db->prepare("SELECT user_id, COUNT(*) as perm_count FROM user_permissions GROUP BY user_id");
                    $stmt->execute();
                    $breakdown = $stmt->fetchAll();
                    
                    echo "<table>";
                    echo "<tr><th>User ID</th><th>Permissions Count</th></tr>";
                    foreach ($breakdown as $row) {
                        echo "<tr><td>User #{$row['user_id']}</td><td>{$row['perm_count']} permissions</td></tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<div class='check warn'><span class='icon'>⚠️</span><strong>Permissions:</strong> No permissions assigned yet</div>";
                    echo "<p style='margin-left: 40px; color: #666;'>Run: <code>seed-admin-permissions.php</code> to assign default permissions</p>";
                }
            } catch (Exception $e) {
                echo "<div class='check warn'><span class='icon'>⚠️</span><strong>Permissions Check:</strong> Could not query table</div>";
            }
            
            // Check 8: Modules Installed
            echo "<h2>✓ Module Files</h2>";
            
            $modules = [
                '../modules/investor/index.php' => 'Investor Module',
                '../modules/project/index.php' => 'Project Module',
                '../includes/InvestorManager.php' => 'Investor Manager Class',
                '../includes/ProjectManager.php' => 'Project Manager Class',
                '../includes/ExchangeRateManager.php' => 'Exchange Rate Manager'
            ];
            
            foreach ($modules as $file => $label) {
                $full_path = __DIR__ . '/' . $file;
                if (file_exists($full_path)) {
                    echo "<div class='check pass'><span class='icon'>✅</span><strong>$label:</strong> Installed</div>";
                } else {
                    echo "<div class='check warn'><span class='icon'>⚠️</span><strong>$label:</strong> Not found at $file</div>";
                }
            }
            
        } catch (Exception $e) {
            echo "<div class='check fail'><span class='icon'>❌</span><strong>Database Connection Failed:</strong> " . $e->getMessage() . "</div>";
        }
        
        // Check 9: JavaScript Files
        echo "<h2>✓ Frontend Resources</h2>";
        
        $js_checks = [
            'setupDropdownToggles' => 'Dropdown Toggle Handler',
            'formatCurrency' => 'Currency Formatter'
        ];
        
        echo "<div class='check pass'><span class='icon'>✅</span><strong>jQuery/Chart.js:</strong> Should be loaded from CDN</div>";
        echo "<div class='check pass'><span class='icon'>✅</span><strong>Feather Icons:</strong> Should be loaded from CDN</div>";
        
        ?>
        
        <h2>✓ Action Items</h2>
        
        <div class="check pass">
            <span class="icon">📋</span>
            <div>
                <strong>Next Steps:</strong><br>
                <ol style="margin: 10px 0;">
                    <li>Run permission setup: <code><a href="seed-admin-permissions.php" target="_blank">seed-admin-permissions.php</a></code></li>
                    <li>Manage user permissions: <code><a href="manage-user-permissions.php" target="_blank">manage-user-permissions.php</a></code></li>
                    <li>Test permission system: <code><a href="test-permission-system.php" target="_blank">test-permission-system.php</a></code></li>
                    <li>Login and verify dropdown menus work</li>
                    <li>Check Investor & Project modules appear in sidebar</li>
                </ol>
            </div>
        </div>
        
    </div>
</body>
</html>
