<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Connection - Narayana</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .box {
            background: white;
            color: #333;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 600px;
        }
        h1 { color: #667eea; margin-bottom: 20px; }
        .success { color: #10b981; font-size: 4rem; }
        .info { background: #f0f9ff; padding: 20px; border-radius: 10px; margin: 20px 0; }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 10px;
            margin: 10px;
            font-weight: bold;
        }
        .btn:hover { transform: scale(1.05); }
        .status { display: flex; justify-content: space-around; margin: 20px 0; }
        .status-item { padding: 15px; background: #f8f9fa; border-radius: 10px; flex: 1; margin: 0 10px; }
        .status-ok { color: #10b981; }
        .status-error { color: #ef4444; }
    </style>
</head>
<body>
    <div class="box">
        <div class="success">✅</div>
        <h1>Koneksi Berhasil!</h1>
        <p><strong>Apache & PHP berjalan dengan baik</strong></p>
        
        <div class="info">
            <h3>📊 System Information</h3>
            <div class="status">
                <div class="status-item">
                    <strong>PHP Version</strong><br>
                    <span class="status-ok"><?php echo PHP_VERSION; ?></span>
                </div>
                <div class="status-item">
                    <strong>Server</strong><br>
                    <span class="status-ok"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span>
                </div>
            </div>
        </div>
        
        <div class="info">
            <h3>🔌 Database Connection Test</h3>
            <?php
            try {
                $conn = new PDO("mysql:host=localhost", "root", "");
                echo '<div class="status-ok"><strong>✅ MySQL Connection OK!</strong></div>';
                echo '<p>Database server berhasil terkoneksi</p>';
            } catch (PDOException $e) {
                echo '<div class="status-error"><strong>❌ MySQL Connection Failed</strong></div>';
                echo '<p>Error: ' . $e->getMessage() . '</p>';
                echo '<p><strong>Solusi:</strong> Start MySQL di XAMPP Control Panel</p>';
            }
            ?>
        </div>
        
        <div style="margin-top: 30px;">
            <a href="installer.php" class="btn">🚀 Install Database</a>
            <a href="login.php" class="btn">🔐 Login Page</a>
        </div>
        
        <div style="margin-top: 20px; font-size: 0.9rem; color: #666;">
            <p>📁 Path: <?php echo __DIR__; ?></p>
            <p>🌐 URL: <?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?></p>
        </div>
    </div>
</body>
</html>
