<?php
/**
 * API: Owner Recent Transactions
 * Get recent transactions for owner dashboard
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
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

try {
    // Build WHERE clause for branch filter
    $branchWhere = '';
    $params = [];
    if ($branchId) {
        $branchWhere = ' AND cb.branch_id = :branch_id';
        $params['branch_id'] = $branchId;
    }
    
    // Get recent transactions
    $transactions = $db->fetchAll(
        "SELECT 
            cb.*,
            d.division_name,
            c.category_name,
            u.full_name as user_name
         FROM cash_book cb
         LEFT JOIN divisions d ON cb.division_id = d.id
         LEFT JOIN categories c ON cb.category_id = c.id
         LEFT JOIN users u ON cb.created_by = u.id
         WHERE DATE(cb.transaction_date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)" . $branchWhere . "
         ORDER BY cb.transaction_date DESC, cb.transaction_time DESC
         LIMIT :limit",
        array_merge($params, ['limit' => $limit])
    );
    
    echo json_encode([
        'success' => true,
        'transactions' => $transactions,
        'count' => count($transactions),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
