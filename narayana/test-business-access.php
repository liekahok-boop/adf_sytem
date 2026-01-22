<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/business_helper.php';
require_once 'includes/business_access.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    die("Not logged in");
}

$currentUser = $auth->getCurrentUser();

echo "<h2>Current User Debug</h2>";
echo "<pre>";
echo "Username: " . $currentUser['username'] . "\n";
echo "Role: " . $currentUser['role'] . "\n";
echo "Business Access (raw): " . ($currentUser['business_access'] ?? 'NULL') . "\n";
echo "\nBusiness Access (decoded): ";
print_r(json_decode($currentUser['business_access'] ?? '[]', true));
echo "\n";

echo "\n<h3>Available Businesses:</h3>\n";
$allBusinesses = getAvailableBusinesses();
foreach ($allBusinesses as $id => $config) {
    echo "- [$id] {$config['name']}\n";
}

echo "\n<h3>User Available Businesses:</h3>\n";
$userBusinesses = getUserAvailableBusinesses();
if (empty($userBusinesses)) {
    echo "NO BUSINESSES AVAILABLE!\n";
} else {
    foreach ($userBusinesses as $id => $config) {
        echo "- [$id] {$config['name']}\n";
    }
}

echo "\n<h3>Direct Database Query:</h3>\n";
$db = Database::getInstance();
$dbUser = $db->fetchOne("SELECT username, role, business_access FROM users WHERE id = ?", [$currentUser['id']]);
echo "DB Username: " . $dbUser['username'] . "\n";
echo "DB Role: " . $dbUser['role'] . "\n";
echo "DB Business Access: " . ($dbUser['business_access'] ?? 'NULL') . "\n";
echo "</pre>";
