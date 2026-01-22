<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invoice_id = (int)$_POST['invoice_id'];
    $payment_method = $_POST['payment_method'];
    $notes = $_POST['notes'] ?? '';
    
    // Debug log
    error_log("Payment attempt - Invoice ID: $invoice_id, Method: $payment_method");
    
    try {
        // Start transaction
        $db->getConnection()->beginTransaction();
        
        // Get invoice data
        $invoice = $db->fetchOne("
            SELECT si.*, d.division_name 
            FROM sales_invoices_header si
            LEFT JOIN divisions d ON si.division_id = d.id
            WHERE si.id = ?
        ", [$invoice_id]);
        
        error_log("Invoice fetched: " . print_r($invoice, true));
        
        if (!$invoice) {
            throw new Exception('Invoice tidak ditemukan');
        }
        
        if ($invoice['payment_status'] === 'paid') {
            throw new Exception('Invoice sudah dibayar');
        }
        
        // Update invoice status to paid - NO payment_date column
        error_log("Updating invoice status to paid...");
        $updateQuery = "UPDATE sales_invoices_header SET payment_status = 'paid', payment_method = ? WHERE id = ?";
        $stmt = $db->getConnection()->prepare($updateQuery);
        $stmt->execute([$payment_method, $invoice_id]);
        $rowsAffected = $stmt->rowCount();
        
        error_log("Rows affected: " . $rowsAffected);
        
        if ($rowsAffected === 0) {
            throw new Exception('Gagal update status invoice - invoice tidak ditemukan atau sudah paid');
        }
        
        // Record to cash_book (Buku Kas Besar) - correct table name
        $cashbook_data = [
            'transaction_date' => date('Y-m-d'),
            'transaction_time' => date('H:i:s'),
            'division_id' => $invoice['division_id'],
            'category_id' => null, // Will use default income category
            'amount' => $invoice['total_amount'],
            'transaction_type' => 'income',
            'payment_method' => $payment_method,
            'description' => 'Pembayaran Invoice ' . $invoice['invoice_number'] . 
                           ($invoice['customer_name'] ? ' - ' . $invoice['customer_name'] : '') .
                           ($notes ? ' (' . $notes . ')' : ''),
            'created_by' => $currentUser['id'],
            'source_type' => 'sales_invoice',
            'source_id' => $invoice_id,
            'is_editable' => 0
        ];
        
        // Try to get "Penjualan" or "Pendapatan" category - correct column name
        $category = $db->fetchOne("
            SELECT id FROM categories 
            WHERE category_name IN ('Penjualan', 'Pendapatan', 'Sales', 'Revenue') 
            AND transaction_type = 'income' 
            LIMIT 1
        ");
        
        if ($category) {
            $cashbook_data['category_id'] = $category['id'];
        } else {
            // Get any income category as fallback
            $fallback = $db->fetchOne("SELECT id FROM categories WHERE transaction_type = 'income' LIMIT 1");
            $cashbook_data['category_id'] = $fallback ? $fallback['id'] : 1;
        }
        
        error_log("Inserting to cash_book with data: " . print_r($cashbook_data, true));
        $cashbook_id = $db->insert('cash_book', $cashbook_data);
        
        if (!$cashbook_id) {
            throw new Exception('Gagal mencatat pembayaran ke buku kas');
        }
        
        // Commit transaction
        $db->getConnection()->commit();
        
        // Prepare detailed success message
        $success_message = '✅ Pembayaran Berhasil!<br><br>';
        $success_message .= '<strong>Invoice:</strong> ' . $invoice['invoice_number'] . '<br>';
        $success_message .= '<strong>Customer:</strong> ' . $invoice['customer_name'] . '<br>';
        $success_message .= '<strong>Total:</strong> Rp ' . number_format($invoice['total_amount'], 0, ',', '.') . '<br>';
        $success_message .= '<strong>Metode:</strong> ' . ucfirst($payment_method) . '<br><br>';
        $success_message .= '💰 Pembayaran telah tercatat di <strong>Buku Kas Besar</strong> sebagai pendapatan ' . $invoice['division_name'];
        
        $_SESSION['success'] = $success_message;
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        $db->getConnection()->rollBack();
        setFlashMessage('error', 'Error: ' . $e->getMessage());
        header('Location: index.php');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
