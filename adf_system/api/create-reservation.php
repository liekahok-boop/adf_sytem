<?php
/**
 * API: CREATE RESERVATION
 * Handles reservation creation from calendar
 */

define('APP_ACCESS', true);
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

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

try {
    // Validate required fields
    $required = ['guest_name', 'check_in_date', 'check_out_date', 'room_id', 'room_price', 'booking_source'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field $field is required");
        }
    }
    
    // Get form data
    $guestName = trim($_POST['guest_name']);
    $guestPhone = trim($_POST['guest_phone'] ?? '');
    $guestEmail = trim($_POST['guest_email'] ?? '');
    $guestIdNumber = trim($_POST['guest_id_number'] ?? '');
    
    $checkInDate = $_POST['check_in_date'];
    $checkOutDate = $_POST['check_out_date'];
    $roomId = (int)$_POST['room_id'];
    $roomPrice = (float)$_POST['room_price'];
    $totalNights = (int)$_POST['total_nights'];
    $bookingSource = $_POST['booking_source'];
    $discount = (float)($_POST['discount'] ?? 0);
    $totalPrice = (float)$_POST['total_price'];
    $finalPrice = (float)$_POST['final_price'];
    $specialRequest = trim($_POST['special_request'] ?? '');
    $paymentStatus = $_POST['payment_status'] ?? 'unpaid';
    $paidAmount = (float)($_POST['paid_amount'] ?? 0);
    
    // Map booking source to database enum values
    $sourceMap = [
        'walk_in' => 'walk_in',
        'phone' => 'phone',
        'online' => 'online',
        'agoda' => 'ota',
        'booking' => 'ota',
        'tiket' => 'ota',
        'airbnb' => 'ota',
        'ota' => 'ota'
    ];
    $bookingSource = $sourceMap[$bookingSource] ?? 'walk_in';
    
    // Validate dates
    $checkIn = new DateTime($checkInDate);
    $checkOut = new DateTime($checkOutDate);
    
    if ($checkOut <= $checkIn) {
        throw new Exception("Check-out date must be after check-in date");
    }
    
    // Calculate nights if not provided
    if ($totalNights == 0) {
        $interval = $checkIn->diff($checkOut);
        $totalNights = $interval->days;
    }
    
    // Check room availability
    $conflicts = $db->fetchAll("
        SELECT id FROM bookings 
        WHERE room_id = ? 
        AND status != 'cancelled'
        AND (
            (check_in_date < ? AND check_out_date > ?)
            OR (check_in_date >= ? AND check_in_date < ?)
        )
    ", [$roomId, $checkOutDate, $checkInDate, $checkInDate, $checkOutDate]);
    
    if (!empty($conflicts)) {
        throw new Exception("Room is not available for selected dates");
    }
    
    $db->beginTransaction();
    
    // Check if guest exists
    $guest = null;
    if (!empty($guestPhone)) {
        $guest = $db->fetchOne("SELECT id FROM guests WHERE phone = ?", [$guestPhone]);
    } elseif (!empty($guestEmail)) {
        $guest = $db->fetchOne("SELECT id FROM guests WHERE email = ?", [$guestEmail]);
    }
    
    // Create or update guest
    if ($guest) {
        $guestId = $guest['id'];
        // Update guest info
        $db->query("
            UPDATE guests 
            SET guest_name = ?, email = ?, id_card_number = ?, phone = ?
            WHERE id = ?
        ", [$guestName, $guestEmail, $guestIdNumber, $guestPhone, $guestId]);
    } else {
        // Create new guest - make sure id_card_number has value
        $idCardNumber = !empty($guestIdNumber) ? $guestIdNumber : 'TEMP-' . date('YmdHis');
        $db->query("
            INSERT INTO guests (guest_name, phone, email, id_card_number, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ", [$guestName, $guestPhone, $guestEmail, $idCardNumber]);
        $guestId = $db->getConnection()->lastInsertId();
    }
    
    // Generate booking code
    $bookingCode = 'BK-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Check if booking code exists
    $exists = $db->fetchOne("SELECT id FROM bookings WHERE booking_code = ?", [$bookingCode]);
    if ($exists) {
        $bookingCode = 'BK-' . date('YmdHis') . '-' . rand(100, 999);
    }
    
    // Create booking (remove guest_name from INSERT as it doesn't exist in table)
    $db->query("
        INSERT INTO bookings (
            booking_code, guest_id, room_id, 
            check_in_date, check_out_date, total_nights,
            room_price, total_price, discount, final_price,
            booking_source, status, payment_status, paid_amount,
            special_request, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', ?, ?, ?, NOW())
    ", [
        $bookingCode, $guestId, $roomId,
        $checkInDate, $checkOutDate, $totalNights,
        $roomPrice, $totalPrice, $discount, $finalPrice,
        $bookingSource, $paymentStatus, $paidAmount,
        $specialRequest
    ]);
    
    $bookingId = $db->getConnection()->lastInsertId();
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Reservation created successfully',
        'booking_id' => $bookingId,
        'booking_code' => $bookingCode
    ]);
    
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    error_log("Create Reservation Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
