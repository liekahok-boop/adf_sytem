<?php
/**
 * Final Fix - Add branch_id and update all records
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
    <title>Final Fix</title>
    <style>
        body { font-family: monospace; background: #0f172a; color: #fff; padding: 20px; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .info { color: #3b82f6; }
        .btn { display: inline-block; padding: 15px 30px; background: #4f46e5; color: white; text-decoration: none; border-radius: 8px; margin: 10px 5px; font-weight: bold; }
        .btn:hover { background: #4338ca; }
    </style>
</head>
<body>
<h1>🔧 Final Database Fix</h1>
<pre>
<?php

try {
    echo "<span class='info'>═══════════════════════════════════════</span>\n";
    echo "<span class='info'>  STEP 1: ALTER TABLES</span>\n";
    echo "<span class='info'>═══════════════════════════════════════</span>\n\n";
    
    // Check and add branch_id to cash_book
    $columns = $conn->query("SHOW COLUMNS FROM cash_book")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('branch_id', $columns)) {
        $conn->exec("ALTER TABLE cash_book ADD branch_id VARCHAR(50) NULL AFTER id");
        echo "<span class='success'>✅ Added branch_id to cash_book</span>\n";
    } else {
        echo "<span class='info'>ℹ️  branch_id already exists in cash_book</span>\n";
    }
    
    // Check and add branch_id to divisions
    $divColumns = $conn->query("SHOW COLUMNS FROM divisions")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('branch_id', $divColumns)) {
        $conn->exec("ALTER TABLE divisions ADD branch_id VARCHAR(50) NULL AFTER id");
        echo "<span class='success'>✅ Added branch_id to divisions</span>\n";
    } else {
        echo "<span class='info'>ℹ️  branch_id already exists in divisions</span>\n";
    }
    
    // Check and add branch_id to categories
    $catColumns = $conn->query("SHOW COLUMNS FROM categories")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('branch_id', $catColumns)) {
        $conn->exec("ALTER TABLE categories ADD branch_id VARCHAR(50) NULL AFTER id");
        echo "<span class='success'>✅ Added branch_id to categories</span>\n";
    } else {
        echo "<span class='info'>ℹ️  branch_id already exists in categories</span>\n";
    }
    
    echo "\n<span class='info'>═══════════════════════════════════════</span>\n";
    echo "<span class='info'>  STEP 2: UPDATE ALL RECORDS</span>\n";
    echo "<span class='info'>═══════════════════════════════════════</span>\n\n";
    
    // Update cash_book
    $stmt = $conn->prepare("UPDATE cash_book SET branch_id = 'narayana-hotel' WHERE branch_id IS NULL OR branch_id = ''");
    $stmt->execute();
    $cbUpdated = $stmt->rowCount();
    echo "<span class='success'>✅ Updated {$cbUpdated} cash_book records</span>\n";
    
    // Update divisions
    $stmt = $conn->prepare("UPDATE divisions SET branch_id = 'narayana-hotel' WHERE branch_id IS NULL OR branch_id = ''");
    $stmt->execute();
    $divUpdated = $stmt->rowCount();
    echo "<span class='success'>✅ Updated {$divUpdated} division records</span>\n";
    
    // Update categories
    $stmt = $conn->prepare("UPDATE categories SET branch_id = 'narayana-hotel' WHERE branch_id IS NULL OR branch_id = ''");
    $stmt->execute();
    $catUpdated = $stmt->rowCount();
    echo "<span class='success'>✅ Updated {$catUpdated} category records</span>\n";
    
    echo "\n<span class='info'>═══════════════════════════════════════</span>\n";
    echo "<span class='info'>  STEP 3: VERIFICATION</span>\n";
    echo "<span class='info'>═══════════════════════════════════════</span>\n\n";
    
    // Verify cashbook
    $cbCheck = $conn->query("SELECT COUNT(*) as c FROM cash_book WHERE branch_id = 'narayana-hotel'")->fetch();
    echo "Cash Book Records: <span class='success'>{$cbCheck['c']}</span>\n";
    
    // Verify divisions
    $divCheck = $conn->query("SELECT COUNT(*) as c FROM divisions WHERE branch_id = 'narayana-hotel'")->fetch();
    echo "Divisions: <span class='success'>{$divCheck['c']}</span>\n";
    
    // Verify categories
    $catCheck = $conn->query("SELECT COUNT(*) as c FROM categories WHERE branch_id = 'narayana-hotel'")->fetch();
    echo "Categories: <span class='success'>{$catCheck['c']}</span>\n";
    
    if ($cbCheck['c'] > 0) {
        echo "\n<span class='info'>Sample Data:</span>\n";
        $samples = $conn->query("
            SELECT cb.id, cb.transaction_date, cb.branch_id, d.division_name, cb.amount
            FROM cash_book cb
            LEFT JOIN divisions d ON cb.division_id = d.id
            WHERE cb.branch_id = 'narayana-hotel'
            ORDER BY cb.id DESC
            LIMIT 3
        ")->fetchAll();
        
        foreach ($samples as $s) {
            echo "  ID: {$s['id']}, Date: {$s['transaction_date']}, Branch: {$s['branch_id']}, ";
            echo "Division: {$s['division_name']}, Amount: " . number_format($s['amount'], 0) . "\n";
        }
    }
    
    echo "\n<span class='success'>═══════════════════════════════════════</span>\n";
    echo "<span class='success'>  ✅ ALL FIXES COMPLETED!</span>\n";
    echo "<span class='success'>═══════════════════════════════════════</span>\n";
    
} catch (Exception $e) {
    echo "<span class='error'>❌ ERROR: " . $e->getMessage() . "</span>\n";
    echo "<span class='error'>" . $e->getTraceAsString() . "</span>\n";
}

?>
</pre>

<div style="text-align:center; padding: 30px;">
    <a href="modules/cashbook/index.php?month=2026-01" class="btn">📊 Open Cashbook (January)</a>
    <a href="modules/cashbook/index.php?date=all" class="btn">📊 Open Cashbook (All)</a>
    <a href="index.php" class="btn">🏠 Dashboard</a>
</div>

<div style="background: #fef3c7; color: #92400e; padding: 20px; margin: 20px 0; border-left: 4px solid #f59e0b; border-radius: 4px;">
    <strong>⚠️ IMPORTANT:</strong><br>
    After clicking the button above, press <strong>Ctrl+Shift+R</strong> or <strong>Ctrl+F5</strong> to hard refresh!
</div>

</body>
</html>
