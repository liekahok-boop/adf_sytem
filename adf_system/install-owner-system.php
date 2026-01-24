<?php
/**
 * NARAYANA HOTEL MANAGEMENT SYSTEM
 * Owner Monitoring System Installer
 * 
 * This installer will:
 * 1. Create branches table
 * 2. Create owner_branch_access table
 * 3. Add 'owner' role to users table
 * 4. Add branch_id to related tables
 * 5. Create default branches
 */

define('APP_ACCESS', true);
require_once 'config/config.php';

// Connect to database
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Owner Monitoring System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem;
            color: #333;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        h1 {
            color: #667eea;
            margin-bottom: 0.5rem;
            font-size: 2rem;
        }
        .subtitle {
            color: #666;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }
        .step {
            background: #f8f9fa;
            padding: 1.5rem;
            margin: 1rem 0;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .step h3 {
            color: #667eea;
            margin-bottom: 0.75rem;
            font-size: 1.1rem;
        }
        .success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        .warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        .info {
            background: #d1ecf1;
            border-left-color: #17a2b8;
            color: #0c5460;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 1rem;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        code {
            background: #f1f3f5;
            padding: 0.2rem 0.4rem;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
        ul {
            margin: 0.5rem 0 0.5rem 1.5rem;
        }
        li {
            margin: 0.25rem 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏢 Owner Monitoring System</h1>
        <p class="subtitle">Installer untuk Sistem Multi-Cabang & Dashboard Owner</p>

        <?php
        $errors = [];
        $success = [];
        
        // Step 1: Create branches table
        echo '<div class="step">';
        echo '<h3>Step 1: Membuat Tabel Branches</h3>';
        try {
            $sql = "CREATE TABLE IF NOT EXISTS branches (
                id INT PRIMARY KEY AUTO_INCREMENT,
                branch_code VARCHAR(20) UNIQUE NOT NULL,
                branch_name VARCHAR(100) NOT NULL,
                address TEXT,
                city VARCHAR(100),
                phone VARCHAR(20),
                email VARCHAR(100),
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_active (is_active),
                INDEX idx_code (branch_code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            $conn->exec($sql);
            echo '<p class="success">✅ Tabel <code>branches</code> berhasil dibuat!</p>';
            $success[] = 'branches table';
        } catch(PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo '<p class="info">ℹ️ Tabel <code>branches</code> sudah ada.</p>';
            } else {
                echo '<p class="error">❌ Error: ' . $e->getMessage() . '</p>';
                $errors[] = $e->getMessage();
            }
        }
        echo '</div>';
        
        // Step 2: Create owner_branch_access table
        echo '<div class="step">';
        echo '<h3>Step 2: Membuat Tabel Owner Branch Access</h3>';
        try {
            $sql = "CREATE TABLE IF NOT EXISTS owner_branch_access (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                branch_id INT NOT NULL,
                granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                granted_by INT,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
                FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE SET NULL,
                UNIQUE KEY unique_user_branch (user_id, branch_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            $conn->exec($sql);
            echo '<p class="success">✅ Tabel <code>owner_branch_access</code> berhasil dibuat!</p>';
            $success[] = 'owner_branch_access table';
        } catch(PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo '<p class="info">ℹ️ Tabel <code>owner_branch_access</code> sudah ada.</p>';
            } else {
                echo '<p class="error">❌ Error: ' . $e->getMessage() . '</p>';
                $errors[] = $e->getMessage();
            }
        }
        echo '</div>';
        
        // Step 3: Modify users table to add 'owner' role
        echo '<div class="step">';
        echo '<h3>Step 3: Menambah Role "Owner" ke Tabel Users</h3>';
        try {
            // First, check current ENUM values
            $result = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
            $row = $result->fetch(PDO::FETCH_ASSOC);
            $enumStr = $row['Type'];
            
            if (strpos($enumStr, 'owner') === false) {
                $sql = "ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'manager', 'staff', 'owner') DEFAULT 'staff'";
                $conn->exec($sql);
                echo '<p class="success">✅ Role <code>owner</code> berhasil ditambahkan!</p>';
                $success[] = 'owner role';
            } else {
                echo '<p class="info">ℹ️ Role <code>owner</code> sudah ada di tabel users.</p>';
            }
        } catch(PDOException $e) {
            echo '<p class="error">❌ Error: ' . $e->getMessage() . '</p>';
            $errors[] = $e->getMessage();
        }
        echo '</div>';
        
        // Step 4: Add branch_id to cash_book (if column doesn't exist)
        echo '<div class="step">';
        echo '<h3>Step 4: Menambah Branch ID ke Tabel Cash Book</h3>';
        try {
            $result = $conn->query("SHOW COLUMNS FROM cash_book LIKE 'branch_id'");
            if ($result->rowCount() == 0) {
                $sql = "ALTER TABLE cash_book ADD COLUMN branch_id INT DEFAULT 1 AFTER id";
                $conn->exec($sql);
                echo '<p class="success">✅ Kolom <code>branch_id</code> ditambahkan ke <code>cash_book</code>!</p>';
                $success[] = 'cash_book branch_id';
            } else {
                echo '<p class="info">ℹ️ Kolom <code>branch_id</code> sudah ada di cash_book.</p>';
            }
        } catch(PDOException $e) {
            echo '<p class="error">❌ Error: ' . $e->getMessage() . '</p>';
            $errors[] = $e->getMessage();
        }
        echo '</div>';
        
        // Step 5: Add branch_id to frontdesk tables
        echo '<div class="step">';
        echo '<h3>Step 5: Menambah Branch ID ke Tabel Frontdesk</h3>';
        
        $frontdeskTables = ['frontdesk_rooms', 'frontdesk_reservations', 'frontdesk_room_types', 'frontdesk_buildings'];
        
        foreach ($frontdeskTables as $table) {
            try {
                // Check if table exists
                $checkTable = $conn->query("SHOW TABLES LIKE '$table'");
                if ($checkTable->rowCount() > 0) {
                    $result = $conn->query("SHOW COLUMNS FROM $table LIKE 'branch_id'");
                    if ($result->rowCount() == 0) {
                        $sql = "ALTER TABLE $table ADD COLUMN branch_id INT DEFAULT 1 AFTER id";
                        $conn->exec($sql);
                        echo '<p class="success">✅ Kolom <code>branch_id</code> ditambahkan ke <code>' . $table . '</code>!</p>';
                        $success[] = $table . ' branch_id';
                    } else {
                        echo '<p class="info">ℹ️ Kolom <code>branch_id</code> sudah ada di ' . $table . '.</p>';
                    }
                } else {
                    echo '<p class="warning">⚠️ Tabel <code>' . $table . '</code> belum ada (skip).</p>';
                }
            } catch(PDOException $e) {
                echo '<p class="error">❌ Error pada ' . $table . ': ' . $e->getMessage() . '</p>';
                $errors[] = $e->getMessage();
            }
        }
        echo '</div>';
        
        // Step 6: Insert default branches
        echo '<div class="step">';
        echo '<h3>Step 6: Membuat Data Default Cabang</h3>';
        try {
            // Check if branches already exist
            $check = $conn->query("SELECT COUNT(*) as count FROM branches");
            $count = $check->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($count == 0) {
                $sql = "INSERT INTO branches (branch_code, branch_name, address, city, phone, email, is_active) VALUES
                    ('HQ', 'Kantor Pusat', 'Alamat Kantor Pusat', 'Jakarta', '021-12345678', 'hq@narayana.com', 1),
                    ('CBG001', 'Cabang 1 - Bandung', 'Jl. Merdeka No. 1', 'Bandung', '022-12345678', 'bandung@narayana.com', 1),
                    ('CBG002', 'Cabang 2 - Surabaya', 'Jl. Pahlawan No. 2', 'Surabaya', '031-12345678', 'surabaya@narayana.com', 1)";
                
                $conn->exec($sql);
                echo '<p class="success">✅ 3 cabang default berhasil dibuat!</p>';
                echo '<ul>';
                echo '<li>HQ - Kantor Pusat (Jakarta)</li>';
                echo '<li>CBG001 - Cabang 1 Bandung</li>';
                echo '<li>CBG002 - Cabang 2 Surabaya</li>';
                echo '</ul>';
                $success[] = 'default branches';
            } else {
                echo '<p class="info">ℹ️ Sudah ada ' . $count . ' cabang di database.</p>';
            }
        } catch(PDOException $e) {
            echo '<p class="error">❌ Error: ' . $e->getMessage() . '</p>';
            $errors[] = $e->getMessage();
        }
        echo '</div>';
        
        // Summary
        echo '<div class="step">';
        echo '<h3>📊 Ringkasan Instalasi</h3>';
        
        if (empty($errors)) {
            echo '<p class="success" style="font-size: 1.1rem; font-weight: 600;">';
            echo '🎉 Instalasi berhasil sempurna!</p>';
            echo '<p>Komponen yang berhasil diinstall: ' . count($success) . '</p>';
            echo '<ul>';
            foreach ($success as $item) {
                echo '<li>✅ ' . $item . '</li>';
            }
            echo '</ul>';
            
            echo '<div class="info" style="margin-top: 1.5rem;">';
            echo '<h4>Langkah Selanjutnya:</h4>';
            echo '<ol>';
            echo '<li>Buka <strong>Settings → Kelola User</strong> untuk membuat akun owner</li>';
            echo '<li>Pilih role <code>owner</code> saat membuat user baru</li>';
            echo '<li>Buka <strong>Settings → Kelola Cabang</strong> untuk mengelola daftar cabang</li>';
            echo '<li>Owner bisa login dan mengakses dashboard di <code>/modules/owner/dashboard.php</code></li>';
            echo '<li>Dashboard owner sudah responsive untuk mobile! 📱</li>';
            echo '</ol>';
            echo '</div>';
            
            echo '<a href="index.php" class="btn">🏠 Kembali ke Dashboard</a> ';
            echo '<a href="modules/settings/users.php" class="btn">👥 Kelola User</a> ';
            echo '<a href="modules/settings/branches.php" class="btn">🏢 Kelola Cabang</a>';
            
        } else {
            echo '<p class="error" style="font-size: 1.1rem; font-weight: 600;">';
            echo '⚠️ Instalasi selesai dengan ' . count($errors) . ' error.</p>';
            echo '<p>Silakan periksa error di atas dan coba lagi.</p>';
            
            if (!empty($success)) {
                echo '<p style="margin-top: 1rem;">Komponen yang berhasil: ' . count($success) . '</p>';
            }
            
            echo '<a href="install-owner-system.php" class="btn">🔄 Coba Lagi</a>';
        }
        
        echo '</div>';
        ?>
    </div>
</body>
</html>
