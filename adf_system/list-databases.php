<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/config/config.php';

echo "<h2>Checking Available Databases</h2>";

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $stmt = $pdo->query("SHOW DATABASES LIKE 'adf_%'");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Found " . count($databases) . " databases:</h3>";
    echo "<ul>";
    foreach ($databases as $db) {
        echo "<li><strong>$db</strong></li>";
    }
    echo "</ul>";
    
    echo "<h3>Database List (for code):</h3>";
    echo "<pre>";
    foreach ($databases as $db) {
        echo "'$db',\n";
    }
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
