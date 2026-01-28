<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/config/config.php';

$businesses = [
    'adf_narayana_hotel',
    'adf_benscafe',
    'adf_eat_meet',
    'adf_furniture',
    'adf_karimunjawa',
    'adf_pabrik_kapal'
];

echo "<h2>Fixing project_expenses Table Structure</h2>";
echo "<p>Adding missing columns: amount_usd, amount_idr, exchange_rate, updated_at</p>";

foreach ($businesses as $db_name) {
    echo "<h3>Processing: $db_name</h3>";
    
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=$db_name;charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Check if table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'project_expenses'");
        if ($stmt->rowCount() == 0) {
            echo "<span style='color: orange;'>⚠ Table doesn't exist, skipping...</span><br><br>";
            continue;
        }
        
        // Get current columns
        $stmt = $pdo->query("DESCRIBE project_expenses");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Add amount_usd if missing
        if (!in_array('amount_usd', $columns)) {
            echo "Adding column: amount_usd... ";
            $pdo->exec("ALTER TABLE project_expenses ADD COLUMN amount_usd DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER description");
            echo "✓ Done<br>";
        } else {
            echo "amount_usd: ✓ Already exists<br>";
        }
        
        // Add amount_idr if missing
        if (!in_array('amount_idr', $columns)) {
            echo "Adding column: amount_idr... ";
            $pdo->exec("ALTER TABLE project_expenses ADD COLUMN amount_idr DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER amount_usd");
            echo "✓ Done<br>";
        } else {
            echo "amount_idr: ✓ Already exists<br>";
        }
        
        // Add exchange_rate if missing
        if (!in_array('exchange_rate', $columns)) {
            echo "Adding column: exchange_rate... ";
            $pdo->exec("ALTER TABLE project_expenses ADD COLUMN exchange_rate DECIMAL(10,2) DEFAULT 15500.00 AFTER amount_idr");
            echo "✓ Done<br>";
        } else {
            echo "exchange_rate: ✓ Already exists<br>";
        }
        
        // Add updated_at if missing
        if (!in_array('updated_at', $columns)) {
            echo "Adding column: updated_at... ";
            $pdo->exec("ALTER TABLE project_expenses ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
            echo "✓ Done<br>";
        } else {
            echo "updated_at: ✓ Already exists<br>";
        }
        
        // Rename 'amount' to keep backward compatibility if needed
        if (in_array('amount', $columns) && !in_array('amount_idr', $columns)) {
            echo "Migrating 'amount' to 'amount_idr'... ";
            $pdo->exec("ALTER TABLE project_expenses CHANGE COLUMN amount amount_idr DECIMAL(15,2) NOT NULL DEFAULT 0.00");
            echo "✓ Done<br>";
        }
        
        echo "<span style='color: green;'>✓ Success for $db_name</span><br><br>";
        
    } catch (Exception $e) {
        echo "<span style='color: red;'>✗ Error for $db_name: " . $e->getMessage() . "</span><br><br>";
    }
}

echo "<h2 style='color: green;'>✓ All tables fixed!</h2>";
echo "<p><a href='check-project-expenses-structure.php' style='padding: 10px 20px; background: #6366f1; color: white; text-decoration: none; border-radius: 8px;'>Check Structure Again →</a></p>";
echo "<p><a href='insert-sample-expenses.php' style='padding: 10px 20px; background: #10b981; color: white; text-decoration: none; border-radius: 8px;'>Insert Sample Data →</a></p>";
?>
