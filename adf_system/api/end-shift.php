<?php
/**
 * END SHIFT - Generate Daily Report, PO Data, and WhatsApp Integration
 */

define('APP_ACCESS', true);
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();
$today = date('Y-m-d');

try {
    // Get today's transactions
    $transactions = $db->fetchAll("
        SELECT 
            cb.id,
            cb.transaction_date,
            cb.transaction_type,
            cb.amount,
            cb.description,
            cb.division_id,
            d.division_name
        FROM cash_book cb
        LEFT JOIN divisions d ON cb.division_id = d.id
        WHERE DATE(cb.transaction_date) = :date
        ORDER BY cb.transaction_date DESC
    ", ['date' => $today]);

    // Calculate daily totals
    $totalIncome = 0;
    $totalExpense = 0;

    foreach ($transactions as $trans) {
        if ($trans['transaction_type'] === 'income') {
            $totalIncome += $trans['amount'];
        } else {
            $totalExpense += $trans['amount'];
        }
    }

    $netBalance = $totalIncome - $totalExpense;

    // Get POs created today
    $pos = $db->fetchAll("
        SELECT 
            po.id,
            po.po_number,
            po.supplier_id,
            po.total_amount,
            po.status,
            po.created_at,
            s.supplier_name,
            pi.image_path
        FROM purchase_orders po
        LEFT JOIN suppliers s ON po.supplier_id = s.id
        LEFT JOIN po_images pi ON po.id = pi.po_id AND pi.is_primary = 1
        WHERE DATE(po.created_at) = :date
        ORDER BY po.created_at DESC
    ", ['date' => $today]);

    // Get user and business info
    $userInfo = $db->fetchOne("
        SELECT u.*, b.business_name, b.phone as business_phone
        FROM users u
        LEFT JOIN businesses b ON u.business_id = b.id
        WHERE u.id = ?
    ", [$currentUser['id']]);

    // Get admin/GM contact info
    $adminInfo = $db->fetchOne("
        SELECT phone, email FROM users WHERE role IN ('admin', 'gm', 'general_manager') LIMIT 1
    ");

    // Get business settings for WhatsApp number
    $settings = $db->fetchOne("
        SELECT * FROM business_settings WHERE business_id = ?
    ", [$currentUser['business_id'] ?? 1]);

    $response = [
        'status' => 'success',
        'data' => [
            'user' => [
                'name' => $userInfo['full_name'] ?? $userInfo['username'],
                'phone' => $userInfo['phone'] ?? '',
                'role' => $currentUser['role']
            ],
            'business' => [
                'name' => $userInfo['business_name'] ?? 'Narayana',
                'phone' => $userInfo['business_phone'] ?? ''
            ],
            'daily_report' => [
                'date' => $today,
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'net_balance' => $netBalance,
                'transaction_count' => count($transactions),
                'transactions' => $transactions
            ],
            'pos_data' => [
                'count' => count($pos),
                'list' => $pos
            ],
            'admin_contact' => $adminInfo,
            'whatsapp_number' => $settings['whatsapp_number'] ?? ''
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
