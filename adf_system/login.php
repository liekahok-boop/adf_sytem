<?php
/**
 * ADF SYSTEM - Multi Business Management
 * Login Page
 */

define('APP_ACCESS', true);
// FORCE adf_system for login - never use business database here
define('ACTIVE_BUSINESS_ID', 'adf_system');

require_once 'config/config.php';
require_once 'config/database.php';

// Check if database exists
try {
    $testConn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    unset($testConn);
} catch (PDOException $e) {
    header('Location: setup-required.html');
    exit;
}

require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
$db = Database::getInstance();

// Get custom login background from settings (with error handling)
$customBg = null;
$bgUrl = null;
try {
    $loginBgSetting = $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = 'login_background'");
    $customBg = $loginBgSetting['setting_value'] ?? null;
    $bgUrl = $customBg && file_exists(BASE_PATH . '/uploads/backgrounds/' . $customBg) 
        ? BASE_URL . '/uploads/backgrounds/' . $customBg 
        : null;
} catch (Exception $e) {
    // Settings table might not exist yet, continue without background
}

// If already logged in, redirect to dashboard
if ($auth->isLoggedIn()) {
    redirect(BASE_URL . '/index.php');
}

// Handle login form submission
if (isPost()) {
    $username = sanitize(getPost('username'));
    $password = getPost('password');
    $selectedBusiness = sanitize(getPost('business', 'narayana-hotel'));
    
    if ($auth->login($username, $password)) {
        $currentUser = $auth->getCurrentUser();
        
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

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
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
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            position: relative;
        }
        
        .login-box {
            background: #1e293b;
            border-radius: 12px;
            padding: 3rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            border: 1px solid #334155;
            width: 100%;
            max-width: 450px;
            position: relative;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
            position: relative;
        }
        
        .business-logo-icon {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .login-logo {
            font-size: 1.75rem;
            font-weight: 900;
            color: #ffffff;
            margin-bottom: 0.25rem;
            letter-spacing: -0.5px;
        }
        
        .login-subtitle {
            color: #cbd5e1;
            font-size: 0.875rem;
            font-weight: 500;
            margin-top: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            color: #e2e8f0;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            background: #0f172a;
            border: 1px solid #475569;
            border-radius: 8px;
            color: #e2e8f0;
            font-size: 1rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            color: #fca5a5;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .btn-primary {
            width: 100%;
            padding: 1rem;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.125rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
        }
        
        .btn-primary:hover {
            background: #4f46e5;
        }
        
        .database-status {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.6));
            border: 1px solid #334155;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .status-indicator {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 10px rgba(16, 185, 129, 1), inset 0 0 3px rgba(255, 255, 255, 0.3);
            animation: blink 1.2s ease-in-out infinite;
            flex-shrink: 0;
        }
        
        @keyframes blink {
            0% {
                background: #10b981;
                box-shadow: 0 0 10px rgba(16, 185, 129, 1), inset 0 0 3px rgba(255, 255, 255, 0.3);
            }
            50% {
                background: #059669;
                box-shadow: 0 0 15px rgba(16, 185, 129, 0.8), inset 0 0 5px rgba(255, 255, 255, 0.2);
            }
            100% {
                background: #10b981;
                box-shadow: 0 0 10px rgba(16, 185, 129, 1), inset 0 0 3px rgba(255, 255, 255, 0.3);
            }
        }
        
        .db-info {
            flex: 1;
        }
        
        .db-label {
            font-size: 0.75rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }
        
        .db-name {
            font-size: 0.938rem;
            color: #e2e8f0;
            font-weight: 600;
            font-family: 'Courier New', monospace;
        }
        
        .demo-credentials {
            background: #334155;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 2rem;
            font-size: 0.875rem;
            color: #cbd5e1;
        }
        
        .demo-credentials strong {
            color: #6366f1;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #334155;
            color: #64748b;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <span class="business-logo-icon">🏢</span>
                <h1 class="login-logo">Narayana Hotel</h1>
                <p class="login-subtitle">Karimunjawa</p>
                <p class="login-subtitle">Hotel System</p>
            </div>
            
            <div class="database-status">
                <div class="status-indicator"></div>
                <div class="db-info">
                    <div class="db-label">DATABASE</div>
                    <div class="db-name">adf_narayana_hotel</div>
                </div>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Pilih Bisnis</label>
                    <select name="business" class="form-control" required id="businessSelect" onchange="updateDatabaseInfo(this.value)">
                        <?php foreach ($availableBusinesses as $bizId => $bizConfig): ?>
                            <option value="<?php echo htmlspecialchars($bizId); ?>" data-dbname="<?php echo htmlspecialchars('adf_' . $bizId); ?>">
                                <?php echo htmlspecialchars($bizConfig['theme']['icon'] . ' ' . $bizConfig['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>
                
                <button type="submit" class="btn-primary">Login</button>
            </form>
            
            <div class="demo-credentials">
                <div style="text-align: center; margin-bottom: 0.5rem;"><strong>Demo Credentials:</strong></div>
                <div>👤 Username: <strong>admin</strong></div>
                <div>🔑 Password: <strong>admin</strong></div>
            </div>
            
            <div class="login-footer">
                &copy; <?php echo APP_YEAR; ?> <?php echo APP_NAME; ?>
            </div>
        </div>
    </div>
    
    <script>
        function updateDatabaseInfo(businessId) {
            const select = document.getElementById('businessSelect');
            const selectedOption = select.options[select.selectedIndex];
            const dbName = selectedOption.getAttribute('data-dbname');
            const businessText = selectedOption.textContent.trim();
            
            // Extract business name (remove emoji and trim)
            const cleanName = businessText.replace(/^[^\w]+/, '').trim();
            
            // Update the database name display
            const dbNameEl = document.querySelector('.db-name');
            if (dbNameEl) {
                dbNameEl.textContent = dbName;
            }
            
            // Update the business name at header
            const logoEl = document.querySelector('.login-logo');
            if (logoEl) {
                logoEl.textContent = cleanName;
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateDatabaseInfo();
        });
    </script>
</body>
</html>
