<?php
/**
 * Send Daily Report to WhatsApp (GM/Admin)
 */

// Set header first
header('Content-Type: application/json');

define('APP_ACCESS', true);

// Load config files
try {
    require_once '../config/config.php';
    require_once '../config/database.php';
    require_once '../includes/auth.php';
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
            'message' => 'Unauthorized'
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
        'message' => 'Authentication error'
    ]);
    exit;
}

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $totalIncome = $data['total_income'] ?? 0;
    $totalExpense = $data['total_expense'] ?? 0;
    $netBalance = $data['net_balance'] ?? 0;
    $userName = $data['user_name'] ?? 'User';
    $transactionCount = $data['transaction_count'] ?? 0;
    $poCount = $data['po_count'] ?? 0;
    $businessName = $data['business_name'] ?? 'Narayana';
    $adminPhone = $data['admin_phone'] ?? '';
    
    // Format currency
    $formattedIncome = 'Rp ' . number_format($totalIncome, 0, ',', '.');
    $formattedExpense = 'Rp ' . number_format($totalExpense, 0, ',', '.');
    $formattedBalance = 'Rp ' . number_format($netBalance, 0, ',', '.');
    
    // Build WhatsApp message
    $message = "*📊 LAPORAN END SHIFT - " . $businessName . "*\n";
    $message .= "📅 " . date('d M Y H:i') . "\n";
    $message .= "👤 Shift Officer: " . $userName . "\n\n";
    
    $message .= "*💰 RINGKASAN TRANSAKSI:*\n";
    $message .= "✅ Total Pemasukan: " . $formattedIncome . "\n";
    $message .= "❌ Total Pengeluaran: " . $formattedExpense . "\n";
    $message .= "📈 Saldo Bersih: " . $formattedBalance . "\n";
    $message .= "🔢 Jumlah Transaksi: " . $transactionCount . "\n";
    
    if ($poCount > 0) {
        $message .= "\n*📦 PO HARI INI:*\n";
        $message .= "🔗 Jumlah PO: " . $poCount . "\n";
        $message .= "📸 Lihat detail PO di dashboard\n";
    }
    
    $message .= "\n_Laporan otomatis dari sistem_";
    
    // Encode message for URL
    $urlMessage = urlencode($message);
    
    // WhatsApp API options:
    // 1. Direct WhatsApp Web Link (no API needed)
    // 2. WhatsApp Business API (paid)
    // 3. Twilio or similar service
    
    // For now, we'll use WhatsApp Web Link method
    // User will see dialog to confirm send
    $whatsappUrl = "https://wa.me/" . str_replace(['+', ' ', '-', '(', ')'], '', $adminPhone) . "?text=" . $urlMessage;
    
    // Log the send action
    $db->execute("
        INSERT INTO shift_logs (user_id, action, data, created_at)
        VALUES (?, ?, ?, NOW())
    ", [
        $currentUser['id'],
        'end_shift_wa_send',
        json_encode([
            'phone' => $adminPhone,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ])
    ]);

    echo json_encode([
        'status' => 'success',
        'whatsapp_url' => $whatsappUrl,
        'message' => $message,
        'phone' => $adminPhone
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
