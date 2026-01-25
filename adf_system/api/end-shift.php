<?php
/**
 * END SHIFT API - STANDALONE
 * Returns daily report data as JSON only
 */

// Prevent output buffering issues
ob_start();

// Set JSON header IMMEDIATELY - before any other output
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Clear any buffered output
ob_end_clean();
ob_start();

$response = array('status' => 'error', 'message' => 'Unknown error');

try {
    // Start session if needed
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check authentication
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        throw new Exception('Unauthorized - Please login');
    }
    
    $userId = (int)$_SESSION['user_id'];
    $today = date('Y-m-d');
    
    // Direct database connection - NO external includes
    $conn = new mysqli('localhost', 'root', '', 'adf_system');
    if ($conn->connect_error) {
        throw new Exception('DB Error: ' . $conn->connect_error);
    }
    $conn->set_charset('utf8');
    
    // Get transactions
    $stmt = $conn->prepare("
        SELECT 
            cb.id, cb.transaction_date, cb.transaction_type, cb.amount,
            cb.description, cb.division_id, COALESCE(d.division_name, 'N/A') as division_name
        FROM cash_book cb
        LEFT JOIN divisions d ON cb.division_id = d.id
        WHERE DATE(cb.transaction_date) = ?
        ORDER BY cb.transaction_date DESC
    ");
    $stmt->bind_param('s', $today);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $transactions = array();
    $totalIncome = 0;
    $totalExpense = 0;
    
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
        $amount = (float)$row['amount'];
        if ($row['transaction_type'] === 'income') {
            $totalIncome += $amount;
        } else {
            $totalExpense += $amount;
        }
    }
    $stmt->close();
    
    // Get POs
    $stmt = $conn->prepare("
        SELECT 
            po.id, po.po_number, po.supplier_id, po.total_amount, po.status, po.created_at,
            COALESCE(s.supplier_name, 'N/A') as supplier_name,
            pi.image_path
        FROM purchase_orders po
        LEFT JOIN suppliers s ON po.supplier_id = s.id
        LEFT JOIN po_images pi ON po.id = pi.po_id AND pi.is_primary = 1
        WHERE DATE(po.created_at) = ?
        ORDER BY po.created_at DESC
    ");
    $stmt->bind_param('s', $today);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $pos = array();
    while ($row = $result->fetch_assoc()) {
        $pos[] = $row;
    }
    $stmt->close();
    
    // Get user
    $stmt = $conn->prepare("
        SELECT id, username, full_name, email, phone, role, business_id 
        FROM users WHERE id = ?
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $userInfo = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$userInfo) {
        throw new Exception('User not found');
    }
    
    // Get admin
    $stmt = $conn->prepare("
        SELECT id, phone, email FROM users WHERE role IN ('admin', 'gm') LIMIT 1
    ");
    $stmt->execute();
    $adminInfo = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Get WhatsApp number
    $businessId = $userInfo['business_id'] ? (int)$userInfo['business_id'] : 1;
    $stmt = $conn->prepare("
        SELECT whatsapp_number FROM business_settings WHERE business_id = ?
    ");
    $stmt->bind_param('i', $businessId);
    $stmt->execute();
    $settingsResult = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    $conn->close();
    
    // Build success response
    $response = array(
        'status' => 'success',
        'data' => array(
            'user' => array(
                'name' => $userInfo['full_name'] ?: ($userInfo['username'] ?: 'User'),
                'phone' => $userInfo['phone'] ?: '',
                'email' => $userInfo['email'] ?: '',
                'role' => $userInfo['role'] ?: 'staff'
            ),
            'business' => array(
                'name' => 'Narayana',
                'phone' => ''
            ),
            'daily_report' => array(
                'date' => $today,
                'total_income' => (int)$totalIncome,
                'total_expense' => (int)$totalExpense,
                'net_balance' => (int)($totalIncome - $totalExpense),
                'transaction_count' => count($transactions),
                'transactions' => $transactions
            ),
            'pos_data' => array(
                'count' => count($pos),
                'list' => $pos
            ),
            'admin_contact' => $adminInfo ? array(
                'id' => $adminInfo['id'] ?: null,
                'phone' => $adminInfo['phone'] ?: '',
                'email' => $adminInfo['email'] ?: ''
            ) : null,
            'whatsapp_number' => $settingsResult['whatsapp_number'] ?: ''
        )
    );
    
} catch (Exception $e) {
    http_response_code(500);
    $response = array(
        'status' => 'error',
        'message' => $e->getMessage()
    );
}

// Output JSON only - clear buffer first
ob_end_clean();
echo json_encode($response);
exit;
