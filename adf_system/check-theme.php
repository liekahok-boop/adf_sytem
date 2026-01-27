<?php
/**
 * Check Theme - Verify current theme for user and business
 */
define('APP_ACCESS', true);
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

$auth = new Auth();
$auth->requireLogin();
$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Check Theme</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #0f172a; color: #fff; }
        .box { background: #1e293b; padding: 20px; margin: 10px 0; border-radius: 8px; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .info { color: #3b82f6; }
        button { padding: 10px 20px; margin: 5px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        .btn-light { background: #f8fafc; color: #0f172a; }
        .btn-dark { background: #1e293b; color: #fff; }
    </style>
</head>
<body>
<h1>🔍 Theme Checker</h1>

<div class="box">
    <h3>Current User & Business</h3>
    <div>User ID: <span class="success"><?php echo $currentUser['id']; ?></span></div>
    <div>User Name: <span class="success"><?php echo $currentUser['full_name']; ?></span></div>
    <div>Business: <span class="success"><?php echo ACTIVE_BUSINESS_ID; ?> (<?php echo BUSINESS_NAME; ?>)</span></div>
</div>

<?php
// Get theme from database
$dbTheme = $db->fetchOne(
    "SELECT theme, language, updated_at FROM user_preferences WHERE user_id = ? AND branch_id = ?",
    [$currentUser['id'], ACTIVE_BUSINESS_ID]
);
?>

<div class="box">
    <h3>Theme in Database</h3>
    <?php if ($dbTheme): ?>
        <div>Theme: <span class="success" style="font-size: 18px; font-weight: bold;"><?php echo strtoupper($dbTheme['theme']); ?></span></div>
        <div>Language: <span class="info"><?php echo $dbTheme['language']; ?></span></div>
        <div>Last Updated: <span class="info"><?php echo $dbTheme['updated_at']; ?></span></div>
    <?php else: ?>
        <div class="error">⚠️ No theme preference found for this business!</div>
    <?php endif; ?>
</div>

<div class="box">
    <h3>Quick Theme Change</h3>
    <form method="POST" style="margin: 20px 0;">
        <button type="submit" name="set_theme" value="light" class="btn-light">☀️ Set LIGHT Theme</button>
        <button type="submit" name="set_theme" value="dark" class="btn-dark">🌙 Set DARK Theme</button>
    </form>
    
    <?php
    if (isset($_POST['set_theme'])) {
        $newTheme = $_POST['set_theme'];
        
        try {
            $existing = $db->fetchOne(
                "SELECT id FROM user_preferences WHERE user_id = ? AND branch_id = ?",
                [$currentUser['id'], ACTIVE_BUSINESS_ID]
            );
            
            if ($existing) {
                $db->update('user_preferences', [
                    'theme' => $newTheme,
                    'updated_at' => date('Y-m-d H:i:s')
                ], 'user_id = :user_id AND branch_id = :branch_id', [
                    'user_id' => $currentUser['id'],
                    'branch_id' => ACTIVE_BUSINESS_ID
                ]);
            } else {
                $db->insert('user_preferences', [
                    'user_id' => $currentUser['id'],
                    'branch_id' => ACTIVE_BUSINESS_ID,
                    'theme' => $newTheme,
                    'language' => 'id'
                ]);
            }
            
            echo '<div style="color: #10b981; margin-top: 10px;">✅ Theme changed to: <strong>' . strtoupper($newTheme) . '</strong></div>';
            echo '<div style="color: #3b82f6; margin-top: 5px;">Refresh page to see changes...</div>';
            echo '<script>setTimeout(function(){ location.reload(); }, 1500);</script>';
            
        } catch (Exception $e) {
            echo '<div style="color: #ef4444;">❌ Error: ' . $e->getMessage() . '</div>';
        }
    }
    ?>
</div>

<div class="box">
    <h3>All Preferences for This User</h3>
    <?php
    $allPrefs = $db->fetchAll(
        "SELECT branch_id, theme, language FROM user_preferences WHERE user_id = ?",
        [$currentUser['id']]
    );
    
    if (count($allPrefs) > 0) {
        echo '<table style="width: 100%; border-collapse: collapse;">';
        echo '<tr style="border-bottom: 1px solid #475569;"><th style="text-align: left; padding: 8px;">Business</th><th style="text-align: left; padding: 8px;">Theme</th><th style="text-align: left; padding: 8px;">Language</th></tr>';
        foreach ($allPrefs as $p) {
            $isCurrent = $p['branch_id'] === ACTIVE_BUSINESS_ID;
            echo '<tr style="' . ($isCurrent ? 'background: #334155;' : '') . '">';
            echo '<td style="padding: 8px;">' . $p['branch_id'] . ($isCurrent ? ' <span class="success">(current)</span>' : '') . '</td>';
            echo '<td style="padding: 8px;"><strong>' . strtoupper($p['theme']) . '</strong></td>';
            echo '<td style="padding: 8px;">' . $p['language'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<div class="error">No preferences found!</div>';
    }
    ?>
</div>

<div style="margin-top: 20px;">
    <a href="index.php" style="padding: 10px 20px; background: #4f46e5; color: white; text-decoration: none; border-radius: 4px;">🏠 Back to Dashboard</a>
    <a href="modules/settings/display.php" style="padding: 10px 20px; background: #059669; color: white; text-decoration: none; border-radius: 4px; margin-left: 10px;">⚙️ Display Settings</a>
</div>

</body>
</html>
