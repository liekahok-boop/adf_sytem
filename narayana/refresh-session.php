<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    die("Not logged in. Please <a href='login.php'>login</a> first.");
}

$currentUser = $auth->getCurrentUser();
$db = Database::getInstance();

// Reload user data from database
$freshUser = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$currentUser['id']]);

if ($freshUser) {
    $_SESSION['user'] = $freshUser;
    echo "<h2>✅ Session Refreshed!</h2>";
    echo "<p>User data has been reloaded from database.</p>";
    echo "<pre>";
    echo "Username: " . $freshUser['username'] . "\n";
    echo "Role: " . $freshUser['role'] . "\n";
    echo "Business Access: " . ($freshUser['business_access'] ?? 'NULL') . "\n";
    echo "</pre>";
    echo "<p><a href='index.php'>Go to Dashboard</a> | <a href='modules/settings/users.php'>Manage Users</a></p>";
} else {
    echo "<h2>❌ Error</h2>";
    echo "<p>Could not find user in database.</p>";
}
