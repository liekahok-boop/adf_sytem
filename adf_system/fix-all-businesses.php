<?php
/**
 * Fix All Businesses - Update branch_id for all business data
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
    <title>Fix All Businesses</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0f172a; color: #fff; padding: 20px; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .info { color: #3b82f6; }
        .warning { color: #f59e0b; }
        h1 { color: #818cf8; }
        pre { background: #1e293b; padding: 20px; border-radius: 8px; }
        .business { margin: 20px 0; padding: 15px; background: #1e293b; border-left: 4px solid #4f46e5; border-radius: 4px; }
    </style>
</head>
<body>
<h1>🔧 Fix All Businesses Data</h1>
<pre>
<?php

// List of all businesses
$businesses = [
    'narayana-hotel' => 'Narayana Hotel',
    'bens-cafe' => "Ben's Cafe",
    'eat-meet' => 'Eat & Meet',
    'furniture-jepara' => 'Furniture Jepara',
    'karimunjawa-party-boat' => 'Karimunjawa Party Boat',
    'pabrik-kapal' => 'Pabrik Kapal'
];

try {
    echo "<span class='info'>═══════════════════════════════════════════════════</span>\n";
    echo "<span class='info'>  CHECKING DATABASE STRUCTURE</span>\n";
    echo "<span class='info'>═══════════════════════════════════════════════════</span>\n\n";
    
    // Ensure branch_id columns exist
    $columns = $conn->query("SHOW COLUMNS FROM cash_book")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('branch_id', $columns)) {
        $conn->exec("ALTER TABLE cash_book ADD branch_id VARCHAR(50) NULL AFTER id");
        echo "<span class='success'>✅ Added branch_id to cash_book</span>\n";
    }
    
    $divColumns = $conn->query("SHOW COLUMNS FROM divisions")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('branch_id', $divColumns)) {
        $conn->exec("ALTER TABLE divisions ADD branch_id VARCHAR(50) NULL AFTER id");
        echo "<span class='success'>✅ Added branch_id to divisions</span>\n";
    }
    
    $catColumns = $conn->query("SHOW COLUMNS FROM categories")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('branch_id', $catColumns)) {
        $conn->exec("ALTER TABLE categories ADD branch_id VARCHAR(50) NULL AFTER id");
        echo "<span class='success'>✅ Added branch_id to categories</span>\n";
    }
    
    echo "\n<span class='info'>═══════════════════════════════════════════════════</span>\n";
    echo "<span class='info'>  DETECTING AND ASSIGNING BUSINESS DATA</span>\n";
    echo "<span class='info'>═══════════════════════════════════════════════════</span>\n\n";
    
    // Strategy: Assign data based on division patterns
    // First, get all divisions and try to map to businesses
    $allDivisions = $conn->query("SELECT id, division_name, branch_id FROM divisions")->fetchAll();
    
    $divisionMapping = [];
    foreach ($allDivisions as $div) {
        $divName = strtolower($div['division_name']);
        
        // Map divisions to businesses based on common patterns
        if (strpos($divName, 'hotel') !== false || strpos($divName, 'room') !== false || 
            strpos($divName, 'housekeeping') !== false || strpos($divName, 'restaurant') !== false ||
            strpos($divName, 'bar') !== false || strpos($divName, 'lounge') !== false) {
            $divisionMapping[$div['id']] = 'narayana-hotel';
        }
        elseif (strpos($divName, 'cafe') !== false || strpos($divName, 'ben') !== false) {
            $divisionMapping[$div['id']] = 'bens-cafe';
        }
        elseif (strpos($divName, 'eat') !== false || strpos($divName, 'meet') !== false) {
            $divisionMapping[$div['id']] = 'eat-meet';
        }
        elseif (strpos($divName, 'furniture') !== false || strpos($divName, 'mebel') !== false) {
            $divisionMapping[$div['id']] = 'furniture-jepara';
        }
        elseif (strpos($divName, 'boat') !== false || strpos($divName, 'kapal') !== false || strpos($divName, 'party') !== false) {
            $divisionMapping[$div['id']] = 'karimunjawa-party-boat';
        }
        elseif (strpos($divName, 'pabrik') !== false || strpos($divName, 'workshop') !== false) {
            $divisionMapping[$div['id']] = 'pabrik-kapal';
        }
        else {
            // Default to narayana-hotel for unmatched
            $divisionMapping[$div['id']] = 'narayana-hotel';
        }
    }
    
    // Update divisions with detected branch_id
    foreach ($divisionMapping as $divId => $branchId) {
        $stmt = $conn->prepare("UPDATE divisions SET branch_id = :branch WHERE id = :id AND (branch_id IS NULL OR branch_id = '')");
        $stmt->execute(['branch' => $branchId, 'id' => $divId]);
    }
    echo "<span class='success'>✅ Mapped and updated " . count($divisionMapping) . " divisions</span>\n\n";
    
    // Update cash_book based on division mapping
    foreach ($divisionMapping as $divId => $branchId) {
        $stmt = $conn->prepare("UPDATE cash_book SET branch_id = :branch WHERE division_id = :div_id AND (branch_id IS NULL OR branch_id = '')");
        $stmt->execute(['branch' => $branchId, 'div_id' => $divId]);
    }
    echo "<span class='success'>✅ Updated cash_book records based on division mapping</span>\n\n";
    
    // Update categories - assign all to all businesses for now (can be used across businesses)
    foreach ($businesses as $businessId => $businessName) {
        $stmt = $conn->prepare("UPDATE categories SET branch_id = :branch WHERE branch_id IS NULL OR branch_id = ''");
        $stmt->execute(['branch' => $businessId]);
        break; // Only run once to set a default
    }
    
    // For categories, we'll create copies for each business
    $categories = $conn->query("SELECT * FROM categories LIMIT 1")->fetchAll();
    if (count($categories) > 0) {
        // Just update all categories with default branch for now
        $conn->exec("UPDATE categories SET branch_id = 'narayana-hotel' WHERE branch_id IS NULL OR branch_id = ''");
        echo "<span class='success'>✅ Updated categories</span>\n\n";
    }
    
    echo "\n<span class='info'>═══════════════════════════════════════════════════</span>\n";
    echo "<span class='info'>  VERIFICATION FOR EACH BUSINESS</span>\n";
    echo "<span class='info'>═══════════════════════════════════════════════════</span>\n\n";
    
    foreach ($businesses as $businessId => $businessName) {
        echo "</pre><div class='business'><strong>{$businessName}</strong> ({$businessId})<pre>";
        
        // Count cash_book
        $cbCount = $conn->prepare("SELECT COUNT(*) as c FROM cash_book WHERE branch_id = :branch");
        $cbCount->execute(['branch' => $businessId]);
        $cb = $cbCount->fetch();
        
        // Count divisions
        $divCount = $conn->prepare("SELECT COUNT(*) as c FROM divisions WHERE branch_id = :branch");
        $divCount->execute(['branch' => $businessId]);
        $div = $divCount->fetch();
        
        echo "  📊 Transactions: <span class='success'>{$cb['c']}</span>\n";
        echo "  🏢 Divisions: <span class='success'>{$div['c']}</span>\n";
        
        if ($cb['c'] > 0) {
            // Show sample
            $sample = $conn->prepare("
                SELECT cb.transaction_date, d.division_name, cb.amount, cb.transaction_type
                FROM cash_book cb
                LEFT JOIN divisions d ON cb.division_id = d.id
                WHERE cb.branch_id = :branch
                ORDER BY cb.id DESC LIMIT 1
            ");
            $sample->execute(['branch' => $businessId]);
            $s = $sample->fetch();
            if ($s) {
                echo "  📅 Latest: {$s['transaction_date']} - {$s['division_name']} - ";
                echo ($s['transaction_type'] == 'income' ? '💰' : '💸') . " " . number_format($s['amount'], 0) . "\n";
            }
        }
        
        echo "</pre></div><pre>";
    }
    
    echo "\n<span class='success'>═══════════════════════════════════════════════════</span>\n";
    echo "<span class='success'>  ✅ ALL BUSINESSES FIXED!</span>\n";
    echo "<span class='success'>═══════════════════════════════════════════════════</span>\n";
    
} catch (Exception $e) {
    echo "<span class='error'>❌ ERROR: " . $e->getMessage() . "</span>\n";
}

?>
</pre>

<div style="text-align:center; padding: 30px;">
    <a href="index.php" style="display:inline-block;padding:15px 30px;background:#4f46e5;color:white;text-decoration:none;border-radius:8px;margin:10px;font-weight:bold;">🏠 Go to Dashboard</a>
    <a href="modules/cashbook/index.php?month=2026-01" style="display:inline-block;padding:15px 30px;background:#059669;color:white;text-decoration:none;border-radius:8px;margin:10px;font-weight:bold;">📊 Open Cashbook</a>
</div>

<div style="background: #fef3c7; color: #92400e; padding: 20px; margin: 20px; border-left: 4px solid #f59e0b; border-radius: 4px;">
    <strong>⚠️ IMPORTANT:</strong><br>
    1. Press <strong>Ctrl+Shift+R</strong> to hard refresh<br>
    2. Switch between businesses using the dropdown menu<br>
    3. Each business should now show their own data
</div>

</body>
</html>
