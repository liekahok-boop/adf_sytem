<?php
/**
 * Investor & Project Module Installation Script
 * Run this script once to initialize the database
 */

session_start();
defined('APP_ACCESS') or die('Direct access not permitted');

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

// Check admin access
$auth = new Auth();
if (!$auth->isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    die('Admin access required');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('POST request required');
}

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<h1>🔧 Installing Investor & Project Modules...</h1>";
    echo "<p>This may take a few moments...</p>";
    echo "<hr>";

    // Read migration file
    $migration_file = __DIR__ . '/../database/migration-investor-project.sql';
    if (!file_exists($migration_file)) {
        throw new Exception('Migration file not found: ' . $migration_file);
    }

    $sql = file_get_contents($migration_file);
    
    // Split by semicolon and execute each statement
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($s) { return !empty($s) && !preg_match('/^--/', $s); }
    );

    $executed = 0;
    foreach ($statements as $statement) {
        if (strpos($statement, 'INSERT') !== false || strpos($statement, 'CREATE') !== false) {
            echo "<p>✓ Executing: " . substr($statement, 0, 60) . "...</p>";
            $db->exec($statement);
            $executed++;
        }
    }

    echo "<hr>";
    echo "<p style='color: green; font-weight: bold;'>✅ Installation completed successfully!</p>";
    echo "<p>Executed " . $executed . " database statements</p>";
    echo "<p><a href='" . BASE_URL . "/modules/investor/index.php'>Go to Investor Module</a></p>";
    echo "<p><a href='" . BASE_URL . "/modules/project/index.php'>Go to Project Module</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>❌ Installation Failed!</p>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p><a href='javascript:history.back()'>Go Back</a></p>";
}
?>
