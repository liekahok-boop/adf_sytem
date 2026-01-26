<?php
/**
 * Test Permission System
 */

session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// Simulate logged-in admin user for testing
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['full_name'] = 'Administrator';
$_SESSION['role'] = 'admin';

$auth = new Auth();

echo "<h1>🧪 Test Permission System</h1>";
echo "<hr>";

// Test permissions
$test_permissions = [
    'dashboard',
    'cashbook',
    'divisions',
    'frontdesk',
    'sales_invoice',
    'users',
    'reports',
    'procurement',
    'settings',
    'investor',
    'project'
];

echo "<h2>Testing hasPermission() method:</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #333; color: white;'><th>Permission</th><th>Result</th><th>Status</th></tr>";

$all_passed = true;

foreach ($test_permissions as $perm) {
    $has_perm = $auth->hasPermission($perm);
    $status = $has_perm ? '✅ PASS' : '❌ FAIL';
    
    if (!$has_perm) {
        $all_passed = false;
    }
    
    echo "<tr>";
    echo "<td><strong>" . ucfirst(str_replace('_', ' ', $perm)) . "</strong></td>";
    echo "<td>" . ($has_perm ? 'TRUE' : 'FALSE') . "</td>";
    echo "<td style='color: " . ($has_perm ? 'green' : 'red') . ";'>" . $status . "</td>";
    echo "</tr>";
}

echo "</table>";
echo "<hr>";

if ($all_passed) {
    echo "<h2 style='color: green;'>✅ SEMUA TEST PASSED!</h2>";
    echo "<p>Permission system bekerja dengan sempurna!</p>";
} else {
    echo "<h2 style='color: red;'>❌ ADA TEST YANG FAILED!</h2>";
    echo "<p>Periksa database dan Auth class</p>";
}

echo "<hr>";
echo "<p><a href='" . BASE_URL . "/index.php'>← Kembali ke Dashboard</a></p>";
?>
