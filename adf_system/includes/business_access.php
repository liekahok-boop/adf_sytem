<?php
/**
 * Business Access Control Middleware
 * Check if user has access to selected business
 */

if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}

/**
 * Check if current user has access to active business
 * @return bool
 */
function checkBusinessAccess() {
    global $auth;
    
    if (!isset($auth) || !$auth->isLoggedIn()) {
        return false;
    }
    
    $currentUser = $auth->getCurrentUser();
    
    // Owner and admin have access to all businesses
    if ($currentUser['role'] === 'owner' || $currentUser['role'] === 'admin') {
        return true;
    }
    
    // Check if user has specific business access
    $businessAccess = json_decode($currentUser['business_access'] ?? '[]', true);
    
    if (empty($businessAccess)) {
        // If no business_access set, deny access (secure by default)
        return false;
    }
    
    // Check if user has access to current business
    return in_array(ACTIVE_BUSINESS_ID, $businessAccess);
}

/**
 * Require business access or redirect
 */
function requireBusinessAccess() {
    if (!checkBusinessAccess()) {
        $_SESSION['error'] = 'Anda tidak memiliki akses ke bisnis ini. Silakan hubungi administrator.';
        header('Location: ' . BASE_URL . '/logout.php');
        exit;
    }
}

/**
 * Get available businesses for current user
 * @return array Filtered list of businesses user can access
 */
function getUserAvailableBusinesses() {
    global $auth;
    
    $allBusinesses = getAvailableBusinesses();
    
    if (!isset($auth) || !$auth->isLoggedIn()) {
        return [];
    }
    
    $currentUser = $auth->getCurrentUser();
    
    // Owner and admin can access all businesses
    if ($currentUser['role'] === 'owner' || $currentUser['role'] === 'admin') {
        return $allBusinesses;
    }
    
    // Filter businesses based on user access
    $businessAccess = json_decode($currentUser['business_access'] ?? '[]', true);
    
    if (empty($businessAccess)) {
        return []; // No access
    }
    
    $filtered = [];
    foreach ($allBusinesses as $bizId => $bizConfig) {
        if (in_array($bizId, $businessAccess)) {
            $filtered[$bizId] = $bizConfig;
        }
    }
    
    return $filtered;
}
