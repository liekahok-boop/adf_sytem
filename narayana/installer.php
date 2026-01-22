<?php
/**
 * NARAYANA HOTEL MANAGEMENT SYSTEM
 * Database Installer - Automatic Installation
 * 
 * CARA PAKAI:
 * 1. Buka: http://localhost/narayana/installer.php
 * 2. Klik tombol "Install Database Now!"
 * 3. SELESAI!
 * 4. Hapus file ini setelah install berhasil
 */

// Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'narayana_hotel');
define('DB_CHARSET', 'utf8mb4');

// Check if already installed
$alreadyInstalled = false;
try {
    $checkConn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $alreadyInstalled = true;
} catch (PDOException $e) {
    // Database not exists, proceed with installation
}

// Handle installation
$installStatus = '';
$installError = '';

if (isset($_POST['install'])) {
    try {
        // Step 1: Connect to MySQL (without database)
        $conn = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Step 2: Create Database
        $conn->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Step 3: Use Database
        $conn->exec("USE " . DB_NAME);
        
        // Step 4: Read and execute SQL file
        $sqlFile = __DIR__ . '/database.sql';
        
        if (!file_exists($sqlFile)) {
            throw new Exception("File database.sql tidak ditemukan!");
        }
        
        $sql = file_get_contents($sqlFile);
        
        // Remove comments and split into individual queries
        $sql = preg_replace('/--.*$/m', '', $sql); // Remove single-line comments
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Remove multi-line comments
        
        // Split by semicolon and execute each query
        $queries = array_filter(array_map('trim', explode(';', $sql)));
        
        $successCount = 0;
        foreach ($queries as $query) {
            if (!empty($query)) {
                $conn->exec($query);
                $successCount++;
            }
        }
        
        $installStatus = "success";
        $installMessage = "✅ Database berhasil terinstall!<br>
                          📊 Total {$successCount} queries berhasil dijalankan<br>
                          🗂️ Database: <strong>" . DB_NAME . "</strong><br><br>
                          <strong>Login Credentials:</strong><br>
                          👤 Username: <code>admin</code><br>
                          🔑 Password: <code>password</code><br><br>
                          🚀 <a href='index.php' style='color: #10b981; text-decoration: underline;'>Klik di sini untuk login</a><br><br>
                          ⚠️ <strong>PENTING:</strong> Hapus file <code>installer.php</code> sekarang untuk keamanan!";
        
    } catch (PDOException $e) {
        $installStatus = "error";
        $installError = "Database Error: " . $e->getMessage();
    } catch (Exception $e) {
        $installStatus = "error";
        $installError = $e->getMessage();
    }
}

