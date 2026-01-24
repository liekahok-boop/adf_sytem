<?php
session_start();
define('APP_ACCESS', true);
require_once 'config/config.php';
require_once 'includes/auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo "❌ Not logged in";
    exit;
}

echo "<h1>Debug Theme Settings</h1>";
echo "<pre>";
echo "SESSION user_theme: " . ($_SESSION['user_theme'] ?? 'NOT SET') . "\n";
echo "SESSION user_language: " . ($_SESSION['user_language'] ?? 'NOT SET') . "\n";
echo "Current HTML body tag will have: data-theme=\"" . ($_SESSION['user_theme'] ?? 'dark') . "\"\n";
echo "</pre>";

// Check database
require_once 'config/database.php';
$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();

$prefs = $db->fetchOne("SELECT * FROM user_preferences WHERE user_id = ?", [$currentUser['id']]);
echo "<h2>Database user_preferences:</h2>";
echo "<pre>";
echo print_r($prefs, true);
echo "</pre>";

echo "<h2>Go to: <a href='modules/settings/preferences.php'>Preferences Page</a></h2>";
echo "<h2>Then return here to see if session updated</h2>";
?>
