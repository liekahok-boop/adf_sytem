<?php
/**
 * ALTER USER PREFERENCES - Add branch_id column
 */
define('APP_ACCESS', true);
require_once 'config/config.php';
require_once 'config/database.php';

$conn = Database::getInstance()->getConnection();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fix User Preferences Table</title>
    <style>
        body { font-family: monospace; background: #0f172a; color: #fff; padding: 20px; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
    </style>
</head>
<body>
<h1>Fix User Preferences Table</h1>
<pre>
<?php

try {
    echo "Checking user_preferences table...\n";
    
    // Check columns
    $columns = $conn->query('SHOW COLUMNS FROM user_preferences')->fetchAll(PDO::FETCH_COLUMN);
    echo "Current columns: " . implode(', ', $columns) . "\n\n";
    
    if (!in_array('branch_id', $columns)) {
        echo "<span class='success'>Adding branch_id column...</span>\n";
        $conn->exec('ALTER TABLE user_preferences ADD branch_id VARCHAR(50) NULL AFTER user_id');
        echo "<span class='success'>✅ Added!</span>\n\n";
        
        // Update existing records with default
        echo "Updating existing records...\n";
        $updated = $conn->exec('UPDATE user_preferences SET branch_id = "narayana-hotel" WHERE branch_id IS NULL');
        echo "<span class='success'>✅ Updated {$updated} records</span>\n\n";
        
        // Make it NOT NULL now
        echo "Making branch_id NOT NULL...\n";
        $conn->exec('ALTER TABLE user_preferences MODIFY branch_id VARCHAR(50) NOT NULL');
        echo "<span class='success'>✅ Done!</span>\n\n";
        
        // Add unique constraint
        echo "Fixing unique constraints...\n";
        try {
            $conn->exec('ALTER TABLE user_preferences DROP INDEX user_id');
            echo "Dropped old user_id index\n";
        } catch (Exception $e) {
            echo "No old index to drop\n";
        }
        
        $conn->exec('ALTER TABLE user_preferences ADD UNIQUE KEY unique_user_branch (user_id, branch_id)');
        echo "<span class='success'>✅ Added unique constraint (user_id, branch_id)</span>\n\n";
        
        echo "<span class='success'>════════════════════════════════════════</span>\n";
        echo "<span class='success'>  ✅ ALL DONE! TABLE FIXED!</span>\n";
        echo "<span class='success'>════════════════════════════════════════</span>\n";
    } else {
        echo "<span class='success'>✅ branch_id already exists!</span>\n";
        echo "\nVerifying structure...\n";
        
        // Show structure
        $structure = $conn->query('DESCRIBE user_preferences')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($structure as $col) {
            echo "  {$col['Field']}: {$col['Type']} {$col['Null']} {$col['Key']}\n";
        }
    }
    
    echo "\n<span class='success'>Table is ready for per-business theme!</span>\n";
    
} catch (Exception $e) {
    echo "<span class='error'>❌ ERROR: " . $e->getMessage() . "</span>\n";
}

?>
</pre>

<div style="margin-top: 30px;">
    <a href="direct-test-theme.php" style="padding: 15px 30px; background: #4f46e5; color: white; text-decoration: none; border-radius: 8px; font-weight: bold;">🔧 Test Theme Now</a>
    <a href="modules/settings/display.php" style="padding: 15px 30px; background: #059669; color: white; text-decoration: none; border-radius: 8px; margin-left: 10px; font-weight: bold;">⚙️ Display Settings</a>
</div>

</body>
</html>
