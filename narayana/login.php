<?php
/**
 * NARAYANA HOTEL MANAGEMENT SYSTEM
 * Login Page
 */

define('APP_ACCESS', true);
require_once 'config/config.php';

// Check if database exists, redirect to installer if not
try {
    $testConn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    unset($testConn);
} catch (PDOException $e) {
    // Database not exists, redirect to setup page
    header('Location: setup-required.html');
    exit;
}

require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'config/database.php';

$auth = new Auth();
$db = Database::getInstance();

// Get custom login background from settings
$loginBgSetting = $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = 'login_background'");
$customBg = $loginBgSetting['setting_value'] ?? null;
$bgUrl = $customBg && file_exists(BASE_PATH . '/uploads/backgrounds/' . $customBg) 
    ? BASE_URL . '/uploads/backgrounds/' . $customBg 
    : null;

// If already logged in, redirect to dashboard
if ($auth->isLoggedIn()) {
    redirect(BASE_URL . '/index.php');
}

// Handle login form submission
if (isPost()) {
    $username = sanitize(getPost('username'));
    $password = getPost('password');
    $selectedBusiness = sanitize(getPost('business', 'bens-cafe')); // Default to bens-cafe
    
    if ($auth->login($username, $password)) {
        $currentUser = $auth->getCurrentUser();
        
        // ALL roles can login here (staff, manager, accountant, admin, owner)
        // Admin and owner get full access to all features
        require_once 'includes/business_helper.php';
        setActiveBusinessId($selectedBusiness);
        setFlash('success', 'Login berhasil! Selamat datang ke ' . getBusinessDisplayName($selectedBusiness));
        redirect(BASE_URL . '/index.php');
    } else {
        $error = 'Username atau password salah!';
    }
}

// Get available businesses for dropdown
require_once 'includes/business_helper.php';
$availableBusinesses = getAvailableBusinesses();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            <?php if ($bgUrl): ?>
            background: linear-gradient(rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.85)), url('<?php echo $bgUrl; ?>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            <?php else: ?>
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            <?php endif; ?>
            position: relative;
            overflow: hidden;
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 70%);
            top: -250px;
            right: -250px;
            animation: pulse 4s ease-in-out infinite;
        }
        
        .login-container::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.1) 0%, transparent 70%);
            bottom: -200px;
            left: -200px;
            animation: pulse 4s ease-in-out infinite 2s;
        }
        
        .login-box {
            background: var(--bg-secondary);
            border-radius: var(--radius-xl);
            padding: 3rem;
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--bg-tertiary);
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 1;
            animation: fadeIn 0.5s ease-out;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .login-logo {
            font-size: 2.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            color: var(--text-muted);
            font-size: 1rem;
        }
        
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--danger);
            color: var(--danger);
            padding: 1rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 500;
        }
        
        .btn-login {
            width: 100%;
            padding: 1rem;
            font-size: 1.125rem;
            margin-top: 1rem;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--bg-tertiary);
            color: var(--text-muted);
            font-size: 0.875rem;
        }
        
        .demo-credentials {
            background: var(--bg-tertiary);
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-top: 2rem;
            font-size: 0.875rem;
        }
        
        .demo-credentials strong {
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1 class="login-logo">Narayana</h1>
                <p class="login-subtitle">Hotel Management System</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Pilih Bisnis</label>
                    <select name="business" class="form-control" required style="padding: 0.75rem;">
                        <?php foreach ($availableBusinesses as $bizId => $bizConfig): ?>
                            <option value="<?php echo htmlspecialchars($bizId); ?>">
                                <?php echo htmlspecialchars($bizConfig['theme']['icon'] . ' ' . $bizConfig['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">
                        Pilih bisnis yang ingin Anda kelola
                    </small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div style="position: relative;">
                        <input type="password" name="password" id="loginPassword" class="form-control" 
                               placeholder="Masukkan password" 
                               style="padding-right: 2.5rem;" required>
                        <button type="button" onclick="toggleLoginPassword()" 
                                style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 0.25rem; color: var(--text-muted);" 
                                title="Lihat password">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login">
                    Login
                </button>
            </form>
            
            <div class="demo-credentials">
                <div style="text-align: center; margin-bottom: 0.5rem;"><strong>Demo Credentials:</strong></div>
                <div style="color: var(--text-secondary);">
                    <div>👤 Username: <strong>admin</strong></div>
                    <div>🔑 Password: <strong>password</strong></div>
                </div>
            </div>
            
            <div class="login-footer">
                &copy; <?php echo APP_YEAR; ?> <?php echo APP_NAME; ?>
            </div>
        </div>
    </div>
    
    <script>
        function toggleLoginPassword() {
            const input = document.getElementById('loginPassword');
            const icon = document.getElementById('eyeIcon');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        }
    </script>
</body>
</html>
