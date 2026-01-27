<?php
/**
 * Debug Theme Save - Check what happens when saving theme
 */
define('APP_ACCESS', true);
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

$auth = new Auth();
$auth->requireLogin();
$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();

echo "<h2>Debug Theme Save</h2><pre>";

echo "Current User ID: {$currentUser['id']}\n";
echo "Current Business: " . ACTIVE_BUSINESS_ID . "\n";
echo "Business Name: " . BUSINESS_NAME . "\n\n";

// Check current theme in database
$prefs = $db->fetchOne(
    "SELECT * FROM user_preferences WHERE user_id = ? AND branch_id = ?",
    [$currentUser['id'], ACTIVE_BUSINESS_ID]
);

echo "=== CURRENT PREFERENCES ===\n";
if ($prefs) {
    echo "Theme in DB: " . ($prefs['theme'] ?? 'NULL') . "\n";
    echo "Language: " . ($prefs['language'] ?? 'NULL') . "\n";
} else {
    echo "No preferences found for this business!\n";
}

// Show all preferences for this user
echo "\n=== ALL USER PREFERENCES ===\n";
$allPrefs = $db->fetchAll(
    "SELECT branch_id, theme, language FROM user_preferences WHERE user_id = ?",
    [$currentUser['id']]
);

if (count($allPrefs) > 0) {
    foreach ($allPrefs as $p) {
        echo "Business: {$p['branch_id']}, Theme: {$p['theme']}, Language: {$p['language']}\n";
    }
} else {
    echo "No preferences found at all!\n";
}

// Test form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "\n=== FORM SUBMITTED ===\n";
    echo "Posted Theme: " . ($_POST['theme'] ?? 'NOT SET') . "\n";
    
    $theme = $_POST['theme'] ?? 'dark';
    
    try {
        $existing = $db->fetchOne(
            "SELECT id FROM user_preferences WHERE user_id = ? AND branch_id = ?",
            [$currentUser['id'], ACTIVE_BUSINESS_ID]
        );
        
        if ($existing) {
            echo "Updating existing preference...\n";
            $result = $db->update('user_preferences', [
                'theme' => $theme,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'user_id = :user_id AND branch_id = :branch_id', [
                'user_id' => $currentUser['id'],
                'branch_id' => ACTIVE_BUSINESS_ID
            ]);
            echo "Update result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
        } else {
            echo "Inserting new preference...\n";
            $result = $db->insert('user_preferences', [
                'user_id' => $currentUser['id'],
                'branch_id' => ACTIVE_BUSINESS_ID,
                'theme' => $theme,
                'language' => 'id'
            ]);
            echo "Insert result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
        }
        
        // Verify
        $verify = $db->fetchOne(
            "SELECT theme FROM user_preferences WHERE user_id = ? AND branch_id = ?",
            [$currentUser['id'], ACTIVE_BUSINESS_ID]
        );
        echo "\nVerification - Theme now: " . ($verify['theme'] ?? 'NOT FOUND') . "\n";
        
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

echo "</pre>";
?>

<form method="POST" style="margin: 20px; padding: 20px; background: #f0f0f0; border-radius: 8px;">
    <h3>Test Theme Save</h3>
    <label>
        <input type="radio" name="theme" value="light" required> Light Theme
    </label><br>
    <label>
        <input type="radio" name="theme" value="dark" required> Dark Theme
    </label><br><br>
    <button type="submit" style="padding: 10px 20px; background: #4f46e5; color: white; border: none; border-radius: 4px; cursor: pointer;">Test Save</button>
</form>

<p style="margin: 20px;"><a href="modules/settings/display.php">Go to Display Settings</a></p>
