<?php
/**
 * SEND WHATSAPP REPORT API - STANDALONE
 * Generates WhatsApp message and returns URL for Web app
 */

// Prevent output buffering issues
ob_start();

// Set JSON header IMMEDIATELY
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Clear and start fresh buffer
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
        throw new Exception('Unauthorized');
    }
    
    // Get request data
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }
    
    $phoneNumber = isset($data['phone']) ? preg_replace('/[^0-9]/', '', $data['phone']) : '';
    $reportData = isset($data['report']) ? $data['report'] : array();
    
    if (empty($phoneNumber)) {
        throw new Exception('Phone number is required');
    }
    
    // Format WhatsApp message
    $message = "*Laporan Harian Shift*\n";
    $message .= "Tanggal: " . (isset($reportData['date']) ? $reportData['date'] : date('Y-m-d')) . "\n\n";
    $message .= "*Ringkasan Transaksi:*\n";
    $message .= "Total Pemasukan: Rp " . number_format($reportData['total_income'] ?? 0) . "\n";
    $message .= "Total Pengeluaran: Rp " . number_format($reportData['total_expense'] ?? 0) . "\n";
    $message .= "Saldo Bersih: Rp " . number_format($reportData['net_balance'] ?? 0) . "\n\n";
    
    if (isset($reportData['transaction_count']) && $reportData['transaction_count'] > 0) {
        $message .= "Jumlah Transaksi: " . $reportData['transaction_count'] . "\n";
    }
    
    if (isset($reportData['pos_count']) && $reportData['pos_count'] > 0) {
        $message .= "Jumlah PO: " . $reportData['pos_count'] . "\n";
    }
    
    $message .= "\nLihat detail di aplikasi.";
    
    // URL encode the message
    $encodedMessage = urlencode($message);
    $whatsappUrl = "https://wa.me/" . $phoneNumber . "?text=" . $encodedMessage;
    
    // Build success response
    $response = array(
        'status' => 'success',
        'data' => array(
            'whatsapp_web_url' => $whatsappUrl,
            'phone' => $phoneNumber,
            'message' => $message,
            'instruction' => 'Open WhatsApp Web and send message to GM/Admin'
        )
    );
    
} catch (Exception $e) {
    http_response_code(500);
    $response = array(
        'status' => 'error',
        'message' => $e->getMessage()
    );
}

// Output JSON only
ob_end_clean();
echo json_encode($response);
exit;
