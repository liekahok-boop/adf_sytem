<?php
/**
 * Debug: Check if menus should appear
 */

session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();

echo "<h1>🔍 DEBUG: Menu Visibility Check</h1>";
echo "<hr>";

// Check login status
echo "<h3>Login Status:</h3>";
echo "isLoggedIn(): " . ($auth->isLoggedIn() ? "TRUE ✅" : "FALSE ❌") . "<br>";
echo "User Role: " . ($_SESSION['role'] ?? 'NOT SET') . "<br>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "<br>";
echo "<hr>";

// Check permissions
echo "<h3>Permission Checks:</h3>";

$permissions_to_check = [
    'dashboard',
    'cashbook',
    'divisions',
    'frontdesk',
    'sales_invoice',
    'users',
    'reports',
    'procurement',
    'settings',
    'investor' => '⭐ INVESTOR MENU',
    'project' => '⭐ PROJECT MENU'
];

foreach ($permissions_to_check as $perm => $label) {
    $has_perm = $auth->hasPermission($perm);
    $label = is_numeric($perm) ? ucfirst(str_replace('_', ' ', $label)) : $label;
    
    echo "<div style='padding: 8px; margin: 5px 0; background: " . ($has_perm ? '#d1fae5' : '#fee2e2') . "; border-left: 3px solid " . ($has_perm ? '#10b981' : '#ef4444') . ";'>";
    echo ($has_perm ? "✅" : "❌") . " $label: " . ($has_perm ? "TRUE" : "FALSE");
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='" . BASE_URL . "/index.php'>← Back to Dashboard</a></p>";
?>
