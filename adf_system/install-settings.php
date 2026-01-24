!DOCTYPE html>
html lang="id">
head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Install Settings - Narayana Hotel</title>
   <style>
       * { margin: 0; padding: 0; box-sizing: border-box; }
       body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
       .container { background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 600px; width: 100%; }
       h1 { color: #333; margin-bottom: 1rem; font-size: 1.5rem; }
       .message { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; }
       .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
       .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
       .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
       .btn { display: inline-block; padding: 0.75rem 1.5rem; background: #667eea; color: white; text-decoration: none; border-radius: 0.5rem; margin-top: 1rem; }
       .btn:hover { background: #5568d3; }
       pre { background: #f4f4f4; padding: 1rem; border-radius: 0.375rem; overflow-x: auto; font-size: 0.875rem; }
   </style>
/head>
body>
   <div class="container">
       <h1>🔧 Install Settings Module</h1>
       
       <?php
       require_once 'config/database.php';
       
       try {
           $db = Database::getInstance();
           $conn = $db->getConnection();
           
           // Read SQL file
           $sql = file_get_contents('database-settings.sql');
           
           // Execute SQL
           $conn->exec($sql);
           
           echo '<div class="message success">';
           echo '<strong>✅ Berhasil!</strong><br>';
           echo 'Settings table dan data default berhasil dibuat!<br><br>';
           echo '<strong>Yang telah diinstall:</strong><ul style="margin-top: 0.5rem;">';
           echo '<li>Table <code>settings</code> dengan 14 field konfigurasi</li>';
           echo '<li>Default company settings (Narayana Hotel)</li>';
           echo '<li>Currency settings (Rp, before)</li>';
           echo '<li>Date format (d/m/Y)</li>';
           echo '<li>Timezone (Asia/Makassar)</li>';
           echo '<li>Report PDF settings</li>';
           echo '</ul></div>';
           
           // Show installed settings
           $settings = $db->fetchAll("SELECT setting_key, setting_value, description FROM settings ORDER BY setting_key");
           echo '<div class="message info">';
           echo '<strong>📋 Settings yang Terinstall:</strong><br><br>';
           foreach ($settings as $s) {
               echo '<strong>' . $s['setting_key'] . ':</strong> ' . ($s['setting_value'] ?: '-') . '<br>';
               echo '<small style="color: #666;">' . $s['description'] . '</small><br><br>';
           }
           echo '</div>';
           
           echo '<a href="modules/settings/index.php" class="btn">Buka Pengaturan</a> ';
           echo '<a href="index.php" class="btn" style="background: #10b981;">Dashboard</a>';
           
       } catch (Exception $e) {
           echo '<div class="message error">';
           echo '<strong>❌ Error!</strong><br>';
           echo $e->getMessage();
           echo '</div>';
           
           echo '<a href="install-settings.php" class="btn">Coba Lagi</a>';
       }
       ?>
   </div>
/body>
/html>
