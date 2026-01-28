<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

echo "<h2>Checking project_expenses Table Structure</h2>";

try {
    $stmt = $db->query("DESCRIBE project_expenses");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Current Columns:</h3>";
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check which columns are missing
    $has_amount_usd = false;
    $has_amount_idr = false;
    $has_exchange_rate = false;
    
    foreach ($columns as $col) {
        if ($col['Field'] === 'amount_usd') $has_amount_usd = true;
        if ($col['Field'] === 'amount_idr') $has_amount_idr = true;
        if ($col['Field'] === 'exchange_rate') $has_exchange_rate = true;
    }
    
    echo "<h3>Missing Columns Check:</h3>";
    echo "<ul>";
    echo "<li>amount_usd: " . ($has_amount_usd ? "✓ EXISTS" : "✗ MISSING") . "</li>";
    echo "<li>amount_idr: " . ($has_amount_idr ? "✓ EXISTS" : "✗ MISSING") . "</li>";
    echo "<li>exchange_rate: " . ($has_exchange_rate ? "✓ EXISTS" : "✗ MISSING") . "</li>";
    echo "</ul>";
    
    if (!$has_amount_usd || !$has_amount_idr || !$has_exchange_rate) {
        echo "<h3 style='color: red;'>Table structure is incomplete!</h3>";
        echo "<p><a href='fix-project-expenses-table.php' style='padding: 10px 20px; background: #ef4444; color: white; text-decoration: none; border-radius: 8px;'>Fix Table Structure →</a></p>";
    } else {
        echo "<h3 style='color: green;'>✓ Table structure is correct!</h3>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
