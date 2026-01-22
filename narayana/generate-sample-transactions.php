<?php
/**
 * Generate Sample Transactions for Last 30 Days
 */

define('APP_ACCESS', true);
require_once 'config/config.php';
require_once 'config/database.php';

$db = Database::getInstance();

// Get divisions and categories
$divisions = $db->fetchAll("SELECT id FROM divisions LIMIT 10");
$categoriesIncome = $db->fetchAll("SELECT id FROM categories WHERE transaction_type = 'income'");
$categoriesExpense = $db->fetchAll("SELECT id FROM categories WHERE transaction_type = 'expense'");

if (empty($divisions) || empty($categoriesIncome) || empty($categoriesExpense)) {
    die("Error: Tidak ada divisions atau categories. Jalankan setup terlebih dahulu.");
}

// Payment methods
$paymentMethods = ['cash', 'transfer', 'qr'];

// Generate 100 transactions over last 30 days
$inserted = 0;
for ($i = 0; $i < 100; $i++) {
    // Random date in last 30 days
    $daysAgo = rand(0, 29);
    $date = date('Y-m-d', strtotime("-{$daysAgo} days"));
    $time = sprintf('%02d:%02d:%02d', rand(8, 20), rand(0, 59), rand(0, 59));
    
    // Random transaction type (60% income, 40% expense)
    $isIncome = rand(1, 100) <= 60;
    $type = $isIncome ? 'income' : 'expense';
    
    // Select random division
    $division = $divisions[array_rand($divisions)];
    
    // Select random category based on type
    $categories = $isIncome ? $categoriesIncome : $categoriesExpense;
    $category = $categories[array_rand($categories)];
    
    // Random amount
    if ($isIncome) {
        $amount = rand(50000, 5000000); // 50rb - 5jt
    } else {
        $amount = rand(25000, 2000000); // 25rb - 2jt
    }
    
    // Random payment method
    $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
    
    // Insert transaction
    try {
        $db->insert('cash_book', [
            'transaction_date' => $date,
            'transaction_time' => $time,
            'transaction_type' => $type,
            'division_id' => $division['id'],
            'category_id' => $category['id'],
            'amount' => $amount,
            'description' => 'Sample transaction ' . ($i + 1),
            'payment_method' => $paymentMethod,
            'created_by' => 1
        ]);
        $inserted++;
    } catch (Exception $e) {
        echo "Error inserting transaction {$i}: " . $e->getMessage() . "\n";
    }
}

echo "✓ Berhasil generate {$inserted} transaksi sample untuk 30 hari terakhir!\n";
echo "Refresh dashboard untuk melihat chart dengan data.\n";
