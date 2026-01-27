<?php
/**
 * Quick Fix Cashbook - Run SQL directly
 */
define('APP_ACCESS', true);
require_once 'config/config.php';
require_once 'config/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

echo "<h2>Quick Fix Cashbook</h2><pre>";

try {
    // Add branch_id to cash_book if not exists
    echo "1. Adding branch_id to cash_book...\n";
    $conn->exec("ALTER TABLE cash_book ADD COLUMN IF NOT EXISTS branch_id VARCHAR(50) NOT NULL DEFAULT 'narayana-hotel' AFTER id");
    
    // Update all cash_book records
    echo "2. Updating cash_book records...\n";
    $stmt = $conn->exec("UPDATE cash_book SET branch_id = 'narayana-hotel' WHERE branch_id IS NULL OR branch_id = ''");
    echo "   Updated records: {$stmt}\n";
    
    // Add branch_id to divisions if not exists
    echo "3. Fixing divisions...\n";
    $conn->exec("ALTER TABLE divisions ADD COLUMN IF NOT EXISTS branch_id VARCHAR(50) NOT NULL DEFAULT 'narayana-hotel' AFTER id");
    $conn->exec("UPDATE divisions SET branch_id = 'narayana-hotel' WHERE branch_id IS NULL OR branch_id = ''");
    
    // Add branch_id to categories if not exists
    echo "4. Fixing categories...\n";
    $conn->exec("ALTER TABLE categories ADD COLUMN IF NOT EXISTS branch_id VARCHAR(50) NOT NULL DEFAULT 'narayana-hotel' AFTER id");
    $conn->exec("UPDATE categories SET branch_id = 'narayana-hotel' WHERE branch_id IS NULL OR branch_id = ''");
    
    echo "\n✅ DONE!\n\n";
    
    // Test query
    echo "Testing query:\n";
    $test = $conn->query("SELECT COUNT(*) as c FROM cash_book WHERE branch_id = 'narayana-hotel'")->fetch();
    echo "Records found: " . $test['c'] . "\n";
    
    if ($test['c'] > 0) {
        echo "\n✅ Success! Redirecting to cashbook...\n";
        echo "<script>setTimeout(function(){ window.location.href='modules/cashbook/index.php?date=all'; }, 2000);</script>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    // Try alternative method for MySQL < 8.0
    try {
        echo "\nTrying alternative method...\n";
        
        // Check if column exists first
        $checkCB = $conn->query("SHOW COLUMNS FROM cash_book LIKE 'branch_id'")->rowCount();
        if ($checkCB == 0) {
            $conn->exec("ALTER TABLE cash_book ADD branch_id VARCHAR(50) NOT NULL DEFAULT 'narayana-hotel' AFTER id");
            echo "✅ Added branch_id to cash_book\n";
        }
        $conn->exec("UPDATE cash_book SET branch_id = 'narayana-hotel'");
        
        $checkDiv = $conn->query("SHOW COLUMNS FROM divisions LIKE 'branch_id'")->rowCount();
        if ($checkDiv == 0) {
            $conn->exec("ALTER TABLE divisions ADD branch_id VARCHAR(50) NOT NULL DEFAULT 'narayana-hotel' AFTER id");
            echo "✅ Added branch_id to divisions\n";
        }
        $conn->exec("UPDATE divisions SET branch_id = 'narayana-hotel'");
        
        $checkCat = $conn->query("SHOW COLUMNS FROM categories LIKE 'branch_id'")->rowCount();
        if ($checkCat == 0) {
            $conn->exec("ALTER TABLE categories ADD branch_id VARCHAR(50) NOT NULL DEFAULT 'narayana-hotel' AFTER id");
            echo "✅ Added branch_id to categories\n";
        }
        $conn->exec("UPDATE categories SET branch_id = 'narayana-hotel'");
        
        echo "\n✅ ALL FIXED! Redirecting...\n";
        echo "<script>setTimeout(function(){ window.location.href='modules/cashbook/index.php?date=all'; }, 2000);</script>";
        
    } catch (Exception $e2) {
        echo "❌ Error: " . $e2->getMessage() . "\n";
    }
}

echo "</pre>";
?>
