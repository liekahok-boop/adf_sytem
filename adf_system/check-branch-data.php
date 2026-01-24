<?php
require_once 'config/database.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance();
    
    // Check branch_id distribution in cash_book
    $cashBookData = $db->fetchAll("
        SELECT branch_id, 
               COUNT(*) as total_transactions,
               SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END) as total_income,
               SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) as total_expense
        FROM cash_book 
        GROUP BY branch_id
    ");
    
    // Get businesses
    $businesses = $db->fetchAll("SELECT id, business_name FROM businesses ORDER BY id");
    
    // Get today's data per branch
    $todayData = $db->fetchAll("
        SELECT branch_id,
               SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END) as today_income,
               SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) as today_expense
        FROM cash_book
        WHERE transaction_date = CURDATE()
        GROUP BY branch_id
    ");
    
    echo json_encode([
        'success' => true,
        'businesses' => $businesses,
        'cash_book_by_branch' => $cashBookData,
        'today_by_branch' => $todayData,
        'current_date' => date('Y-m-d')
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
