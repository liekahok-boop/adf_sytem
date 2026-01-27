<?php
/**
 * Fix Cashbook - Add branch_id column and update existing data
 */
define('APP_ACCESS', true);
require_once 'config/config.php';
require_once 'config/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

echo "<h2>Fix Cashbook Branch ID</h2>";
echo "<pre>";

try {
    // 1. Check if branch_id column exists
    echo "1. Checking if branch_id column exists in cash_book...\n";
    $checkColumn = $conn->query("SHOW COLUMNS FROM cash_book LIKE 'branch_id'");
    $columnExists = $checkColumn->rowCount() > 0;
    
    if (!$columnExists) {
        echo "   ❌ Column branch_id does not exist. Adding...\n";
        $conn->exec("ALTER TABLE cash_book ADD COLUMN branch_id VARCHAR(50) NOT NULL DEFAULT 'narayana-hotel' AFTER id");
        echo "   ✅ Column branch_id added successfully!\n";
    } else {
        echo "   ✅ Column branch_id already exists.\n";
    }
    
    // 2. Update all existing records to use default branch
    echo "\n2. Updating existing records with branch_id...\n";
    $stmt = $conn->prepare("UPDATE cash_book SET branch_id = 'narayana-hotel' WHERE branch_id = '' OR branch_id IS NULL");
    $stmt->execute();
    echo "   ✅ Updated " . $stmt->rowCount() . " records.\n";
    
    // 3. Check divisions
    echo "\n3. Checking divisions table...\n";
    $checkDivColumn = $conn->query("SHOW COLUMNS FROM divisions LIKE 'branch_id'");
    $divColumnExists = $checkDivColumn->rowCount() > 0;
    
    if (!$divColumnExists) {
        echo "   ❌ Column branch_id does not exist in divisions. Adding...\n";
        $conn->exec("ALTER TABLE divisions ADD COLUMN branch_id VARCHAR(50) NOT NULL DEFAULT 'narayana-hotel' AFTER id");
        echo "   ✅ Column branch_id added to divisions!\n";
    } else {
        echo "   ✅ Column branch_id already exists in divisions.\n";
    }
    
    // Update divisions
    $stmt = $conn->prepare("UPDATE divisions SET branch_id = 'narayana-hotel' WHERE branch_id = '' OR branch_id IS NULL");
    $stmt->execute();
    echo "   ✅ Updated " . $stmt->rowCount() . " division records.\n";
    
    // 4. Check categories
    echo "\n4. Checking categories table...\n";
    $checkCatColumn = $conn->query("SHOW COLUMNS FROM categories LIKE 'branch_id'");
    $catColumnExists = $checkCatColumn->rowCount() > 0;
    
    if (!$catColumnExists) {
        echo "   ❌ Column branch_id does not exist in categories. Adding...\n";
        $conn->exec("ALTER TABLE categories ADD COLUMN branch_id VARCHAR(50) NOT NULL DEFAULT 'narayana-hotel' AFTER id");
        echo "   ✅ Column branch_id added to categories!\n";
    } else {
        echo "   ✅ Column branch_id already exists in categories.\n";
    }
    
    // Update categories
    $stmt = $conn->prepare("UPDATE categories SET branch_id = 'narayana-hotel' WHERE branch_id = '' OR branch_id IS NULL");
    $stmt->execute();
    echo "   ✅ Updated " . $stmt->rowCount() . " category records.\n";
    
    // 5. Verify the fix
    echo "\n5. Verification:\n";
    $cashbookCount = $conn->query("SELECT COUNT(*) as count FROM cash_book WHERE branch_id = 'narayana-hotel'")->fetch()['count'];
    echo "   - Cash book records with branch_id: {$cashbookCount}\n";
    
    $divCount = $conn->query("SELECT COUNT(*) as count FROM divisions WHERE branch_id = 'narayana-hotel'")->fetch()['count'];
    echo "   - Divisions with branch_id: {$divCount}\n";
    
    $catCount = $conn->query("SELECT COUNT(*) as count FROM categories WHERE branch_id = 'narayana-hotel'")->fetch()['count'];
    echo "   - Categories with branch_id: {$catCount}\n";
    
    echo "\n✅ ALL DONE! Cashbook should now work properly.\n";
    echo "\n<a href='modules/cashbook/index.php' style='display:inline-block;padding:10px 20px;background:#4f46e5;color:white;text-decoration:none;border-radius:5px;'>Open Cashbook</a>\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
