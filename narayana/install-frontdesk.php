<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Frontdesk Module</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .container { background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 800px; width: 100%; }
        h1 { color: #333; margin-bottom: 1rem; font-size: 1.75rem; }
        .message { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .btn { display: inline-block; padding: 0.75rem 1.5rem; background: #667eea; color: white; text-decoration: none; border-radius: 0.5rem; margin-top: 1rem; margin-right: 0.5rem; }
        .btn:hover { background: #5568d3; }
        .btn-success { background: #10b981; }
        .btn-success:hover { background: #059669; }
        ul { margin: 1rem 0; padding-left: 2rem; }
        code { background: #f4f4f4; padding: 0.25rem 0.5rem; border-radius: 0.25rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏨 Install Frontdesk Module</h1>
        
        <?php
        require_once 'config/database.php';
        
        try {
            $db = Database::getInstance();
            $conn = $db->getConnection();
            
            // Baca SQL file
            $sql = file_get_contents('database-frontdesk.sql');
            
            // Split by semicolon untuk multiple statements
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            $success_count = 0;
            $errors = [];
            
            foreach ($statements as $statement) {
                if (empty($statement) || strpos($statement, '--') === 0) continue;
                
                try {
                    $conn->exec($statement);
                    $success_count++;
                } catch (PDOException $e) {
                    // Skip if table already exists
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        $errors[] = $e->getMessage();
                    }
                }
            }
            
            if (empty($errors)) {
                echo '<div class="message success">';
                echo '<strong>✅ Instalasi Berhasil!</strong><br><br>';
                echo '<strong>Database Tables Created:</strong><ul>';
                echo '<li><code>fd_master_kamar</code> - Master data kamar dengan layout gedung</li>';
                echo '<li><code>fd_layout_gedung</code> - Konfigurasi gedung (A, B, C)</li>';
                echo '<li><code>fd_reservasi</code> - Data reservasi tamu</li>';
                echo '<li><code>fd_inhouse</code> - Tamu yang sedang menginap (Active/CheckedOut)</li>';
                echo '<li><code>fd_hk_status</code> - Status Housekeeping (Clean/Dirty)</li>';
                echo '<li><code>fd_menu_breakfast</code> - Master menu sarapan</li>';
                echo '<li><code>fd_color_config</code> - Konfigurasi warna tema denah</li>';
                echo '</ul>';
                echo '<br><strong>Sample Data Inserted:</strong><ul>';
                echo '<li>3 Gedung (A, B, C) dengan 4 kamar per gedung (12 total)</li>';
                echo '<li>10 Menu sarapan (Nasi Goreng, Mie Goreng, American Breakfast, dll)</li>';
                echo '<li>13 Warna tema denah (customizable)</li>';
                echo '</ul></div>';
                
                // Show installed modules
                echo '<div class="message info">';
                echo '<strong>📋 Modul yang Tersedia:</strong><ul>';
                echo '<li><strong>Denah Kamar</strong> - Visual layout kamar dengan status real-time (Clean/Dirty/Occupied/Due Out/Arrival/B2B)</li>';
                echo '<li><strong>Reservasi</strong> - Kelola booking tamu dengan auto-detect arrival</li>';
                echo '<li><strong>Check-In</strong> - Proses check-in dari reservasi atau walk-in</li>';
                echo '<li><strong>In-House</strong> - Daftar tamu menginap (extend stay, pindah kamar, quick checkout)</li>';
                echo '<li><strong>Breakfast Order</strong> - Input pesanan F&B per tamu dengan multi-menu dan PAX</li>';
                echo '<li><strong>Laporan</strong> - Daily report (PDF export), monthly revenue report</li>';
                echo '<li><strong>Master Data</strong> - Kelola kamar, menu, warna tema</li>';
                echo '</ul></div>';
                
                echo '<div class="message warning">';
                echo '<strong>⚠️ IMPORTANT - B2B Detection (Back-to-Back Booking):</strong><br>';
                echo 'System otomatis mendeteksi <strong>ESTAFET/B2B</strong> ketika:<br>';
                echo '• Ada tamu checkout hari ini DAN ada reservasi masuk di tanggal yang sama untuk kamar yang sama<br>';
                echo '• Ditandai dengan border <strong style="color: #D946EF;">MAGENTA</strong> dan ikon <strong style="color: #F0ABFC;">⇄</strong> di denah<br>';
                echo '• Membantu HK dan FO koordinasi turnover kamar';
                echo '</div>';
                
                echo '<a href="modules/frontdesk/" class="btn btn-success">🏨 Buka Front Desk</a> ';
                echo '<a href="index.php" class="btn">🏠 Dashboard</a>';
                
            } else {
                echo '<div class="message error">';
                echo '<strong>❌ Ada Error Saat Instalasi:</strong><br><br>';
                foreach ($errors as $err) {
                    echo '• ' . htmlspecialchars($err) . '<br>';
                }
                echo '</div>';
                echo '<a href="install-frontdesk.php" class="btn">🔄 Coba Lagi</a>';
            }
            
        } catch (Exception $e) {
            echo '<div class="message error">';
            echo '<strong>❌ Fatal Error!</strong><br>';
            echo htmlspecialchars($e->getMessage());
            echo '</div>';
            echo '<a href="install-frontdesk.php" class="btn">🔄 Coba Lagi</a>';
        }
        ?>
    </div>
</body>
</html>
