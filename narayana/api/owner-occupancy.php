<?php
/**
 * API: Owner Occupancy
 * Get room occupancy statistics
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

try {
    // Build WHERE clause for branch filter
    $branchWhere = '';
    $params = [];
    if ($branchId) {
        $branchWhere = ' WHERE r.branch_id = :branch_id';
        $params['branch_id'] = $branchId;
    }
    
    // Get room statistics
    $roomStats = $db->fetchOne(
        "SELECT 
            COUNT(*) as total_rooms,
            COUNT(CASE WHEN r.status = 'occupied' THEN 1 END) as occupied_rooms,
            COUNT(CASE WHEN r.status = 'available' THEN 1 END) as available_rooms,
            COUNT(CASE WHEN r.status = 'maintenance' THEN 1 END) as maintenance_rooms
         FROM frontdesk_rooms r" . $branchWhere,
        $params
    );
    
    $totalRooms = (int)$roomStats['total_rooms'];
    $occupiedRooms = (int)$roomStats['occupied_rooms'];
    $availableRooms = (int)$roomStats['available_rooms'];
    $maintenanceRooms = (int)$roomStats['maintenance_rooms'];
    
    $occupancyRate = $totalRooms > 0 ? ($occupiedRooms / $totalRooms) * 100 : 0;
    
    // Get today's check-ins and check-outs
    $today = date('Y-m-d');
    
    $todayActivity = $db->fetchOne(
        "SELECT 
            COUNT(CASE WHEN DATE(check_in_date) = :today THEN 1 END) as today_checkins,
            COUNT(CASE WHEN DATE(check_out_date) = :today THEN 1 END) as today_checkouts
         FROM frontdesk_reservations
         WHERE status IN ('checked_in', 'checked_out')" . ($branchId ? ' AND branch_id = :branch_id' : ''),
        $branchId ? ['today' => $today, 'branch_id' => $branchId] : ['today' => $today]
    );
    
    echo json_encode([
        'success' => true,
        'total_rooms' => $totalRooms,
        'occupied_rooms' => $occupiedRooms,
        'available_rooms' => $availableRooms,
        'maintenance_rooms' => $maintenanceRooms,
        'occupancy_rate' => round($occupancyRate, 1),
        'today_checkins' => (int)$todayActivity['today_checkins'],
        'today_checkouts' => (int)$todayActivity['today_checkouts'],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
