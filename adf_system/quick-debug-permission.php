<?php
/**
 * SIMPLE DEBUG - Check hasPermission
 */

session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();

echo "<h1>🔍 QUICK DEBUG: hasPermission Check</h1>";
echo "<hr>";

if (!$auth->isLoggedIn()) {
    die('<p style="color: red;">❌ NOT LOGGED IN! Please login first.</p>');
}

$user_id = $_SESSION['user_id'] ?? 'unknown';
$username = $_SESSION['username'] ?? 'unknown';
$role = $_SESSION['role'] ?? 'unknown';

echo "<p><strong>Logged in as:</strong> $username (ID: $user_id, Role: $role)</p>";
echo "<hr>";

echo "<h3>Checking hasPermission() for each menu:</h3>";

$menus = [
    'dashboard' => 'Dashboard',
    'cashbook' => 'Buku Kas Besar',
    'divisions' => 'Kelola Divisi',
    'frontdesk' => 'Front Desk',
    'sales_invoice' => 'Sales Invoice',
    'users' => 'Kelola User',
    'reports' => 'Laporan',
    'procurement' => 'Procurement',
    'settings' => 'Settings',
    'investor' => '⭐ INVESTOR',
    'project' => '⭐ PROJECT'
];

$db = Database::getInstance()->getConnection();

foreach ($menus as $key => $label) {
    $has_perm = $auth->hasPermission($key);
    
    // Also check database directly
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM user_permissions WHERE user_id = ? AND permission = ?");
    $stmt->execute([$user_id, $key]);
    $result = $stmt->fetch();
    $in_db = intval($result['count']) > 0;
    
    $bg = $has_perm ? '#d1fae5' : '#fee2e2';
    $border = $has_perm ? '#10b981' : '#ef4444';
    $icon = $has_perm ? '✅' : '❌';
    
    echo "<div style='padding: 10px; margin: 8px 0; background: $bg; border-left: 4px solid $border;'>";
    echo "$icon <strong>$label</strong> → hasPermission: " . ($has_perm ? 'TRUE' : 'FALSE');
    echo " | In DB: " . ($in_db ? 'YES' : 'NO');
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='" . BASE_URL . "/index.php'>← Back to Dashboard</a></p>";
?>
