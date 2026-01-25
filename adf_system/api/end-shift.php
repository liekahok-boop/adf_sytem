<?php
/**
 * END SHIFT - Generate Daily Report, PO Data, and WhatsApp Integration
 */

// Set header first
header('Content-Type: application/json');

define('APP_ACCESS', true);

// Try to load config files safely
try {
    if (!file_exists('../config/config.php')) {
        throw new Exception('Config file not found');
    }
    require_once '../config/config.php';
    
    if (!file_exists('../config/database.php')) {
        throw new Exception('Database config not found');
    }
    require_once '../config/database.php';
    
    if (!file_exists('../includes/auth.php')) {
        throw new Exception('Auth file not found');
    }
    require_once '../includes/auth.php';
    
    if (!file_exists('../includes/functions.php')) {
        throw new Exception('Functions file not found');
    }
    require_once '../includes/functions.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Configuration error: ' . $e->getMessage()
    ]);
    exit;
}

// Check authentication
try {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Unauthorized - Please login first'
        ]);
        exit;
    }
    
    $auth = new Auth();
    $currentUser = $auth->getCurrentUser();
    
    if (!$currentUser) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid session'
        ]);
        exit;
    }
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication error: ' . $e->getMessage()
    ]);
    exit;
}

$db = Database::getInstance();
$today = date('Y-m-d');try {
    // Get today's transactions
    $transactions = [];
    try {
        $result = $db->fetchAll("
            SELECT 
                cb.id,
                cb.transaction_date,
                cb.transaction_type,
                cb.amount,
                cb.description,
                cb.division_id,
                COALESCE(d.division_name, 'Unknown') as division_name
            FROM cash_book cb
            LEFT JOIN divisions d ON cb.division_id = d.id
            WHERE DATE(cb.transaction_date) = ?
            ORDER BY cb.transaction_date DESC
        ", [$today]);
        $transactions = $result ?? [];
    } catch (Exception $e) {
        error_log('Transaction query error: ' . $e->getMessage());
        $transactions = [];
    }

    // Calculate daily totals
    $totalIncome = 0;
    $totalExpense = 0;

    foreach ($transactions as $trans) {
        $amount = floatval($trans['amount'] ?? 0);
        if (isset($trans['transaction_type'])) {
            if ($trans['transaction_type'] === 'income') {
                $totalIncome += $amount;
            } else {
                $totalExpense += $amount;
            }
        }
    }

    $netBalance = $totalIncome - $totalExpense;

    // Get POs created today
    $pos = [];
    try {
        $result = $db->fetchAll("
            SELECT 
                po.id,
                po.po_number,
                po.supplier_id,
                po.total_amount,
                po.status,
                po.created_at,
                COALESCE(s.supplier_name, 'Unknown') as supplier_name,
                pi.image_path
            FROM purchase_orders po
            LEFT JOIN suppliers s ON po.supplier_id = s.id
            LEFT JOIN po_images pi ON po.id = pi.po_id AND pi.is_primary = 1
            WHERE DATE(po.created_at) = ?
            ORDER BY po.created_at DESC
        ", [$today]);
        $pos = $result ?? [];
    } catch (Exception $e) {
        error_log('PO query error: ' . $e->getMessage());
        $pos = [];
    }

    // Get user and business info
    $userInfo = $db->fetchOne("
        SELECT u.id, u.username, u.full_name, u.email, u.phone, u.role, u.business_id,
               b.id as business_id_2, b.business_name
        FROM users u
        LEFT JOIN businesses b ON u.business_id = b.id
        WHERE u.id = ?
    ", [$currentUser['id']]);

    if (!$userInfo) {
        throw new Exception('User information not found');
    }

    // Get admin/GM contact info
    $adminInfo = $db->fetchOne("
        SELECT id, phone, email FROM users WHERE (role = 'admin' OR role = 'gm' OR role = 'general_manager') LIMIT 1
    ");

    // Get business settings for WhatsApp number
    $businessId = $currentUser['business_id'] ?? 1;
    $settings = $db->fetchOne("
        SELECT * FROM business_settings WHERE business_id = ?
    ", [$businessId]);

    $response = [
        'status' => 'success',
        'data' => [
            'user' => [
                'name' => $userInfo['full_name'] ?? $userInfo['username'] ?? 'User',
                'phone' => $userInfo['phone'] ?? '',
                'email' => $userInfo['email'] ?? '',
                'role' => $currentUser['role'] ?? 'staff'
            ],
            'business' => [
                'name' => $userInfo['business_name'] ?? 'Narayana',
                'phone' => '' // Get from settings if needed
            ],
            'daily_report' => [
                'date' => $today,
                'total_income' => (int)$totalIncome,
                'total_expense' => (int)$totalExpense,
                'net_balance' => (int)$netBalance,
                'transaction_count' => count($transactions),
                'transactions' => $transactions ?? []
            ],
            'pos_data' => [
                'count' => count($pos) ?? 0,
                'list' => $pos ?? []
            ],
            'admin_contact' => $adminInfo ? [
                'id' => $adminInfo['id'] ?? null,
                'phone' => $adminInfo['phone'] ?? '',
                'email' => $adminInfo['email'] ?? ''
            ] : null,
            'whatsapp_number' => $settings['whatsapp_number'] ?? ''
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    // Log error untuk debugging
    error_log('End Shift Error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal mengambil data laporan: ' . $e->getMessage(),
        'debug' => [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>
