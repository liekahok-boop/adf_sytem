<?php
require_once 'config/config.php';
require_once 'config/database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Install Accounting Module</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#1e293b;color:#fff;}
.success{background:#10b981;padding:15px;border-radius:8px;margin:10px 0;}
.error{background:#ef4444;padding:15px;border-radius:8px;margin:10px 0;}
.info{background:#3b82f6;padding:15px;border-radius:8px;margin:10px 0;}
.btn{display:inline-block;padding:10px 20px;background:#6366f1;color:white;text-decoration:none;border-radius:5px;margin:10px 5px 10px 0;font-size:1.1em;}
h2{margin-top:20px;}
</style></head><body>";

echo "<h1>📊 Install Accounting Module</h1>";
echo "<p>Database: <strong>" . DB_NAME . "</strong></p><hr>";

try {
    // Baca SQL file
    $sql = file_get_contents('database-accounting.sql');
    
    if (!$sql) {
        throw new Exception("File database-accounting.sql tidak ditemukan!");
    }
    
    // Split by semicolon
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $success_count = 0;
    $errors = [];
    
    echo "<h2>📥 Executing SQL Statements...</h2>";
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) continue;
        
        try {
            $conn->exec($statement);
            $success_count++;
        } catch (PDOException $e) {
            $errors[] = $e->getMessage();
        }
    }
    
    if (empty($errors)) {
        echo "<div class='success'>";
        echo "<h2>✅ Installation Successful!</h2>";
        echo "<p><strong>$success_count</strong> SQL statements executed successfully.</p>";
        echo "</div>";
        
        // Cek data
        echo "<h2>📋 Verification:</h2>";
        
        $divCount = $db->fetchOne("SELECT COUNT(*) FROM divisions");
        $catCount = $db->fetchOne("SELECT COUNT(*) FROM categories");
        
        echo "<div class='info'>";
        echo "✓ <strong>Divisions Table:</strong> $divCount records<br>";
        echo "✓ <strong>Categories Table:</strong> $catCount records<br>";
        echo "✓ <strong>Cash Book Table:</strong> Ready for transactions<br>";
        echo "</div>";
        
        echo "<h2>🎉 Next Steps:</h2>";
        echo "<a href='setup-sample-data.php' class='btn'>📊 Generate Sample Transactions</a>";
        echo "<a href='index.php' class='btn'>🏠 Go to Dashboard</a>";
        
    } else {
        echo "<div class='error'>";
        echo "<strong>⚠️ Some errors occurred:</strong><br>";
        foreach ($errors as $err) {
            echo "• $err<br>";
        }
        echo "</div>";
        echo "<p>Note: Beberapa error mungkin normal (misalnya tabel sudah ada).</p>";
        echo "<a href='setup-sample-data.php' class='btn'>📊 Continue to Sample Data</a>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<strong>❌ Fatal Error:</strong><br>";
    echo $e->getMessage();
    echo "</div>";
}

echo "</body></html>";
?>
