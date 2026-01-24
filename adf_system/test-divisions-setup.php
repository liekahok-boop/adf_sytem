<?php
define('APP_ACCESS', true);
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

echo "=== TESTING DIVISIONS PAGE ===\n\n";

try {
    $auth = new Auth();
    if (!$auth->isLoggedIn()) {
        echo "❌ Not logged in\n";
    } else {
        echo "✅ Logged in\n";
    }
    
    // Test if setFlashMessage exists
    if (function_exists('setFlashMessage')) {
        echo "✅ setFlashMessage() exists\n";
    } else {
        echo "❌ setFlashMessage() NOT found\n";
    }
    
    // Test if setFlash exists
    if (function_exists('setFlash')) {
        echo "✅ setFlash() exists\n";
    } else {
        echo "❌ setFlash() NOT found\n";
    }
    
    // Try calling setFlashMessage
    setFlashMessage('success', 'Test message');
    echo "✅ setFlashMessage() can be called\n";
    
    // Check database
    $db = Database::getInstance();
    $result = $db->fetchAll("SELECT COUNT(*) as cnt FROM divisions");
    echo "✅ Database accessible. Divisions count: " . $result[0]['cnt'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