// Handle force reinstall
if (isset($_POST['reinstall'])) {
    try {
        $conn = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Drop existing database
        $conn->exec("DROP DATABASE IF EXISTS " . DB_NAME);
        
        // Redirect to install
        header("Location: installer.php?action=install");
        exit;
        
    } catch (PDOException $e) {
        $installStatus = "error";
        $installError = "Error: " . $e->getMessage();
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'install') {
    $_POST['install'] = true;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Installer - Narayana Hotel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --bg-dark: #0f172a;
            --bg-light: #1e293b;
            --text: #f8fafc;
            --text-muted: #94a3b8;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--bg-dark), var(--bg-light));
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .installer-box {
            background: var(--bg-light);
            border-radius: 1rem;
            padding: 3rem;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo h1 {
            font-size: 2.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }
        
        .logo p {
            color: var(--text-muted);
            font-size: 1rem;
        }
        
        .status-box {
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            border: 2px solid;
        }
        
        .status-box.success {
            background: rgba(16, 185, 129, 0.1);
            border-color: var(--success);
            color: var(--success);
        }
        
        .status-box.error {
            background: rgba(239, 68, 68, 0.1);
            border-color: var(--danger);
            color: var(--danger);
        }
        
        .status-box.warning {
            background: rgba(245, 158, 11, 0.1);
            border-color: var(--warning);
            color: var(--warning);
        }
        
        .status-box.info {
            background: rgba(99, 102, 241, 0.1);
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .btn {
            display: inline-block;
            width: 100%;
            padding: 1rem 2rem;
            border: none;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 1.125rem;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #4f46e5);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
        }
        
        .info-list {
            background: rgba(99, 102, 241, 0.05);
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
        }
        
        .info-list h3 {
            color: var(--text);
            margin-bottom: 1rem;
            font-size: 1.125rem;
        }
        
        .info-list ul {
            list-style: none;
            padding: 0;
        }
        
        .info-list li {
            color: var(--text-muted);
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .info-list li:last-child {
            border-bottom: none;
        }
        
        .info-list li strong {
            color: var(--text);
        }
        
        form {
            margin-bottom: 1rem;
        }
        
        code {
            background: rgba(0, 0, 0, 0.3);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-family: 'Courier New', monospace;
        }
        
        .warning-text {
            color: var(--warning);
            font-size: 0.875rem;
            margin-top: 1rem;
            text-align: center;
        }
        
        .divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            margin: 2rem 0;
        }
    </style>
</head>
<body>
    <div class="installer-box">
        <div class="logo">
            <h1>🏨 Narayana</h1>
            <p>Hotel Management System</p>
        </div>
        
        <?php if ($installStatus === 'success'): ?>
            <!-- Installation Success -->
            <div class="status-box success">
                <h2 style="margin-bottom: 1rem;">🎉 Installation Complete!</h2>
                <p style="line-height: 1.8;"><?php echo $installMessage; ?></p>
            </div>
            
            <a href="index.php" class="btn btn-success">
                🚀 Buka Aplikasi Sekarang
            </a>
            
        <?php elseif ($installStatus === 'error'): ?>
            <!-- Installation Error -->
            <div class="status-box error">
                <h2 style="margin-bottom: 1rem;">❌ Installation Failed</h2>
                <p><?php echo $installError; ?></p>
            </div>
            
            <form method="POST">
                <button type="submit" name="install" class="btn btn-primary">
                    🔄 Coba Install Lagi
                </button>
            </form>
            
        <?php elseif ($alreadyInstalled): ?>
            <!-- Already Installed -->
            <div class="status-box warning">
                <h2 style="margin-bottom: 1rem;">⚠️ Database Sudah Ada!</h2>
                <p>Database <strong><?php echo DB_NAME; ?></strong> sudah terinstall sebelumnya.</p>
            </div>
            
            <a href="index.php" class="btn btn-success" style="margin-bottom: 1rem;">
                🚀 Buka Aplikasi
            </a>
            
            <div class="divider"></div>
            
            <p style="text-align: center; color: var(--text-muted); margin-bottom: 1rem;">
                Atau reinstall database (data lama akan dihapus):
            </p>
            
            <form method="POST" onsubmit="return confirm('⚠️ PERHATIAN!\n\nSemua data yang ada akan DIHAPUS!\n\nLanjutkan reinstall?')">
                <button type="submit" name="reinstall" class="btn btn-danger">
                    🗑️ Reinstall Database
                </button>
            </form>
            
        <?php else: ?>
            <!-- Ready to Install -->
            <div class="status-box info">
                <h2 style="margin-bottom: 1rem;">📦 Ready to Install</h2>
                <p>Database installer siap dijalankan. Klik tombol di bawah untuk memulai instalasi otomatis.</p>
            </div>
            
            <div class="info-list">
                <h3>🔧 Configuration:</h3>
                <ul>
                    <li><strong>Host:</strong> <?php echo DB_HOST; ?></li>
                    <li><strong>Database:</strong> <?php echo DB_NAME; ?></li>
                    <li><strong>User:</strong> <?php echo DB_USER; ?></li>
                    <li><strong>Charset:</strong> <?php echo DB_CHARSET; ?></li>
                </ul>
            </div>
            
            <div class="info-list">
                <h3>✨ Yang Akan Dibuat:</h3>
                <ul>
                    <li>✅ Database & Tables (5 tables)</li>
                    <li>✅ Sample Data (Users, Divisions, Categories)</li>
                    <li>✅ Views untuk Reporting</li>
                    <li>✅ Default Admin Account</li>
                </ul>
            </div>
            
            <form method="POST">
                <button type="submit" name="install" class="btn btn-primary">
                    🚀 Install Database Now!
                </button>
            </form>
            
            <p class="warning-text">
                ⚠️ Pastikan XAMPP MySQL sudah running
            </p>
        <?php endif; ?>
    </div>
</body>
</html>
