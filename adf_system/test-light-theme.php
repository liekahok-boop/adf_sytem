<?php
/**
 * Test Light Theme CSS
 */
define('APP_ACCESS', true);
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <title>Test Light Theme</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body data-theme="light">
    <div style="padding: 40px; max-width: 800px; margin: 0 auto;">
        <h1>✅ Light Theme CSS Test</h1>
        
        <div style="background: #ffffff; border: 2px solid #6366f1; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h2>Text Appearance Test</h2>
            <p>Ini adalah paragraf normal yang seharusnya <strong>warna gelap</strong> pada background terang.</p>
            
            <div style="background: #f8fafc; padding: 15px; border-radius: 5px; margin: 15px 0;">
                <h3>Subtitle - Harus Gelap</h3>
                <p>Teks di dalam box ini harus terlihat jelas dengan warna gelap.</p>
            </div>
            
            <label style="display: block; margin: 15px 0;">
                <strong>Form Label (Harus Gelap):</strong>
                <input type="text" placeholder="Input test" style="width: 100%; margin-top: 5px; padding: 8px;">
            </label>
            
            <a href="#">Link Test (Harus Ungu)</a>
        </div>
        
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3>CSS Computed Styles:</h3>
            <p>Jika Anda melihat teks yang jelas dan gelap di sini, CSS light theme berfungsi dengan baik!</p>
            
            <p style="color: var(--text-primary);">Warna text-primary: <strong><?php echo 'var(--text-primary)'; ?></strong></p>
            <p style="color: var(--text-secondary);">Warna text-secondary: <strong><?php echo 'var(--text-secondary)'; ?></strong></p>
        </div>
        
        <hr style="margin: 30px 0; border: none; border-top: 2px solid #e2e8f0;">
        
        <p style="text-align: center; color: #6366f1; font-weight: bold;">
            Jika semua text di halaman ini terlihat <strong>GELAP</strong>, berarti CSS light theme berfungsi! ✅
        </p>
    </div>
    
    <script>
        // Log CSS variables to console
        const root = document.documentElement;
        const styles = getComputedStyle(root);
        
        console.log("=== Light Theme CSS Variables ===");
        console.log("--text-primary:", styles.getPropertyValue('--text-primary'));
        console.log("--text-secondary:", styles.getPropertyValue('--text-secondary'));
        console.log("--bg-primary:", styles.getPropertyValue('--bg-primary'));
        console.log("Current theme:", document.body.getAttribute('data-theme'));
    </script>
</body>
</html>
