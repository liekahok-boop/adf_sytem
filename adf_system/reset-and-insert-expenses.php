<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

echo "<h2>Reset and Insert Fresh Sample Data</h2>";

try {
    // Delete all existing expenses
    echo "<h3>Deleting old data...</h3>";
    $stmt = $db->exec("DELETE FROM project_expenses");
    echo "✓ Deleted $stmt records<br>";
    
    // Get or create projects
    $stmt = $db->query("SELECT id, name FROM projects LIMIT 2");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($projects)) {
        echo "Creating projects... ";
        $db->exec("
            INSERT INTO projects (name, description, status, start_date) 
            VALUES 
            ('Narayana Hotel G4', 'Renovasi dan pembangunan Narayana Hotel G4', 'active', CURDATE()),
            ('Narayana Hotel G5', 'Pembangunan gedung baru Narayana Hotel G5', 'active', CURDATE())
        ");
        $stmt = $db->query("SELECT id, name FROM projects LIMIT 2");
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "✓ Created<br>";
    }
    
    echo "<h3>Inserting fresh sample expenses...</h3>";
    
    $expenses = [
        ['category' => 'Semen', 'description' => 'Pembelian semen portland 50 sak', 'amount_usd' => 500, 'amount_idr' => 7750000],
        ['category' => 'Besi', 'description' => 'Besi beton diameter 12mm', 'amount_usd' => 800, 'amount_idr' => 12400000],
        ['category' => 'Cat', 'description' => 'Cat tembok interior Dulux', 'amount_usd' => 200, 'amount_idr' => 3100000],
        ['category' => 'Pasir', 'description' => 'Pasir untuk cor 10 truk', 'amount_usd' => 150, 'amount_idr' => 2325000],
        ['category' => 'Upah Tukang', 'description' => 'Upah pekerja konstruksi minggu 1', 'amount_usd' => 1000, 'amount_idr' => 15500000],
        ['category' => 'Semen', 'description' => 'Semen tambahan untuk pondasi lantai 2', 'amount_usd' => 300, 'amount_idr' => 4650000],
        ['category' => 'Besi', 'description' => 'Besi untuk rangka atap', 'amount_usd' => 400, 'amount_idr' => 6200000],
        ['category' => 'Kayu', 'description' => 'Kayu jati untuk bekisting', 'amount_usd' => 250, 'amount_idr' => 3875000],
        ['category' => 'Cat', 'description' => 'Cat eksterior weathershield', 'amount_usd' => 150, 'amount_idr' => 2325000],
        ['category' => 'Keramik', 'description' => 'Keramik lantai 60x60 Roman', 'amount_usd' => 600, 'amount_idr' => 9300000],
        ['category' => 'Pasir', 'description' => 'Pasir halus untuk plester', 'amount_usd' => 100, 'amount_idr' => 1550000],
        ['category' => 'Upah Tukang', 'description' => 'Upah pekerja konstruksi minggu 2', 'amount_usd' => 1000, 'amount_idr' => 15500000],
        ['category' => 'Pipa', 'description' => 'Pipa PVC dan sambungan', 'amount_usd' => 300, 'amount_idr' => 4650000],
        ['category' => 'Listrik', 'description' => 'Kabel dan instalasi listrik', 'amount_usd' => 450, 'amount_idr' => 6975000],
        ['category' => 'Kayu', 'description' => 'Kayu untuk rangka plafon', 'amount_usd' => 200, 'amount_idr' => 3100000]
    ];
    
    $stmt = $db->prepare("
        INSERT INTO project_expenses 
        (project_id, category, description, amount_usd, amount_idr, exchange_rate, expense_date) 
        VALUES 
        (?, ?, ?, ?, ?, 15500, DATE_SUB(CURDATE(), INTERVAL ? DAY))
    ");
    
    $inserted = 0;
    foreach ($expenses as $i => $expense) {
        $project_id = $projects[$i % count($projects)]['id'];
        $days_ago = rand(1, 30);
        
        $stmt->execute([
            $project_id,
            $expense['category'],
            $expense['description'],
            $expense['amount_usd'],
            $expense['amount_idr'],
            $days_ago
        ]);
        $inserted++;
        echo "✓ {$expense['category']}: Rp " . number_format($expense['amount_idr'], 0, ',', '.') . "<br>";
    }
    
    echo "<h3 style='color: green;'>✓ Successfully inserted $inserted expense records!</h3>";
    
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
    
    $grand_total = 0;
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%; max-width: 600px;'>";
    echo "<tr style='background: #f3f4f6;'><th>Kategori</th><th>Transaksi</th><th>Total (IDR)</th></tr>";
    foreach ($summary as $row) {
        $grand_total += $row['total_idr'];
        echo "<tr>";
        echo "<td><strong>{$row['category']}</strong></td>";
        echo "<td style='text-align: center;'>{$row['count']}</td>";
        echo "<td style='text-align: right;'><strong>Rp " . number_format($row['total_idr'], 0, ',', '.') . "</strong></td>";
        echo "</tr>";
    }
    echo "<tr style='background: #e5e7eb; font-weight: bold;'>";
    echo "<td colspan='2' style='text-align: right;'>TOTAL:</td>";
    echo "<td style='text-align: right; color: #ef4444;'>Rp " . number_format($grand_total, 0, ',', '.') . "</td>";
    echo "</tr>";
    echo "</table>";
    
    echo "<h2 style='color: green;'>✓ Fresh sample data created!</h2>";
    echo "<p><a href='modules/investor/' style='font-size: 18px; padding: 15px 30px; background: #6366f1; color: white; text-decoration: none; border-radius: 8px; display: inline-block; margin-top: 20px;'>Go to Investor Module →</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
