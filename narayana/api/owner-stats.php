<?php
/**
 * API: Owner Statistics
 * Get today and monthly statistics
 */
error_reporting(0);
ini_set('display_errors', 0);

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$currentUser = $auth->getCurrentUser();

// Check if user is owner or admin
if ($currentUser['role'] !== 'owner' && $currentUser['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$db = Database::getInstance();
$branchId = isset($_GET['branch_id']) ? (int)$_GET['branch_id'] : null;

try {
    $today = date('Y-m-d');
    $thisMonth = date('Y-m');
    $lastMonth = date('Y-m', strtotime('-1 month'));
    
    // Build WHERE clause for branch filter
    $branchWhere = '';
    $params = [];
    if ($branchId) {
        $branchWhere = ' AND branch_id = :branch_id';
        $params['branch_id'] = $branchId;
    }
    
    // TODAY STATS
    $todayStats = $db->fetchOne(
        "SELECT 
            COALESCE(SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END), 0) as income,
            COALESCE(SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END), 0) as expense,
            COUNT(CASE WHEN transaction_type = 'income' THEN 1 END) as income_count,
            COUNT(CASE WHEN transaction_type = 'expense' THEN 1 END) as expense_count
         FROM cash_book 
         WHERE transaction_date = :today" . $branchWhere,
        array_merge(['today' => $today], $params)
    );
    
    // THIS MONTH STATS
    $monthStats = $db->fetchOne(
        "SELECT 
            COALESCE(SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END), 0) as income,
            COALESCE(SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END), 0) as expense
         FROM cash_book 
         WHERE DATE_FORMAT(transaction_date, '%Y-%m') = :month" . $branchWhere,
        array_merge(['month' => $thisMonth], $params)
    );
    
    // LAST MONTH STATS (for comparison)
    $lastMonthStats = $db->fetchOne(
        "SELECT 
            COALESCE(SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END), 0) as income,
            COALESCE(SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END), 0) as expense
         FROM cash_book 
         WHERE DATE_FORMAT(transaction_date, '%Y-%m') = :month" . $branchWhere,
        array_merge(['month' => $lastMonth], $params)
    );
    
    // Calculate change percentages
    $incomeChange = 0;
    $expenseChange = 0;
    
    if ($lastMonthStats['income'] > 0) {
        $incomeChange = (($monthStats['income'] - $lastMonthStats['income']) / $lastMonthStats['income']) * 100;
    }
    
    if ($lastMonthStats['expense'] > 0) {
        $expenseChange = (($monthStats['expense'] - $lastMonthStats['expense']) / $lastMonthStats['expense']) * 100;
    }
    
    echo json_encode([
        'success' => true,
        'today' => [
            'income' => (float)$todayStats['income'],
            'expense' => (float)$todayStats['expense'],
            'income_count' => (int)$todayStats['income_count'],
            'expense_count' => (int)$todayStats['expense_count'],
            'net' => (float)($todayStats['income'] - $todayStats['expense'])
        ],
        'month' => [
            'income' => (float)$monthStats['income'],
            'expense' => (float)$monthStats['expense'],
            'net' => (float)($monthStats['income'] - $monthStats['expense']),
            'income_change' => round($incomeChange, 1),
            'expense_change' => round($expenseChange, 1)
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
