<?php
/**
 * COMPLETE FIX - Check Everything and Apply Fixes
 */
header('Content-Type: text/html; charset=utf-8');
define('APP_ACCESS', true);
require_once 'config/config.php';
require_once 'config/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Complete Fix</title></head><body>";
echo "<h2>🔧 Complete System Fix & Check</h2><pre style='background:#1e293b;color:#fff;padding:20px;border-radius:8px;'>";

echo "=== STEP 1: Check Current State ===\n";
echo "Active Business: " . ACTIVE_BUSINESS_ID . "\n";
echo "Business Name: " . BUSINESS_NAME . "\n\n";

// Check if columns exist
$cbColumns = $conn->query("SHOW COLUMNS FROM cash_book")->fetchAll(PDO::FETCH_COLUMN);
$hasBranchId = in_array('branch_id', $cbColumns);
echo "cash_book has branch_id: " . ($hasBranchId ? "✅ YES" : "❌ NO") . "\n";

if (!$hasBranchId) {
    echo "\n=== STEP 2: Adding branch_id Column ===\n";
    try {
        $conn->exec("ALTER TABLE cash_book ADD branch_id VARCHAR(50) NOT NULL DEFAULT 'narayana-hotel' AFTER id");
        echo "✅ Added branch_id to cash_book\n";
    } catch (Exception $e) {
        echo "⚠️ " . $e->getMessage() . "\n";
    }
}

echo "\n=== STEP 3: Updating Records ===\n";
try {
    $updated = $conn->exec("UPDATE cash_book SET branch_id = 'narayana-hotel' WHERE branch_id = '' OR branch_id IS NULL OR branch_id != 'narayana-hotel'");
    echo "✅ Updated {$updated} cash_book records\n";
    
    // Check and fix divisions
    $divColumns = $conn->query("SHOW COLUMNS FROM divisions")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('branch_id', $divColumns)) {
        $conn->exec("ALTER TABLE divisions ADD branch_id VARCHAR(50) NOT NULL DEFAULT 'narayana-hotel' AFTER id");
        echo "✅ Added branch_id to divisions\n";
    }
    $conn->exec("UPDATE divisions SET branch_id = 'narayana-hotel' WHERE branch_id = '' OR branch_id IS NULL");
    
    // Check and fix categories  
    $catColumns = $conn->query("SHOW COLUMNS FROM categories")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('branch_id', $catColumns)) {
        $conn->exec("ALTER TABLE categories ADD branch_id VARCHAR(50) NOT NULL DEFAULT 'narayana-hotel' AFTER id");
        echo "✅ Added branch_id to categories\n";
    }
    $conn->exec("UPDATE categories SET branch_id = 'narayana-hotel' WHERE branch_id = '' OR branch_id IS NULL");
    
} catch (Exception $e) {
    echo "⚠️ Error: " . $e->getMessage() . "\n";
}

echo "\n=== STEP 4: Verification ===\n";
$cashbookData = $conn->query("
    SELECT 
        COUNT(*) as total,
        MIN(transaction_date) as first_date,
        MAX(transaction_date) as last_date
    FROM cash_book 
    WHERE branch_id = 'narayana-hotel'
")->fetch(PDO::FETCH_ASSOC);

echo "Total records: {$cashbookData['total']}\n";
echo "Date range: {$cashbookData['first_date']} to {$cashbookData['last_date']}\n";

if ($cashbookData['total'] > 0) {
    echo "\n=== STEP 5: Sample Data ===\n";
    $samples = $conn->query("
        SELECT cb.id, cb.transaction_date, cb.amount, cb.transaction_type, d.division_name
        FROM cash_book cb
        LEFT JOIN divisions d ON cb.division_id = d.id
        WHERE cb.branch_id = 'narayana-hotel'
        ORDER BY cb.transaction_date DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($samples as $row) {
        echo "#{$row['id']} - {$row['transaction_date']} - {$row['division_name']} - ";
        echo $row['transaction_type'] . " - Rp " . number_format($row['amount'], 0, ',', '.') . "\n";
    }
}

echo "\n=== ✅ ALL DONE! ===\n";
echo "\nNow try these links:\n";
echo "</pre>";
echo "<div style='padding:20px;'>";
echo "<a href='modules/cashbook/index.php?month=2026-01' style='display:inline-block;margin:10px;padding:15px 30px;background:#4f46e5;color:white;text-decoration:none;border-radius:8px;font-weight:bold;'>📊 Open Cashbook (January 2026)</a><br>";
echo "<a href='modules/cashbook/index.php?date=all' style='display:inline-block;margin:10px;padding:15px 30px;background:#059669;color:white;text-decoration:none;border-radius:8px;font-weight:bold;'>📊 Open Cashbook (All Dates)</a><br>";
echo "<a href='debug-cashbook.php' style='display:inline-block;margin:10px;padding:15px 30px;background:#dc2626;color:white;text-decoration:none;border-radius:8px;font-weight:bold;'>🔍 Debug Again</a>";
echo "</div>";
echo "<p style='padding:20px;background:#fef3c7;border-left:4px solid #f59e0b;margin:20px;'><strong>⚠️ Important:</strong> Press <strong>Ctrl+Shift+R</strong> or <strong>Ctrl+F5</strong> to hard refresh and clear browser cache!</p>";
echo "</body></html>";
?>
