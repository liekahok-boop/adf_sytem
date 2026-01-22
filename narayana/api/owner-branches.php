<?php
/**
 * API: Owner Branches
 * Get list of branches accessible by owner
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
$db = Database::getInstance();

// Check if user is owner or admin
if ($currentUser['role'] !== 'owner' && $currentUser['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

try {
    // If admin, show all branches
    if ($currentUser['role'] === 'admin') {
        $branches = $db->fetchAll(
            "SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name"
        );
    } else {
        // If owner, show only accessible branches
        $branches = $db->fetchAll(
            "SELECT b.* FROM branches b
             INNER JOIN owner_branch_access oba ON b.id = oba.branch_id
             WHERE oba.user_id = ? AND b.is_active = 1
             ORDER BY b.branch_name",
            [$currentUser['id']]
        );
    }
    
    echo json_encode([
        'success' => true,
        'branches' => $branches,
        'count' => count($branches)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
