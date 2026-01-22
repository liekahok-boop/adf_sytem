<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

$auth = new Auth();
$auth->requireLogin();

// Only admin can reset data
if (!$auth->hasRole('admin')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Hanya admin yang bisa reset data.']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$resetType = $input['reset_type'] ?? '';

if (empty($resetType)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tipe reset tidak valid.']);
    exit;
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $deletedCount = 0;
    $tables = [];
    
    switch ($resetType) {
        case 'accounting':
            // Reset cash_book table (semua transaksi)
            $stmt = $conn->query("SELECT COUNT(*) FROM cash_book");
            $deletedCount = $stmt->fetchColumn();
            $conn->exec("TRUNCATE TABLE cash_book");
            $tables = ['cash_book'];
            $message = "Data accounting berhasil direset. {$deletedCount} transaksi dihapus.";
            break;
            
        case 'employees':
            // Reset employees table
            $stmt = $conn->query("SELECT COUNT(*) FROM employees");
            $deletedCount = $stmt->fetchColumn();
            $conn->exec("TRUNCATE TABLE employees");
            $tables = ['employees'];
            $message = "Data karyawan berhasil direset. {$deletedCount} karyawan dihapus.";
            break;
            
        case 'users':
            // Reset users table (except admin)
            $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role != 'admin'");
            $stmt->execute();
            $deletedCount = $stmt->fetchColumn();
            
            $stmt = $conn->prepare("DELETE FROM users WHERE role != 'admin'");
            $stmt->execute();
            $tables = ['users'];
            $message = "Data user berhasil direset. {$deletedCount} user (selain admin) dihapus.";
            break;
            
        case 'guests':
            // Reset guests table
            $stmt = $conn->query("SELECT COUNT(*) FROM guests");
            $deletedCount = $stmt->fetchColumn();
            $conn->exec("TRUNCATE TABLE guests");
            $tables = ['guests'];
            $message = "Data tamu berhasil direset. {$deletedCount} tamu dihapus.";
            break;
            
        case 'all':
            // Reset everything except settings, divisions, categories
            $allTables = ['cash_book', 'employees', 'guests'];
            
            // Count total records
            $totalCount = 0;
            foreach ($allTables as $table) {
                $stmt = $conn->query("SELECT COUNT(*) FROM {$table}");
                $totalCount += $stmt->fetchColumn();
            }
            
            // Truncate all
            foreach ($allTables as $table) {
                $conn->exec("TRUNCATE TABLE {$table}");
            }
            
            // Delete non-admin users
            $stmt = $conn->prepare("DELETE FROM users WHERE role != 'admin'");
            $stmt->execute();
            $deletedUsers = $stmt->rowCount();
            
            $deletedCount = $totalCount + $deletedUsers;
            $tables = array_merge($allTables, ['users (non-admin)']);
            $message = "Semua data berhasil direset. Total {$deletedCount} record dihapus.";
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Tipe reset tidak valid.']);
            exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'deleted_count' => $deletedCount,
        'tables' => $tables
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error saat reset data: ' . $e->getMessage()
    ]);
}
