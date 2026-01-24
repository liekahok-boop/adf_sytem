<?php
/**
 * RESET SEMUA - Password, User, Akses
 * Jalankan file ini untuk reset sistem ke kondisi awal yang jelas
 */
define('APP_ACCESS', true);
require_once 'config/config.php';

header('Content-Type: text/html; charset=UTF-8');

// Cek apakah form disubmit
$reset_done = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_reset'])) {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // 1. Hapus semua user kecuali admin
        $pdo->exec("DELETE FROM users WHERE username != 'admin'");
        
        // 2. Reset password admin
        $pdo->exec("UPDATE users SET password = MD5('admin123'), business_access = '[1,2]' WHERE username = 'admin'");
        
        // 3. Tambah user baru dengan password yang jelas
        $users = [
            ['staff1', 'Staff Satu', 'staff', 'staff123', '[1,2]'],
            ['manager1', 'Manager Satu', 'manager', 'manager123', '[1,2]'],
            ['owner1', 'Owner Satu', 'owner', 'owner123', '[1,2]'],
        ];
        
        foreach ($users as $user) {
            $stmt = $pdo->prepare("INSERT INTO users (username, full_name, role, password, business_access) 
                                   VALUES (?, ?, ?, MD5(?), ?)");
            $stmt->execute($user);
        }
        
        // 4. Pastikan tabel businesses ada
        $pdo->exec("CREATE TABLE IF NOT EXISTS businesses (
            id INT PRIMARY KEY AUTO_INCREMENT,
            business_name VARCHAR(100) NOT NULL,
            address TEXT,
            phone VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // 5. Tambah data bisnis
        $pdo->exec("INSERT INTO businesses (id, business_name, address, phone) 
                    VALUES (1, 'Narayana Hotel', 'Jl. Utama No. 1', '081234567890'),
                           (2, 'Bens Cafe', 'Jl. Cafe No. 2', '081234567891')
                    ON DUPLICATE KEY UPDATE business_name=VALUES(business_name)");
        
        $reset_done = true;
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Semua</title>
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
        .container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            max-width: 800px;
            width: 100%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 {
            color: #667eea;
            margin-bottom: 10px;
        }
        .warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .warning h2 {
            color: #856404;
            margin-bottom: 10px;
        }
        .success {
            background: #d4edda;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .success h2 {
            color: #155724;
            margin-bottom: 10px;
        }
        .error {
            background: #f8d7da;
            border: 2px solid #dc3545;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background: #667eea;
            color: white;
        }
        table tr:hover {
            background: #f5f5f5;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #218838;
        }
        .btn-secondary {
            background: #6c757d;
            margin-left: 10px;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        ul {
            margin: 15px 0;
            padding-left: 25px;
        }
        ul li {
            margin: 8px 0;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($reset_done): ?>
            <div class="success">
                <h2>✅ Reset Berhasil!</h2>
                <p>Sistem sudah direset ke kondisi awal yang bersih.</p>
            </div>
            
            <h2>👥 User yang Tersedia:</h2>
            <table>
                <tr>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Role</th>
                    <th>Akses Bisnis</th>
                </tr>
                <tr>
                    <td><strong>admin</strong></td>
                    <td><strong>admin123</strong></td>
                    <td>admin</td>
                    <td>Semua (1,2)</td>
                </tr>
                <tr>
                    <td><strong>staff1</strong></td>
                    <td><strong>staff123</strong></td>
                    <td>staff</td>
                    <td>Semua (1,2)</td>
                </tr>
                <tr>
                    <td><strong>manager1</strong></td>
                    <td><strong>manager123</strong></td>
                    <td>manager</td>
                    <td>Semua (1,2)</td>
                </tr>
                <tr>
                    <td><strong>owner1</strong></td>
                    <td><strong>owner123</strong></td>
                    <td>owner</td>
                    <td>Semua (1,2)</td>
                </tr>
            </table>
            
            <h2>🏢 Bisnis yang Terdaftar:</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nama Bisnis</th>
                    <th>Alamat</th>
                </tr>
                <tr>
                    <td>1</td>
                    <td>Narayana Hotel</td>
                    <td>Jl. Utama No. 1</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Bens Cafe</td>
                    <td>Jl. Cafe No. 2</td>
                </tr>
            </table>
            
            <h2>🎯 Langkah Selanjutnya:</h2>
            <ul>
                <li><strong>Untuk kerja sehari-hari:</strong> <a href="login.php">login.php</a> → Login pakai <strong>admin/admin123</strong></li>
                <li><strong>Untuk owner lihat laporan:</strong> <a href="owner-login.php">owner-login.php</a> → Login pakai <strong>owner1/owner123</strong></li>
                <li><strong>Untuk test sistem:</strong> <a href="tools/test-system.php">tools/test-system.php</a></li>
            </ul>
            
            <div style="margin-top: 30px;">
                <a href="home.php" class="btn btn-success">Ke Homepage</a>
                <a href="login.php" class="btn btn-success">Login Sekarang</a>
                <a href="tools/test-system.php" class="btn btn-secondary">Test Sistem</a>
            </div>
            
        <?php elseif (isset($error_message)): ?>
            <div class="error">
                <h2>❌ Error</h2>
                <p><?php echo htmlspecialchars($error_message); ?></p>
            </div>
            <a href="" class="btn btn-secondary">Coba Lagi</a>
            
        <?php else: ?>
            <h1>🔄 Reset Semua</h1>
            <p>Reset password, user, dan data ke kondisi awal yang jelas.</p>
            
            <div class="warning">
                <h2>⚠️ Peringatan!</h2>
                <p>Action ini akan:</p>
                <ul>
                    <li>Menghapus semua user kecuali <strong>admin</strong></li>
                    <li>Reset password <strong>admin</strong> menjadi <strong>admin123</strong></li>
                    <li>Membuat user baru: staff1, manager1, owner1</li>
                    <li>Semua user akan punya akses ke bisnis 1 dan 2</li>
                    <li>Membuat tabel businesses jika belum ada</li>
                    <li>Menambah data: Narayana Hotel & Bens Cafe</li>
                </ul>
            </div>
            
            <h2>User yang akan dibuat:</h2>
            <table>
                <tr>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Role</th>
                </tr>
                <tr><td>admin</td><td>admin123</td><td>admin</td></tr>
                <tr><td>staff1</td><td>staff123</td><td>staff</td></tr>
                <tr><td>manager1</td><td>manager123</td><td>manager</td></tr>
                <tr><td>owner1</td><td>owner123</td><td>owner</td></tr>
            </table>
            
            <form method="POST" onsubmit="return confirm('Yakin ingin reset semua? Data user lama akan hilang!');">
                <input type="hidden" name="confirm_reset" value="1">
                <button type="submit" class="btn">Reset Sekarang</button>
                <a href="home.php" class="btn btn-secondary">Batal</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
