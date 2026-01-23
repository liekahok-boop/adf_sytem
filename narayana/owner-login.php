<?php
/**
 * Owner Login Page - Simple Login for Business Owners
 */
define('APP_ACCESS', true);
require_once 'config/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();

// If already logged in as owner, redirect to dashboard
if ($auth->isLoggedIn()) {
    $currentUser = $auth->getCurrentUser();
    if ($currentUser['role'] === 'owner' || $currentUser['role'] === 'admin') {
        redirect(BASE_URL . '/owner-dashboard.php');
    }
}

$error = '';
$success = '';

// Handle login form submission
if (isPost()) {
    $username = sanitize(getPost('username'));
    $password = getPost('password');
    
    if ($auth->login($username, $password)) {
        $currentUser = $auth->getCurrentUser();
        
        // Check if user is owner or admin
        if ($currentUser['role'] === 'owner' || $currentUser['role'] === 'admin') {
            redirect(BASE_URL . '/owner-dashboard.php');
        } else {
            $auth->logout();
            $error = 'Halaman ini khusus untuk Owner. Silakan login sebagai Owner.';
        }
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Login - Narayana Multi-Business</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 100%;
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-section h1 {
            color: #667eea;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .logo-section p {
            color: #666;
            font-size: 14px;
        }
        
        .badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
        
        .footer-links {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
        }
        
        .footer-links a {
            color: #667eea;
            text-decoration: none;
        }
        
        .footer-links a:hover {
            text-decoration: underline;
        }
        
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            font-size: 13px;
            color: #666;
            text-align: center;
        }
        
        .info-box strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-section">
            <h1>🏢 Narayana</h1>
            <p>Multi-Business Management</p>
            <span class="badge">👔 OWNER LOGIN</span>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                ❌ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                ✅ <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username Owner</label>
                <input type="text" id="username" name="username" required autofocus placeholder="Masukkan username">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Masukkan password">
            </div>
            
            <button type="submit" class="btn-login">
                🔐 Login sebagai Owner
            </button>
        </form>
        
        <div class="info-box">
            <strong>ℹ️ Halaman ini khusus untuk Owner</strong><br>
            Owner bisa melihat monitoring semua bisnis yang dikelola
        </div>
        
        <div class="footer-links">
            <a href="<?= BASE_URL ?>/login.php">← Login Staff/Manager</a>
        </div>
    </div>
</body>
</html>
