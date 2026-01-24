<?php
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $stmt = $pdo->query("SHOW DATABASES LIKE 'adf_%'");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "ADF Databases found:\n";
    foreach ($databases as $db) {
        echo "  - $db\n";
    }
    
    if (empty($databases)) {
        echo "  (No ADF databases found)\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
