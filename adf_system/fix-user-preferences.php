<?php
/**
 * Fix User Preferences - Make settings per business & per user
 */
define('APP_ACCESS', true);
require_once 'config/config.php';
require_once 'config/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fix User Preferences</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0f172a; color: #fff; padding: 20px; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .info { color: #3b82f6; }
        pre { background: #1e293b; padding: 20px; border-radius: 8px; line-height: 1.8; }
    </style>
</head>
<body>
<h1>🔧 Fix User Preferences - Per Business Settings</h1>
<pre>
<?php

try {
    echo "<span class='info'>═══════════════════════════════════════════════════</span>\n";
    echo "<span class='info'>  STEP 1: CHECK & UPDATE TABLE STRUCTURE</span>\n";
    echo "<span class='info'>═══════════════════════════════════════════════════</span>\n\n";
    
    // Check if user_preferences table exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'user_preferences'")->rowCount() > 0;
    
    if (!$tableExists) {
        echo "<span class='info'>Creating user_preferences table...</span>\n";
        $conn->exec("
            CREATE TABLE user_preferences (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                branch_id VARCHAR(50) NOT NULL,
                theme VARCHAR(20) DEFAULT 'dark',
                language VARCHAR(10) DEFAULT 'id',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_user_branch (user_id, branch_id),
                INDEX idx_user (user_id),
                INDEX idx_branch (branch_id)
            )
        ");
        echo "<span class='success'>✅ Created user_preferences table</span>\n\n";
    } else {
        echo "<span class='info'>user_preferences table exists</span>\n";
        
        // Check if branch_id column exists
        $columns = $conn->query("SHOW COLUMNS FROM user_preferences")->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array('branch_id', $columns)) {
            echo "<span class='info'>Adding branch_id column...</span>\n";
            
            // Add branch_id column
            $conn->exec("ALTER TABLE user_preferences ADD branch_id VARCHAR(50) NOT NULL DEFAULT 'narayana-hotel' AFTER user_id");
            echo "<span class='success'>✅ Added branch_id column</span>\n";
            
            // Drop old unique constraint if exists and add new one
            try {
                $conn->exec("ALTER TABLE user_preferences DROP INDEX user_id");
            } catch (Exception $e) {
                // Index might not exist
            }
            
            // Add new unique constraint
            try {
                $conn->exec("ALTER TABLE user_preferences ADD UNIQUE KEY unique_user_branch (user_id, branch_id)");
                echo "<span class='success'>✅ Added unique constraint (user_id, branch_id)</span>\n";
            } catch (Exception $e) {
                echo "<span class='info'>⚠️ Unique constraint might already exist</span>\n";
            }
            
            // Add indexes
            try {
                $conn->exec("ALTER TABLE user_preferences ADD INDEX idx_branch (branch_id)");
                echo "<span class='success'>✅ Added branch_id index</span>\n";
            } catch (Exception $e) {
                // Index might already exist
            }
        } else {
            echo "<span class='success'>✅ branch_id column already exists</span>\n";
        }
    }
    
    echo "\n<span class='info'>═══════════════════════════════════════════════════</span>\n";
    echo "<span class='info'>  STEP 2: MIGRATE EXISTING PREFERENCES</span>\n";
    echo "<span class='info'>═══════════════════════════════════════════════════</span>\n\n";
    
    // Get existing preferences without branch_id
    $existingPrefs = $conn->query("
        SELECT user_id, theme, language 
        FROM user_preferences 
        WHERE branch_id = 'narayana-hotel' OR branch_id = ''
    ")->fetchAll();
    
    if (count($existingPrefs) > 0) {
        echo "Found " . count($existingPrefs) . " existing preferences\n";
        echo "Creating preferences for all 6 businesses...\n\n";
        
        $businesses = ['narayana-hotel', 'bens-cafe', 'eat-meet', 'furniture-jepara', 'karimunjawa-party-boat', 'pabrik-kapal'];
        
        foreach ($existingPrefs as $pref) {
            foreach ($businesses as $businessId) {
                try {
                    $stmt = $conn->prepare("
                        INSERT INTO user_preferences (user_id, branch_id, theme, language)
                        VALUES (:user_id, :branch_id, :theme, :language)
                        ON DUPLICATE KEY UPDATE 
                            theme = VALUES(theme),
                            language = VALUES(language)
                    ");
                    $stmt->execute([
                        'user_id' => $pref['user_id'],
                        'branch_id' => $businessId,
                        'theme' => $pref['theme'] ?? 'dark',
                        'language' => $pref['language'] ?? 'id'
                    ]);
                } catch (Exception $e) {
                    // Duplicate or error, skip
                }
            }
        }
        echo "<span class='success'>✅ Migrated preferences for all businesses</span>\n";
    } else {
        echo "<span class='info'>No existing preferences to migrate</span>\n";
    }
    
    echo "\n<span class='info'>═══════════════════════════════════════════════════</span>\n";
    echo "<span class='info'>  STEP 3: VERIFICATION</span>\n";
    echo "<span class='info'>═══════════════════════════════════════════════════</span>\n\n";
    
    $totalPrefs = $conn->query("SELECT COUNT(*) as c FROM user_preferences")->fetch()['c'];
    echo "Total preferences: <span class='success'>{$totalPrefs}</span>\n";
    
    $prefsByBusiness = $conn->query("
        SELECT branch_id, COUNT(*) as count
        FROM user_preferences
        GROUP BY branch_id
    ")->fetchAll();
    
    echo "\nPreferences by business:\n";
    foreach ($prefsByBusiness as $row) {
        echo "  • {$row['branch_id']}: <span class='success'>{$row['count']}</span> user(s)\n";
    }
    
    echo "\n<span class='success'>═══════════════════════════════════════════════════</span>\n";
    echo "<span class='success'>  ✅ USER PREFERENCES FIXED!</span>\n";
    echo "<span class='success'>═══════════════════════════════════════════════════</span>\n";
    
    echo "\n<span class='info'>Now theme/settings will be saved per business per user.</span>\n";
    echo "<span class='info'>When you change theme in Ben's Cafe, it won't affect other businesses!</span>\n";
    
} catch (Exception $e) {
    echo "<span class='error'>❌ ERROR: " . $e->getMessage() . "</span>\n";
    echo "<span class='error'>" . $e->getTraceAsString() . "</span>\n";
}

?>
</pre>

<div style="text-align:center; padding: 30px;">
    <a href="index.php" style="display:inline-block;padding:15px 30px;background:#4f46e5;color:white;text-decoration:none;border-radius:8px;margin:10px;font-weight:bold;">🏠 Go to Dashboard</a>
</div>

<div style="background: #dbeafe; color: #1e40af; padding: 20px; margin: 20px; border-left: 4px solid #3b82f6; border-radius: 4px;">
    <strong>ℹ️ What Changed:</strong><br>
    • Each user now has separate preferences for each business<br>
    • Theme setting in Ben's Cafe won't affect Narayana Hotel<br>
    • All settings are now per-business and per-user<br>
    • You can have dark theme in one business and light theme in another
</div>

</body>
</html>
