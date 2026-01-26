<?php
define('APP_ACCESS', true);
require_once 'config/config.php';

// Start session with correct name
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

require_once 'config/database.php';
require_once 'includes/auth.php';

$auth = new Auth();

echo "<h2>Debug Permission System</h2>";
echo "<pre>";

// Check login status
echo "=== LOGIN STATUS ===\n";
echo "Is Logged In: " . ($auth->isLoggedIn() ? "YES" : "NO") . "\n";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NULL') . "\n";
echo "Username: " . ($_SESSION['username'] ?? 'NULL') . "\n";
echo "Role: " . ($_SESSION['role'] ?? 'NULL') . "\n\n";

// Check database connection
echo "=== DATABASE ===\n";
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    echo "Database Connected: YES\n";
    echo "Database Name: " . DB_NAME . "\n\n";
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage() . "\n\n";
}

// Check permissions in database
if (isset($_SESSION['user_id'])) {
    echo "=== PERMISSIONS IN DATABASE ===\n";
    try {
        $query = "SELECT permission FROM user_permissions WHERE user_id = ? ORDER BY permission";
        $stmt = $conn->prepare($query);
        $stmt->execute([$_SESSION['user_id']]);
        $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($permissions)) {
            echo "NO PERMISSIONS FOUND!\n";
        } else {
            foreach ($permissions as $perm) {
                echo "- " . $perm . "\n";
            }
        }
        echo "\nTotal: " . count($permissions) . " permissions\n\n";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n\n";
    }
    
    // Test specific permissions
    echo "=== PERMISSION CHECK ===\n";
    $testPermissions = ['dashboard', 'investor', 'project', 'settings'];
    foreach ($testPermissions as $perm) {
        $hasIt = $auth->hasPermission($perm);
        echo "hasPermission('$perm'): " . ($hasIt ? "✓ YES" : "✗ NO") . "\n";
    }
}

echo "</pre>";

echo "<hr>";
echo "<a href='home.php'>← Back to Home</a>";
