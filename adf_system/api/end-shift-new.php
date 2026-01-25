<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die('{"status":"error","message":"Unauthorized"}');
}

$today = date('Y-m-d');
$userId = (int)$_SESSION['user_id'];

$conn = new mysqli('localhost', 'root', '', 'adf_system');
if ($conn->connect_error) {
    http_response_code(500);
    die('{"status":"error","message":"DB error"}');
}
$conn->set_charset('utf8');

$transactions = [];
$totalIncome = 0;
$totalExpense = 0;

$stmt = $conn->prepare("SELECT id, amount, transaction_type FROM cash_book WHERE DATE(transaction_date) = ?");
if ($stmt) {
    $stmt->bind_param('s', $today);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
        $amt = (float)$row['amount'];
        if ($row['transaction_type'] === 'income') $totalIncome += $amt;
        else $totalExpense += $amt;
    }
    $stmt->close();
}

$pos = [];
$stmt = $conn->prepare("SELECT id, po_number FROM purchase_orders WHERE DATE(created_at) = ?");
if ($stmt) {
    $stmt->bind_param('s', $today);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $pos[] = $row;
    $stmt->close();
}

$user = [];
$stmt = $conn->prepare("SELECT full_name, username FROM users WHERE id = ?");
if ($stmt) {
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc() ?: [];
    $stmt->close();
}

$conn->close();

echo json_encode([
    'status' => 'success',
    'data' => [
        'user' => ['name' => $user['full_name'] ?? $user['username'] ?? 'User'],
        'daily_report' => [
            'date' => $today,
            'total_income' => (int)$totalIncome,
            'total_expense' => (int)$totalExpense,
            'net_balance' => (int)($totalIncome - $totalExpense),
            'transaction_count' => count($transactions)
        ],
        'pos_data' => ['count' => count($pos), 'list' => $pos]
    ]
]);
exit;
