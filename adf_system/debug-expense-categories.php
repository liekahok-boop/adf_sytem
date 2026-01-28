<?php
define('APP_ACCESS', true);
$base_path = dirname(__FILE__);
require_once $base_path . '/config/config.php';
require_once $base_path . '/config/database.php';

$db = Database::getInstance()->getConnection();

echo "<h2>Debug: Expense Categories</h2>";

// Check if table exists
try {
    $stmt = $db->query("SHOW TABLES LIKE 'project_expenses'");
    $table_exists = $stmt->rowCount() > 0;
    echo "<h3>Table 'project_expenses' exists: " . ($table_exists ? 'YES' : 'NO') . "</h3>";
} catch (Exception $e) {
    echo "<h3>Error checking table: " . $e->getMessage() . "</h3>";
}

// Check table structure
try {
    $stmt = $db->query("DESCRIBE project_expenses");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Table Structure:</h3><pre>";
    print_r($columns);
    echo "</pre>";
    
    $has_category = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'category') {
            $has_category = true;
            break;
        }
    }
    echo "<h3>Has 'category' column: " . ($has_category ? 'YES' : 'NO') . "</h3>";
} catch (Exception $e) {
    echo "<h3>Error checking structure: " . $e->getMessage() . "</h3>";
}

// Check data
try {
    $stmt = $db->query("SELECT * FROM project_expenses LIMIT 10");
    $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Sample Data (10 rows):</h3><pre>";
    print_r($expenses);
    echo "</pre>";
    echo "<h3>Total Rows: " . count($expenses) . "</h3>";
} catch (Exception $e) {
    echo "<h3>Error fetching data: " . $e->getMessage() . "</h3>";
}

// Test the category query
try {
    $stmt = $db->prepare("
        SELECT 
            pe.category,
            SUM(pe.amount_idr) as total_amount_idr,
            COUNT(*) as transaction_count
        FROM project_expenses pe
        JOIN projects p ON pe.project_id = p.id
        GROUP BY pe.category
        ORDER BY total_amount_idr DESC
    ");
    $stmt->execute();
    $expense_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Expense Categories Query Result:</h3>";
    echo "<h4>Total Categories: " . count($expense_categories) . "</h4>";
    echo "<pre>";
    print_r($expense_categories);
    echo "</pre>";
    
    $total_sum = array_sum(array_column($expense_categories, 'total_amount_idr'));
    echo "<h3>Total Sum: Rp " . number_format($total_sum, 0, ',', '.') . "</h3>";
    echo "<h3>Has Data: " . ((!empty($expense_categories) && $total_sum > 0) ? 'YES' : 'NO') . "</h3>";
    
} catch (Exception $e) {
    echo "<h3>Error running category query: " . $e->getMessage() . "</h3>";
}
?>
