<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

echo "<h2>Creating Sample Project Expense Data</h2>";

// Insert sample project if not exists
try {
    $stmt = $db->query("SELECT COUNT(*) FROM projects");
    $project_count = $stmt->fetchColumn();
    
    if ($project_count == 0) {
        echo "Creating sample project... ";
        $stmt = $db->prepare("
            INSERT INTO projects (name, description, status, start_date) 
            VALUES 
            ('Narayana Hotel G4', 'Renovasi dan pembangunan Narayana Hotel G4', 'active', CURDATE()),
            ('Narayana Hotel G5', 'Pembangunan gedung baru Narayana Hotel G5', 'active', CURDATE())
        ");
        $stmt->execute();
        echo "✓ Done<br>";
    } else {
        echo "Projects already exist: $project_count projects<br>";
    }
    
    // Get project IDs
    $stmt = $db->query("SELECT id, name FROM projects LIMIT 2");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($projects)) {
        echo "<p style='color: red;'>No projects found!</p>";
        exit;
    }
    
    // Check if expenses already exist
    $stmt = $db->query("SELECT COUNT(*) FROM project_expenses");
    $expense_count = $stmt->fetchColumn();
    
    if ($expense_count == 0) {
        echo "<h3>Inserting sample expenses...</h3>";
        
        $categories = [
            ['category' => 'Semen', 'description' => 'Pembelian semen portland', 'amount_usd' => 500, 'amount_idr' => 7750000],
            ['category' => 'Besi', 'description' => 'Pembelian besi beton', 'amount_usd' => 800, 'amount_idr' => 12400000],
            ['category' => 'Cat', 'description' => 'Cat tembok dan finishing', 'amount_usd' => 200, 'amount_idr' => 3100000],
            ['category' => 'Pasir', 'description' => 'Pasir untuk cor', 'amount_usd' => 150, 'amount_idr' => 2325000],
            ['category' => 'Upah Tukang', 'description' => 'Upah pekerja konstruksi', 'amount_usd' => 1000, 'amount_idr' => 15500000],
            ['category' => 'Semen', 'description' => 'Semen tambahan untuk pondasi', 'amount_usd' => 300, 'amount_idr' => 4650000],
            ['category' => 'Besi', 'description' => 'Besi untuk rangka atap', 'amount_usd' => 400, 'amount_idr' => 6200000],
            ['category' => 'Kayu', 'description' => 'Kayu untuk bekisting', 'amount_usd' => 250, 'amount_idr' => 3875000],
            ['category' => 'Cat', 'description' => 'Cat eksterior', 'amount_usd' => 150, 'amount_idr' => 2325000],
            ['category' => 'Keramik', 'description' => 'Keramik lantai dan dinding', 'amount_usd' => 600, 'amount_idr' => 9300000]
        ];
        
        $stmt = $db->prepare("
            INSERT INTO project_expenses 
            (project_id, category, description, amount_usd, amount_idr, exchange_rate, expense_date) 
            VALUES 
            (?, ?, ?, ?, ?, 15500, DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND() * 30) DAY))
        ");
        
        $inserted = 0;
        foreach ($categories as $expense) {
            // Randomly assign to one of the projects
            $project_id = $projects[array_rand($projects)]['id'];
            
            $stmt->execute([
                $project_id,
                $expense['category'],
                $expense['description'],
                $expense['amount_usd'],
                $expense['amount_idr']
            ]);
            $inserted++;
            echo "✓ Inserted: {$expense['category']} - Rp " . number_format($expense['amount_idr'], 0, ',', '.') . "<br>";
        }
        
        echo "<h3 style='color: green;'>✓ Successfully inserted $inserted expense records!</h3>";
    } else {
        echo "<h3>Expenses already exist: $expense_count records</h3>";
    }
    
    // Update project balances (skip if table doesn't exist or has issues)
    echo "<h3>Updating project balances...</h3>";
    try {
        $stmt = $db->query("
            INSERT INTO project_balances (project_id, total_expenses_usd, total_expenses_idr)
            SELECT 
                project_id,
                SUM(amount_usd) as total_usd,
                SUM(amount_idr) as total_idr
            FROM project_expenses
            GROUP BY project_id
            ON DUPLICATE KEY UPDATE
                total_expenses_usd = VALUES(total_expenses_usd),
                total_expenses_idr = VALUES(total_expenses_idr)
        ");
        echo "✓ Balances updated<br>";
    } catch (Exception $e) {
        echo "<span style='color: orange;'>⚠ Skipped balance update (table may not exist): " . $e->getMessage() . "</span><br>";
    }
    
    // Show summary
    echo "<h3>Expense Summary by Category:</h3>";
    $stmt = $db->query("
        SELECT 
            category,
            COUNT(*) as count,
            SUM(amount_idr) as total_idr
        FROM project_expenses
        GROUP BY category
        ORDER BY total_idr DESC
    ");
    $summary = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>Kategori</th><th>Jumlah Transaksi</th><th>Total (IDR)</th></tr>";
    foreach ($summary as $row) {
        echo "<tr>";
        echo "<td><strong>{$row['category']}</strong></td>";
        echo "<td>{$row['count']}</td>";
        echo "<td>Rp " . number_format($row['total_idr'], 0, ',', '.') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2 style='color: green;'>✓ Sample data created successfully!</h2>";
    echo "<p><a href='modules/investor/' style='font-size: 18px; padding: 10px 20px; background: #6366f1; color: white; text-decoration: none; border-radius: 8px; display: inline-block;'>Go to Investor Module →</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
