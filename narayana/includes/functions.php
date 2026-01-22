<?php
/**
 * NARAYANA HOTEL MANAGEMENT SYSTEM
 * Helper Functions
 */

defined('APP_ACCESS') or define('APP_ACCESS', true);

/**
 * Format Currency (Indonesian Rupiah)
 */
function formatCurrency($amount) {
    return CURRENCY_SYMBOL . ' ' . number_format($amount, CURRENCY_DECIMAL, ',', '.');
}

/**
 * Format Date
 */
function formatDate($date, $format = DATE_FORMAT) {
    return date($format, strtotime($date));
}

/**
 * Format DateTime
 */
function formatDateTime($datetime, $format = DATETIME_FORMAT) {
    return date($format, strtotime($datetime));
}

/**
 * Sanitize Input
 */
function sanitize($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitize($value);
        }
        return $data;
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Set Flash Message
 */
function setFlashMessage($type, $message) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get Flash Message
 */
function getFlashMessage() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Display Flash Message
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $alertClass = $flash['type'] === 'success' ? 'alert-success' : 'alert-danger';
        echo "<div class='alert {$alertClass}' style='margin-bottom: 1rem;'>{$flash['message']}</div>";
    }
}

/**
 * Get Flash Message
 */
function getFlash($key) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    
    return null;
}

/**
 * Set Flash Message
 */
function setFlash($key, $message) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['flash'][$key] = $message;
}

/**
 * JSON Response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Get Request Method
 */
function getRequestMethod() {
    return $_SERVER['REQUEST_METHOD'];
}

/**
 * Is POST Request
 */
function isPost() {
    return getRequestMethod() === 'POST';
}

/**
 * Is GET Request
 */
function isGet() {
    return getRequestMethod() === 'GET';
}

/**
 * Get POST Data
 */
function getPost($key = null, $default = null) {
    if ($key === null) {
        return $_POST;
    }
    return $_POST[$key] ?? $default;
}

/**
 * Get GET Data
 */
function getGet($key = null, $default = null) {
    if ($key === null) {
        return $_GET;
    }
    return $_GET[$key] ?? $default;
}

/**
 * Debug Print (Development only)
 */
function dd($data, $die = true) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    if ($die) {
        die();
    }
}

/**
 * Generate Random String
 */
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Check if date is today
 */
function isToday($date) {
    return date('Y-m-d', strtotime($date)) === date('Y-m-d');
}

/**
 * Get Date Range
 */
function getDateRange($period = 'today') {
    $start = '';
    $end = date('Y-m-d');
    
    switch ($period) {
        case 'today':
            $start = date('Y-m-d');
            break;
        case 'yesterday':
            $start = $end = date('Y-m-d', strtotime('-1 day'));
            break;
        case 'week':
            $start = date('Y-m-d', strtotime('-7 days'));
            break;
        case 'month':
            $start = date('Y-m-01');
            break;
        case 'year':
            $start = date('Y-01-01');
            break;
    }
    
    return ['start' => $start, 'end' => $end];
}

/**
 * Pagination
 */
function paginate($total, $perPage, $currentPage = 1) {
    $totalPages = ceil($total / $perPage);
    $currentPage = max(1, min($totalPages, $currentPage));
    $offset = ($currentPage - 1) * $perPage;
    
    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * Active Menu Class
 */
function activeMenu($page) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    $currentPath = $_SERVER['PHP_SELF'];
    
    // For dashboard - only match if in root directory
    if ($page === 'index.php') {
        // Check if we're in root directory (not in modules folder)
        if ($currentPage === 'index.php' && strpos($currentPath, '/modules/') === false) {
            return 'active';
        }
        return '';
    }
    
    // For settings index page
    if ($page === 'settings-index') {
        if (strpos($currentPath, '/settings/') !== false && $currentPage === 'index.php') {
            return 'active';
        }
        return '';
    }
    
    // For reports menu - mark active if in any reports page
    if ($page === 'reports') {
        $reportPages = ['daily.php', 'monthly.php', 'yearly.php', 'detailed.php', 'by-division.php', 'index.php'];
        if (strpos($currentPath, '/reports/') !== false && in_array($currentPage, $reportPages)) {
            return 'active';
        }
        return '';
    }
    
    // Exact match for specific file names (daily.php, monthly.php, etc)
    if ($currentPage === $page) {
        return 'active';
    }
    
    // For module folders - check if path contains the module name
    if (strpos($currentPath, '/' . $page . '/') !== false) {
        return 'active';
    }
    
    return '';
}

?>
