<?php
/**
 * DIRECT TEST - Langsung ke database tanpa abstraction
 */
define('APP_ACCESS', true);
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

$auth = new Auth();
$auth->requireLogin();
$currentUser = $auth->getCurrentUser();

// Get direct PDO connection
$pdo = Database::getInstance()->getConnection();

header('Content-Type: text/html; charset=utf-8');

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $theme = $_POST['theme'] ?? 'dark';
    $userId = $currentUser['id'];
    $branchId = ACTIVE_BUSINESS_ID;
    
    try {
        if ($action === 'insert') {
            $sql = "INSERT INTO user_preferences (user_id, branch_id, theme, language) 
                    VALUES (?, ?, ?, 'id') 
                    ON DUPLICATE KEY UPDATE theme = VALUES(theme), updated_at = NOW()";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId, $branchId, $theme]);
            $message = "✅ INSERTED/UPDATED theme to: <strong>$theme</strong>";
        } 
        elseif ($action === 'delete') {
            $sql = "DELETE FROM user_preferences WHERE user_id = ? AND branch_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId, $branchId]);
            $message = "🗑️ DELETED preference for this business";
        }
        elseif ($action === 'delete_all') {
            $sql = "DELETE FROM user_preferences WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            $message = "🗑️ DELETED ALL preferences";
        }
        
        // Auto refresh after 1 second
        echo "<script>setTimeout(() => location.reload(), 1000);</script>";
        
    } catch (Exception $e) {
        $message = "❌ ERROR: " . $e->getMessage();
    }
}

// Get current data
$sql = "SELECT * FROM user_preferences WHERE user_id = ? AND branch_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$currentUser['id'], ACTIVE_BUSINESS_ID]);
$currentPref = $stmt->fetch(PDO::FETCH_ASSOC);

// Get all preferences
$sql = "SELECT * FROM user_preferences WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$currentUser['id']]);
$allPrefs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Direct Database Test</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; padding: 20px; background: #f3f4f6; }
        .container { max-width: 900px; margin: 0 auto; }
        .box { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        h1 { color: #1f2937; }
        h3 { color: #374151; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-weight: 600; }
        .current { background: #dbeafe !important; }
        button { padding: 10px 20px; margin: 5px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 600; }
        .btn-light { background: #fef3c7; color: #92400e; }
        .btn-dark { background: #1e293b; color: white; }
        .btn-danger { background: #ef4444; color: white; }
        .message { padding: 15px; margin: 15px 0; border-radius: 6px; background: #d1fae5; color: #065f46; font-weight: 600; }
        .error { background: #fee2e2; color: #991b1b; }
        .info { background: #e0e7ff; color: #3730a3; padding: 15px; margin: 15px 0; border-radius: 6px; }
        .sql { background: #f3f4f6; padding: 10px; border-radius: 4px; font-family: monospace; font-size: 12px; margin: 10px 0; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 Direct Database Test</h1>
    
    <div class="info">
        <strong>Current Context:</strong><br>
        User ID: <strong><?php echo $currentUser['id']; ?></strong> (<?php echo $currentUser['full_name']; ?>)<br>
        Business: <strong><?php echo ACTIVE_BUSINESS_ID; ?></strong> (<?php echo BUSINESS_NAME; ?>)
    </div>
    
    <?php if ($message): ?>
        <div class="message <?php echo strpos($message, '❌') !== false ? 'error' : ''; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div class="box">
        <h3>📊 Current Preference for This Business</h3>
        <?php if ($currentPref): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Branch ID</th>
                    <th>Theme</th>
                    <th>Language</th>
                    <th>Updated</th>
                </tr>
                <tr class="current">
                    <td><?php echo $currentPref['id']; ?></td>
                    <td><?php echo $currentPref['user_id']; ?></td>
                    <td><?php echo $currentPref['branch_id']; ?></td>
                    <td><strong style="font-size: 18px; text-transform: uppercase;"><?php echo $currentPref['theme']; ?></strong></td>
                    <td><?php echo $currentPref['language']; ?></td>
                    <td><?php echo $currentPref['updated_at']; ?></td>
                </tr>
            </table>
        <?php else: ?>
            <p style="color: #ef4444;">❌ No preference found for this business!</p>
        <?php endif; ?>
        
        <div class="sql">
            SQL: SELECT * FROM user_preferences WHERE user_id = <?php echo $currentUser['id']; ?> AND branch_id = '<?php echo ACTIVE_BUSINESS_ID; ?>'
        </div>
    </div>
    
    <div class="box">
        <h3>🎨 Direct Theme Change</h3>
        <form method="POST">
            <input type="hidden" name="action" value="insert">
            <button type="submit" name="theme" value="light" class="btn-light">☀️ SET LIGHT</button>
            <button type="submit" name="theme" value="dark" class="btn-dark">🌙 SET DARK</button>
        </form>
    </div>
    
    <div class="box">
        <h3>📋 All Preferences for User #<?php echo $currentUser['id']; ?></h3>
        <?php if (count($allPrefs) > 0): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Branch ID</th>
                    <th>Theme</th>
                    <th>Language</th>
                    <th>Updated</th>
                </tr>
                <?php foreach ($allPrefs as $pref): ?>
                    <tr <?php echo $pref['branch_id'] === ACTIVE_BUSINESS_ID ? 'class="current"' : ''; ?>>
                        <td><?php echo $pref['id']; ?></td>
                        <td><?php echo $pref['branch_id']; ?><?php echo $pref['branch_id'] === ACTIVE_BUSINESS_ID ? ' <strong>(CURRENT)</strong>' : ''; ?></td>
                        <td><strong><?php echo strtoupper($pref['theme']); ?></strong></td>
                        <td><?php echo $pref['language']; ?></td>
                        <td><?php echo $pref['updated_at']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p style="color: #6b7280;">No preferences found.</p>
        <?php endif; ?>
    </div>
    
    <div class="box">
        <h3>🗑️ Reset/Delete</h3>
        <form method="POST" onsubmit="return confirm('Delete preference for this business?')">
            <input type="hidden" name="action" value="delete">
            <button type="submit" class="btn-danger">Delete This Business Preference</button>
        </form>
        <form method="POST" style="margin-top: 10px;" onsubmit="return confirm('Delete ALL preferences?')">
            <input type="hidden" name="action" value="delete_all">
            <button type="submit" class="btn-danger">Delete ALL Preferences</button>
        </form>
    </div>
    
    <div style="margin-top: 30px;">
        <a href="index.php" style="padding: 10px 20px; background: #4f46e5; color: white; text-decoration: none; border-radius: 6px;">🏠 Dashboard</a>
        <a href="modules/settings/display.php" style="padding: 10px 20px; background: #059669; color: white; text-decoration: none; border-radius: 6px; margin-left: 10px;">⚙️ Settings</a>
    </div>
</div>
</body>
</html>
