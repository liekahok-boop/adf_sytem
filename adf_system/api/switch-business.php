<?php
/**
 * API Endpoint: Switch Active Business
 * Handles business switching via AJAX
 */

define('APP_ACCESS', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/business_helper.php';
require_once __DIR__ . '/../includes/business_access.php';

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

$currentUser = $auth->getCurrentUser();

// Check if business_id is provided
if (!isset($_POST['business_id']) || empty($_POST['business_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Business ID is required.'
    ]);
    exit;
}

$businessId = sanitize($_POST['business_id']);

// Validate business exists first
$businessFile = __DIR__ . '/../config/businesses/' . $businessId . '.php';
if (!file_exists($businessFile)) {
    echo json_encode([
        'success' => false,
        'message' => 'Business not found. Invalid business ID: ' . $businessId
    ]);
    exit;
}

// Check if user has access to this business
$businessAccess = json_decode($currentUser['business_access'] ?? '[]', true);
$isOwnerOrAdmin = ($currentUser['role'] === 'owner' || $currentUser['role'] === 'admin');

// Owner and Admin can access all businesses
if (!$isOwnerOrAdmin) {
    // Regular users must have explicit access
    if (!in_array($businessId, $businessAccess)) {
        echo json_encode([
            'success' => false,
            'message' => 'Access denied. You do not have permission to access this business.'
        ]);
        exit;
    }
}

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
