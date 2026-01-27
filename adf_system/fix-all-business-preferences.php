<?php
/**
 * Fix User Preferences for ALL BUSINESSES
 */
define('APP_ACCESS', true);
require_once 'config/config.php';

header('Content-Type: text/html; charset=utf-8');

// All business databases
$businesses = [
    'narayana-hotel' => 'adf_narayana_hotel',
    'bens-cafe' => 'adf_bens_cafe',
    'eat-meet' => 'adf_eat_meet',
    'furniture-jepara' => 'adf_furniture_jepara',
    'karimunjawa-party-boat' => 'adf_karimunjawa_party_boat',
    'pabrik-kapal' => 'adf_pabrik_kapal'
];

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fix All Business Databases</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0f172a; color: #fff; padding: 20px; }
        .business { background: #1e293b; padding: 20px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #4f46e5; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .info { color: #3b82f6; }
        pre { margin: 10px 0; line-height: 1.6; }
    </style>
</head>
<body>
<h1>🔧 Fix User Preferences for ALL Businesses</h1>

<?php
foreach ($businesses as $businessId => $dbName) {
    echo "<div class='business'>";
    echo "<h2>{$businessId}</h2>";
    echo "<pre>";
    
    try {
        // Connect to specific business database
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname={$dbName};charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        echo "<span class='info'>Connected to database: {$dbName}</span>\n";
        
        // Check if user_preferences table exists
        $tableExists = $conn->query("SHOW TABLES LIKE 'user_preferences'")->rowCount() > 0;
        
        if (!$tableExists) {
            echo "<span class='info'>Creating user_preferences table...</span>\n";
            $conn->exec("
                CREATE TABLE user_preferences (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    branch_id VARCHAR(50) NOT NULL DEFAULT '{$businessId}',
                    theme VARCHAR(20) DEFAULT 'dark',
                    language VARCHAR(10) DEFAULT 'id',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_user_branch (user_id, branch_id),
                    INDEX idx_user (user_id),
                    INDEX idx_branch (branch_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            echo "<span class='success'>✅ Table created!</span>\n";
        } else {
            echo "<span class='success'>✅ Table exists</span>\n";
            
            // Check if branch_id column exists
            $columns = $conn->query("SHOW COLUMNS FROM user_preferences")->fetchAll(PDO::FETCH_COLUMN);
            
            if (!in_array('branch_id', $columns)) {
                echo "<span class='info'>Adding branch_id column...</span>\n";
                $conn->exec("ALTER TABLE user_preferences ADD branch_id VARCHAR(50) NULL AFTER user_id");
                $conn->exec("UPDATE user_preferences SET branch_id = '{$businessId}' WHERE branch_id IS NULL");
                $conn->exec("ALTER TABLE user_preferences MODIFY branch_id VARCHAR(50) NOT NULL");
                
                // Fix unique constraint
                try {
                    $conn->exec("ALTER TABLE user_preferences DROP INDEX user_id");
                } catch (Exception $e) {}
                
                $conn->exec("ALTER TABLE user_preferences ADD UNIQUE KEY unique_user_branch (user_id, branch_id)");
                echo "<span class='success'>✅ Added branch_id and fixed constraints!</span>\n";
            } else {
                echo "<span class='success'>✅ branch_id exists</span>\n";
            }
        }
        
        // Show current records
        $count = $conn->query("SELECT COUNT(*) FROM user_preferences")->fetchColumn();
        echo "\n<span class='info'>Total preferences: {$count}</span>\n";
        
        if ($count > 0) {
            $sample = $conn->query("SELECT user_id, branch_id, theme FROM user_preferences LIMIT 3")->fetchAll();
            echo "Sample data:\n";
            foreach ($sample as $row) {
                echo "  User {$row['user_id']}: {$row['branch_id']} → {$row['theme']}\n";
            }
        }
        
    } catch (PDOException $e) {
        echo "<span class='error'>❌ Error: " . $e->getMessage() . "</span>\n";
        if (strpos($e->getMessage(), '1049') !== false) {
            echo "<span class='info'>ℹ️  Database doesn't exist yet - will be created when business is accessed</span>\n";
        }
    }
    
    echo "</pre>";
    echo "</div>";
}
?>

<div style="background: #10b981; color: white; padding: 20px; margin: 20px 0; border-radius: 8px; text-align: center; font-weight: bold; font-size: 18px;">
    ✅ ALL BUSINESSES PROCESSED!
</div>

<div style="margin-top: 30px; text-align: center;">
    <a href="index.php" style="padding: 15px 30px; background: #4f46e5; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 0 10px;">🏠 Dashboard</a>
    <a href="modules/settings/display.php" style="padding: 15px 30px; background: #059669; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 0 10px;">⚙️ Display Settings</a>
</div>

<div style="background: #fef3c7; color: #92400e; padding: 20px; margin: 20px 0; border-radius: 8px;">
    <strong>⚠️ Next Steps:</strong><br>
    1. Switch to different businesses using the dropdown<br>
    2. Go to Settings → Display Settings<br>
    3. Change theme and save<br>
    4. Press Ctrl+Shift+R to refresh<br>
    5. Each business should now remember its own theme!
</div>

</body>
</html>
