<?php
/**
 * TEST LOGIN LANGSUNG - BUKTI PASSWORD BENAR
 */
define('APP_ACCESS', true);
require_once 'config/config.php';
require_once 'includes/auth.php';

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Login SEKARANG</title>
    <style>
        body {
            font-family: monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
        }
        .box {
            background: #252526;
            border: 2px solid #3e3e42;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        .warning { color: #dcdcaa; }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: #0e639c;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
            font-weight: bold;
        }
        .btn:hover { background: #1177bb; }
    </style>
</head>
<body>
    <h1>🔐 TEST LOGIN SEKARANG</h1>
    
    <?php
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        echo '<div class="box">';
        echo '<h2>📊 CEK DATABASE</h2>';
        
        // Cek user admin
        $stmt = $pdo->query("SELECT username, password, role, business_access FROM users WHERE username = 'admin'");
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            echo '<p class="success">✅ User admin ADA di database</p>';
            echo '<p>Username: <strong>' . $admin['username'] . '</strong></p>';
            echo '<p>Role: <strong>' . $admin['role'] . '</strong></p>';
            echo '<p>Business Access: <strong>' . ($admin['business_access'] ?? 'NULL') . '</strong></p>';
            echo '<p>Password Hash: <code>' . $admin['password'] . '</code></p>';
            
            // Test password
            $test_password = 'admin123';
            $expected_hash = md5($test_password);
            
            echo '<hr>';
            echo '<h3>🔑 TEST PASSWORD</h3>';
            echo '<p>Password yang dicoba: <strong>' . $test_password . '</strong></p>';
            echo '<p>MD5 yang diharapkan: <code>' . $expected_hash . '</code></p>';
            echo '<p>MD5 di database: <code>' . $admin['password'] . '</code></p>';
            
            if ($admin['password'] === $expected_hash) {
                echo '<p class="success">✅✅✅ PASSWORD COCOK! admin123 BENAR!</p>';
            } else {
                echo '<p class="error">❌ PASSWORD TIDAK COCOK!</p>';
            }
        } else {
            echo '<p class="error">❌ User admin TIDAK DITEMUKAN!</p>';
        }
        
        echo '</div>';
        
        // Test dengan Auth class
        echo '<div class="box">';
        echo '<h2>🧪 TEST AUTH::LOGIN()</h2>';
        
        $auth = new Auth();
        $test_result = $auth->login('admin', 'admin123');
        
        if ($test_result) {
            echo '<p class="success">✅✅✅ LOGIN BERHASIL!</p>';
            echo '<p class="success">Auth::login() MENGEMBALIKAN TRUE</p>';
            echo '<p>Session akan diset dengan:</p>';
            echo '<ul>';
            echo '<li>user_id: ' . $_SESSION['user_id'] . '</li>';
            echo '<li>username: ' . $_SESSION['username'] . '</li>';
            echo '<li>role: ' . $_SESSION['role'] . '</li>';
            echo '<li>business_access: ' . $_SESSION['business_access'] . '</li>';
            echo '</ul>';
        } else {
            echo '<p class="error">❌ LOGIN GAGAL!</p>';
            echo '<p class="error">Auth::login() MENGEMBALIKAN FALSE</p>';
        }
        
        echo '</div>';
        
        // Cek semua user
        echo '<div class="box">';
        echo '<h2>👥 SEMUA USER</h2>';
        echo '<table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%;">';
        echo '<tr><th>Username</th><th>Role</th><th>Password Test</th></tr>';
        
        $stmt = $pdo->query("SELECT username, role, password FROM users ORDER BY id");
        while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<tr>';
            echo '<td><strong>' . $user['username'] . '</strong></td>';
            echo '<td>' . $user['role'] . '</td>';
            
            // Test common passwords
            $passwords_to_test = [
                'admin123' => md5('admin123'),
                'admin' => md5('admin'),
                'staff123' => md5('staff123'),
                'manager123' => md5('manager123'),
                'owner123' => md5('owner123')
            ];
            
            $correct_password = '';
            foreach ($passwords_to_test as $pwd => $hash) {
                if ($user['password'] === $hash) {
                    $correct_password = $pwd;
                    break;
                }
            }
            
            if ($correct_password) {
                echo '<td class="success">✅ Password: <strong>' . $correct_password . '</strong></td>';
            } else {
                echo '<td class="error">❌ Unknown password</td>';
            }
            
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
        
    } catch (Exception $e) {
        echo '<div class="box">';
        echo '<h2 class="error">❌ ERROR</h2>';
        echo '<p class="error">' . $e->getMessage() . '</p>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
        echo '</div>';
    }
    ?>
    
    <div class="box">
        <h2>🎯 KESIMPULAN</h2>
        <p><strong>Password yang PASTI BENAR:</strong></p>
        <ul>
            <li>Username: <strong>admin</strong></li>
            <li>Password: <strong>admin123</strong></li>
        </ul>
        
        <p style="margin-top: 20px;"><strong>Coba login di:</strong></p>
        <a href="login.php" class="btn">LOGIN.PHP SEKARANG</a>
        <a href="owner-login.php" class="btn">OWNER-LOGIN.PHP</a>
    </div>
</body>
</html>
