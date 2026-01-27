<?php
/**
 * DEBUG Cashbook - Check what's happening
 */
define('APP_ACCESS', true);
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

$auth = new Auth();
$auth->requireLogin();
$db = Database::getInstance();

echo "<h2>Debug Cashbook Data</h2>";
echo "<pre>";

echo "\n=== ACTIVE BUSINESS ===\n";
echo "ACTIVE_BUSINESS_ID: " . ACTIVE_BUSINESS_ID . "\n";
echo "BUSINESS_NAME: " . BUSINESS_NAME . "\n";

echo "\n=== CASH BOOK DATA ===\n";
$allCashBook = $db->fetchAll("SELECT cb.*, d.division_name, c.category_name 
    FROM cash_book cb
    LEFT JOIN divisions d ON cb.division_id = d.id
    LEFT JOIN categories c ON cb.category_id = c.id
    ORDER BY cb.id DESC LIMIT 10");
echo "Total records (last 10): " . count($allCashBook) . "\n";
foreach ($allCashBook as $cb) {
    echo "ID: {$cb['id']}, Branch: {$cb['branch_id']}, Date: {$cb['transaction_date']}, ";
    echo "Division: {$cb['division_name']}, Category: {$cb['category_name']}, ";
    echo "Type: {$cb['transaction_type']}, Amount: {$cb['amount']}\n";
}

echo "\n=== CASH BOOK FOR CURRENT BRANCH ===\n";
$currentBranchCB = $db->fetchAll(
    "SELECT cb.*, d.division_name, c.category_name 
    FROM cash_book cb
    LEFT JOIN divisions d ON cb.division_id = d.id
    LEFT JOIN categories c ON cb.category_id = c.id
    WHERE cb.branch_id = :branch_id
    ORDER BY cb.id DESC LIMIT 10",
    ['branch_id' => ACTIVE_BUSINESS_ID]
);
echo "Total for branch " . ACTIVE_BUSINESS_ID . ": " . count($currentBranchCB) . "\n";
foreach ($currentBranchCB as $cb) {
    echo "ID: {$cb['id']}, Date: {$cb['transaction_date']}, ";
    echo "Division: {$cb['division_name']}, Category: {$cb['category_name']}, ";
    echo "Type: {$cb['transaction_type']}, Amount: {$cb['amount']}\n";
}

echo "\n=== TODAY'S FILTER (as in index.php) ===\n";
$today = date('Y-m-d');
echo "Today's date: {$today}\n";
$todayData = $db->fetchAll(
    "SELECT cb.* 
    FROM cash_book cb
    WHERE cb.branch_id = :branch_id AND cb.transaction_date = :date
    ORDER BY cb.id DESC",
    ['branch_id' => ACTIVE_BUSINESS_ID, 'date' => $today]
);
echo "Records for today: " . count($todayData) . "\n";
foreach ($todayData as $cb) {
    echo "ID: {$cb['id']}, Date: {$cb['transaction_date']}, Time: {$cb['transaction_time']}, Amount: {$cb['amount']}\n";
}

echo "\n=== ALL DATES FOR CURRENT BRANCH ===\n";
$allDates = $db->fetchAll(
    "SELECT DISTINCT transaction_date, COUNT(*) as count 
    FROM cash_book 
    WHERE branch_id = :branch_id
    GROUP BY transaction_date
    ORDER BY transaction_date DESC LIMIT 10",
    ['branch_id' => ACTIVE_BUSINESS_ID]
);
echo "Distinct dates available:\n";
foreach ($allDates as $date) {
    echo "Date: {$date['transaction_date']}, Count: {$date['count']}\n";
}

echo "\n=== DIVISIONS ===\n";
$divisions = $db->fetchAll(
    "SELECT * FROM divisions WHERE branch_id = :branch_id",
    ['branch_id' => ACTIVE_BUSINESS_ID]
);
echo "Total divisions: " . count($divisions) . "\n";
foreach ($divisions as $div) {
    echo "ID: {$div['id']}, Name: {$div['division_name']}, Active: {$div['is_active']}\n";
}

echo "\n=== CATEGORIES ===\n";
$categories = $db->fetchAll(
    "SELECT * FROM categories WHERE branch_id = :branch_id",
    ['branch_id' => ACTIVE_BUSINESS_ID]
);
echo "Total categories: " . count($categories) . "\n";
foreach ($categories as $cat) {
    echo "ID: {$cat['id']}, Name: {$cat['category_name']}\n";
}

echo "</pre>";
?>
