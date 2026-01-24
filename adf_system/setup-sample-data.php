<?php
require_once 'config/config.php';
require_once 'config/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Setup Sample Data</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#1e293b;color:#fff;}
.success{background:#10b981;padding:15px;border-radius:8px;margin:10px 0;}
.error{background:#ef4444;padding:15px;border-radius:8px;margin:10px 0;}
.info{background:#3b82f6;padding:15px;border-radius:8px;margin:10px 0;}
table{width:100%;border-collapse:collapse;margin:20px 0;background:#334155;}
th,td{padding:10px;border:1px solid #475569;text-align:left;font-size:13px;}
th{background:#475569;}
.btn{display:inline-block;padding:10px 20px;background:#6366f1;color:white;text-decoration:none;border-radius:5px;margin:10px 5px 10px 0;}
h3{margin-top:30px;border-bottom:2px solid #475569;padding-bottom:10px;}
</style></head><body>";

echo "<h2>🏨 Setup Sample Data - Narayana DB</h2><hr>";

try {
    // ====== CEK & BUAT TABEL DIVISIONS ======
    echo "<h3>📊 Divisions Table</h3>";
    try {
        $count = $db->fetchOne("SELECT COUNT(*) FROM divisions");
        echo "<div class='info'>✓ Tabel divisions sudah ada. Jumlah data: $count</div>";
        
        if ($count == 0) {
            echo "<p>Menambahkan sample divisions...</p>";
            
            $divisions = [
                ['Restaurant', 'RES', 'Restaurant & Dining'],
                ['Room Service', 'RS', 'In-Room Dining Service'],
                ['Front Office', 'FO', 'Reception & Guest Services'],
                ['Housekeeping', 'HK', 'Room Cleaning & Maintenance'],
                ['Laundry', 'LDY', 'Laundry Services'],
                ['Spa & Wellness', 'SPA', 'Spa & Massage'],
                ['Bar & Lounge', 'BAR', 'Bar & Beverage'],
                ['Banquet', 'BNQ', 'Event & Meeting Room'],
                ['Minibar', 'MB', 'In-Room Minibar Sales'],
                ['Transportation', 'TRANS', 'Airport Transfer & Car Rental']
            ];
            
            $stmt = $conn->prepare("INSERT INTO divisions (division_name, division_code, description, is_active) VALUES (?, ?, ?, 1)");
            foreach ($divisions as $div) {
                $stmt->execute($div);
            }
            
            echo "<div class='success'>✅ " . count($divisions) . " divisions berhasil ditambahkan!</div>";
        }
        
        // Tampilkan divisions
        $divList = $db->fetchAll("SELECT * FROM divisions WHERE is_active = 1 ORDER BY division_code");
        echo "<table><tr><th>ID</th><th>Code</th><th>Division Name</th><th>Description</th></tr>";
        foreach ($divList as $d) {
            echo "<tr><td>{$d['id']}</td><td><strong>{$d['division_code']}</strong></td><td>{$d['division_name']}</td><td>{$d['description']}</td></tr>";
        }
        echo "</table>";
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Tabel divisions tidak ada. Error: " . $e->getMessage() . "</div>";
        echo "<p>Silakan setup tabel divisions terlebih dahulu dari modul Settings.</p>";
    }
    
    // ====== CEK & BUAT TABEL CATEGORIES ======
    echo "<h3>📁 Categories Table</h3>";
    try {
        $count = $db->fetchOne("SELECT COUNT(*) FROM categories");
        echo "<div class='info'>✓ Tabel categories sudah ada. Jumlah data: $count</div>";
        
        if ($count == 0) {
            echo "<p>Menambahkan sample categories...</p>";
            
            $categories = [
                ['Food Sales', 'income', 'Revenue from food sales'],
                ['Beverage Sales', 'income', 'Revenue from beverage sales'],
                ['Room Charges', 'income', 'Room service charges'],
                ['Service Charges', 'income', 'Additional service fees'],
                ['Food Supplies', 'expense', 'Purchase of food ingredients'],
                ['Beverage Supplies', 'expense', 'Purchase of beverages'],
                ['Staff Salary', 'expense', 'Employee salaries & wages'],
                ['Utilities', 'expense', 'Electricity, water, internet'],
                ['Maintenance', 'expense', 'Equipment & facility maintenance'],
                ['Cleaning Supplies', 'expense', 'Cleaning materials & chemicals']
            ];
            
            $stmt = $conn->prepare("INSERT INTO categories (category_name, category_type, description, is_active) VALUES (?, ?, ?, 1)");
            foreach ($categories as $cat) {
                $stmt->execute($cat);
            }
            
            echo "<div class='success'>✅ " . count($categories) . " categories berhasil ditambahkan!</div>";
        }
        
        // Tampilkan categories
        $catList = $db->fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY category_type, category_name");
        echo "<table><tr><th>ID</th><th>Type</th><th>Category Name</th><th>Description</th></tr>";
        foreach ($catList as $c) {
            $typeColor = $c['category_type'] == 'income' ? '#10b981' : '#ef4444';
            echo "<tr><td>{$c['id']}</td><td style='color:$typeColor;font-weight:bold;'>{$c['category_type']}</td><td>{$c['category_name']}</td><td>{$c['description']}</td></tr>";
        }
        echo "</table>";
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Tabel categories tidak ada. Error: " . $e->getMessage() . "</div>";
    }
    
    // ====== CEK & ISI TABEL CASH_BOOK (TRANSAKSI) ======
    echo "<h3>💰 Cash Book Transactions</h3>";
    try {
        $count = $db->fetchOne("SELECT COUNT(*) FROM cash_book");
        echo "<div class='info'>✓ Tabel cash_book sudah ada. Jumlah transaksi: $count</div>";
        
        if ($count < 20) {
            echo "<p>Menambahkan sample transactions...</p>";
            
            // Get IDs
            $divIds = $db->fetchAll("SELECT id FROM divisions WHERE is_active = 1 LIMIT 5");
            $incomeCategories = $db->fetchAll("SELECT id FROM categories WHERE category_type = 'income' AND is_active = 1");
            $expenseCategories = $db->fetchAll("SELECT id FROM categories WHERE category_type = 'expense' AND is_active = 1");
            $userId = $db->fetchOne("SELECT id FROM users WHERE username = 'admin'");
            
            if (empty($divIds) || empty($incomeCategories) || empty($expenseCategories) || !$userId) {
                echo "<div class='error'>❌ Data master belum lengkap. Pastikan divisions, categories, dan users sudah ada.</div>";
            } else {
                // Generate sample transactions untuk bulan ini
                $transactions = [];
                $today = date('Y-m-d');
                
                // Income transactions
                for ($i = 1; $i <= 15; $i++) {
                    $date = date('Y-m-d', strtotime("-" . rand(0, 20) . " days"));
                    $div = $divIds[array_rand($divIds)];
                    $cat = $incomeCategories[array_rand($incomeCategories)];
                    $amount = rand(100, 500) * 1000; // 100k - 500k
                    
                    $transactions[] = [
                        'income',
                        $div['id'],
                        $cat['id'],
                        $amount,
                        $date,
                        date('H:i:s'),
                        'Sample income transaction #' . $i,
                        $userId
                    ];
                }
                
                // Expense transactions
                for ($i = 1; $i <= 10; $i++) {
                    $date = date('Y-m-d', strtotime("-" . rand(0, 20) . " days"));
                    $div = $divIds[array_rand($divIds)];
                    $cat = $expenseCategories[array_rand($expenseCategories)];
                    $amount = rand(50, 300) * 1000; // 50k - 300k
                    
                    $transactions[] = [
                        'expense',
                        $div['id'],
                        $cat['id'],
                        $amount,
                        $date,
                        date('H:i:s'),
                        'Sample expense transaction #' . $i,
                        $userId
                    ];
                }
                
                $stmt = $conn->prepare("
                    INSERT INTO cash_book 
                    (transaction_type, division_id, category_id, amount, transaction_date, transaction_time, description, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                foreach ($transactions as $trans) {
                    $stmt->execute($trans);
                }
                
                echo "<div class='success'>✅ " . count($transactions) . " sample transactions berhasil ditambahkan!</div>";
            }
        }
        
        // Tampilkan recent transactions
        $recentTrans = $db->fetchAll("
            SELECT cb.*, d.division_name, c.category_name 
            FROM cash_book cb
            JOIN divisions d ON cb.division_id = d.id
            JOIN categories c ON cb.category_id = c.id
            ORDER BY cb.transaction_date DESC, cb.transaction_time DESC
            LIMIT 10
        ");
        
        if (!empty($recentTrans)) {
            echo "<h4>10 Transaksi Terakhir:</h4>";
            echo "<table><tr><th>Date</th><th>Type</th><th>Division</th><th>Category</th><th>Amount</th><th>Description</th></tr>";
            foreach ($recentTrans as $t) {
                $typeColor = $t['transaction_type'] == 'income' ? '#10b981' : '#ef4444';
                $amount = number_format($t['amount'], 0, ',', '.');
                echo "<tr>";
                echo "<td>{$t['transaction_date']}</td>";
                echo "<td style='color:$typeColor;font-weight:bold;'>{$t['transaction_type']}</td>";
                echo "<td>{$t['division_name']}</td>";
                echo "<td>{$t['category_name']}</td>";
                echo "<td style='text-align:right;'>Rp $amount</td>";
                echo "<td>{$t['description']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        // Summary
        $summary = $db->fetchAll("
            SELECT 
                transaction_type,
                COUNT(*) as total_trans,
                SUM(amount) as total_amount
            FROM cash_book
            WHERE MONTH(transaction_date) = MONTH(CURDATE())
            GROUP BY transaction_type
        ");
        
        if (!empty($summary)) {
            echo "<h4>Summary Bulan Ini:</h4>";
            echo "<table><tr><th>Type</th><th>Total Transactions</th><th>Total Amount</th></tr>";
            foreach ($summary as $s) {
                $typeColor = $s['transaction_type'] == 'income' ? '#10b981' : '#ef4444';
                $amount = number_format($s['total_amount'], 0, ',', '.');
                echo "<tr>";
                echo "<td style='color:$typeColor;font-weight:bold;'>" . strtoupper($s['transaction_type']) . "</td>";
                echo "<td style='text-align:center;'>{$s['total_trans']}</td>";
                echo "<td style='text-align:right;'>Rp $amount</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    }
    
    echo "<hr>";
    echo "<div class='success' style='font-size:1.1em;'>";
    echo "<strong>🎉 Setup Complete!</strong><br>";
    echo "Sample data sudah siap. Silakan cek dashboard untuk melihat statistik.";
    echo "</div>";
    
    echo "<a href='index.php' class='btn'>📊 Lihat Dashboard</a>";
    echo "<a href='modules/cashbook/index.php' class='btn'>💰 Buku Kas</a>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Fatal Error: " . $e->getMessage() . "</div>";
}

echo "</body></html>";
?>
