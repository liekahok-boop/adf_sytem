<?php
/**
 * API: Check-in Guest
 * Update booking status to 'checked_in' and record check-in time
 */

// Suppress all errors and warnings to prevent non-JSON output
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering to catch any unexpected output
ob_start();

define('APP_ACCESS', true);
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

// Clean any output that might have been generated
ob_clean();

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!$auth->hasPermission('frontdesk')) {
    echo json_encode(['success' => false, 'message' => 'No permission']);
    exit;
}

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();

try {
    // Get booking ID from request
    $bookingId = $_POST['booking_id'] ?? null;
    
    if (!$bookingId) {
        throw new Exception('Booking ID is required');
    }
    
    // Get booking details
    $booking = $db->fetchOne("
        SELECT b.*, g.guest_name, r.room_number 
        FROM bookings b
        LEFT JOIN guests g ON b.guest_id = g.id
        LEFT JOIN rooms r ON b.room_id = r.id
        WHERE b.id = ?
    ", [$bookingId]);
    
    if (!$booking) {
        throw new Exception('Booking not found');
    }
    
    // Check if already checked in
    if ($booking['status'] === 'checked_in') {
        throw new Exception('Guest sudah check-in sebelumnya');
    }
    
    // Check if booking is confirmed or pending
    if (!in_array($booking['status'], ['confirmed', 'pending'])) {
        throw new Exception('Booking status tidak valid untuk check-in');
    }
    
    // Start transaction
    $db->beginTransaction();
    
    // Update booking status to checked_in
    $db->query("
        UPDATE bookings 
        SET status = 'checked_in',
            actual_checkin_time = NOW(),
            checked_in_by = ?,
            updated_at = NOW()
        WHERE id = ?
    ", [$currentUser['id'], $bookingId]);
    
    // Update room status to occupied
    $db->query("
        UPDATE rooms 
        SET status = 'occupied',
            current_guest_id = ?,
            updated_at = NOW()
        WHERE id = ?
    ", [$booking['guest_id'], $booking['room_id']]);
    
    // Log activity
    $db->query("
        INSERT INTO activity_logs (user_id, action, description, created_at)
        VALUES (?, ?, ?, NOW())
    ", [
        $currentUser['id'],
        'check_in',
        "Check-in guest: {$booking['guest_name']} - Room {$booking['room_number']} - Booking #{$booking['booking_code']}"
    ]);
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Check-in berhasil! {$booking['guest_name']} - Room {$booking['room_number']}",
        'booking_id' => $bookingId,
        'guest_name' => $booking['guest_name'],
        'room_number' => $booking['room_number']
    ]);
    
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    error_log("Check-in Error: " . $e->getMessage());
    
    // Clean output buffer before sending JSON
    ob_clean();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Flush output buffer
ob_end_flush();
