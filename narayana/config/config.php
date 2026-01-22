<?php
/**
 * NARAYANA HOTEL MANAGEMENT SYSTEM
 * Configuration File
 */

// Prevent direct access
defined('APP_ACCESS') or define('APP_ACCESS', true);

// ============================================
// APPLICATION SETTINGS
// ============================================
define('APP_NAME', 'Narayana Hotel Management');
define('APP_VERSION', '1.0.0');
define('APP_YEAR', '2026');
define('DEVELOPER_NAME', 'Ariefsystemdesign.net');
define('DEVELOPER_LOGO', 'assets/img/developer-logo.png'); // Hardcoded, tidak bisa diganti dari settings

// ============================================
// DATABASE CONFIGURATION
// ============================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'narayana_db');
define('DB_USER', 'root');
define('DB_PASS', ''); // Change this in production!
define('DB_CHARSET', 'utf8mb4');

// ============================================
// PATH CONFIGURATION
// ============================================
define('BASE_PATH', dirname(dirname(__FILE__)));

// Auto-detect port
$port = $_SERVER['SERVER_PORT'] ?? '80';
$portSuffix = ($port != '80' && $port != '443') ? ':' . $port : '';
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
define('BASE_URL', $protocol . '://' . $_SERVER['HTTP_HOST'] . '/narayana');

// ============================================
// SESSION CONFIGURATION
// ============================================
define('SESSION_NAME', 'NARAYANA_SESSION');
define('SESSION_LIFETIME', 3600 * 8); // 8 hours

// ============================================
// TIMEZONE
// ============================================
date_default_timezone_set('Asia/Jakarta'); // Adjust to your timezone

// ============================================
// ERROR REPORTING (Development)
// ============================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// For production, use:
// error_reporting(0);
// ini_set('display_errors', 0);

// ============================================
// CURRENCY FORMAT
// ============================================
define('CURRENCY_SYMBOL', 'Rp');
define('CURRENCY_DECIMAL', 0);

// ============================================
// PAGINATION
// ============================================
define('RECORDS_PER_PAGE', 25);

// ============================================
// DATE FORMAT
// ============================================
define('DATE_FORMAT', 'd/m/Y');
define('DATETIME_FORMAT', 'd/m/Y H:i');
define('TIME_FORMAT', 'H:i');

// ============================================
// MULTI-BUSINESS CONFIGURATION (SESSION-BASED)
// ============================================
// Start session for business selection
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Load business helper functions
require_once __DIR__ . '/../includes/business_helper.php';

// Get active business from session
$activeBusinessId = getActiveBusinessId();
$BUSINESS_CONFIG = getActiveBusinessConfig();

// Make business config globally available
define('ACTIVE_BUSINESS_ID', $activeBusinessId);
define('BUSINESS_NAME', $BUSINESS_CONFIG['name']);
define('BUSINESS_TYPE', $BUSINESS_CONFIG['business_type']);
define('BUSINESS_ICON', $BUSINESS_CONFIG['theme']['icon']);
define('BUSINESS_COLOR', $BUSINESS_CONFIG['theme']['color_primary']);

?>
