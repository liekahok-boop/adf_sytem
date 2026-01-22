<?php
/**
 * API Endpoint: Switch Active Business
 * Handles business switching via AJAX
 */

define('APP_ACCESS', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/business_helper.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please login first.'
    ]);
    exit;
}

// Check if business_id is provided
if (!isset($_POST['business_id']) || empty($_POST['business_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Business ID is required.'
    ]);
    exit;
}

$businessId = sanitize($_POST['business_id']);

// Attempt to switch business
if (setActiveBusinessId($businessId)) {
    $businessName = getBusinessDisplayName($businessId);
    
    echo json_encode([
        'success' => true,
        'message' => "Successfully switched to: {$businessName}",
        'business_id' => $businessId,
        'business_name' => $businessName
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Business not found or invalid business ID.'
    ]);
}
