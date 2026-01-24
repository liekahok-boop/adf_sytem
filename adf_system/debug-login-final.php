<?php
session_start();

// Clear any existing session first
$_SESSION = array();

echo "<pre>\n";
echo "=== LOGIN FLOW DEBUG ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

// Simulate login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "1. POST Data Received:\n";
    echo "   Username: " . ($_POST['username'] ?? 'NOT SET') . "\n";
    echo "   Password: " . (isset($_POST['password']) ? '***' : 'NOT SET') . "\n";
    echo "   Business: " . ($_POST['business'] ?? 'NOT SET') . "\n\n";
    
    // Include auth
    require_once 'config/config.php';
    require_once 'config/database.php';
    require_once 'includes/auth.php';
    
    echo "2. Auth Class Loaded\n";
    echo "   DB_NAME: " . DB_NAME . "\n";
    echo "   DB_HOST: " . DB_HOST . "\n\n";
    
    // Try login
    $auth = new Auth();
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "3. Attempting Login:\n";
    echo "   Username: $username\n";
    echo "   Password Hash: " . md5($password) . "\n\n";
    
    $result = $auth->login($username, $password, $_POST['business'] ?? 'narayana-hotel');
    
    echo "4. Login Result: " . ($result ? "SUCCESS" : "FAILED") . "\n\n";
    
    if ($result) {
        echo "5. Session Data After Login:\n";
        foreach ($_SESSION as $key => $value) {
            $val = is_array($value) ? json_encode($value) : $value;
            echo "   SESSION[" . $key . "] = " . $val . "\n";
        }
        echo "\n6. Next Action: Should redirect to home.php\n";
        echo "   Redirect URL: home.php\n";
        echo "   But we'll show this debug page instead.\n";
    } else {
        echo "5. Login Failed - Session NOT SET\n";
        echo "   Check database connection and user credentials\n";
    }
} else {
    echo "No POST data. Visit this page with:\n";
    echo "curl -X POST 'http://localhost:8080/adf_system/debug-login-final.php' -d 'username=admin&password=admin&business=narayana-hotel'\n";
}

echo "\n</pre>\n";
?>
