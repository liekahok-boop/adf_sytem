<?php
require_once 'config/database.php';

echo "Testing suppliers table and purchase order creation...\n\n";

$db = Database::getInstance();

// Check suppliers table
try {
    $result = $db->fetchOne("DESCRIBE suppliers");
    if ($result) {
        echo "✓ Suppliers table exists\n";
        echo "Columns:\n";
        $columns = $db->fetchAll("DESCRIBE suppliers");
        foreach ($columns as $col) {
            echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error checking suppliers: " . $e->getMessage() . "\n";
}

echo "\n";

// Check purchase_orders_header table
try {
    $columns = $db->fetchAll("DESCRIBE purchase_orders_header");
    echo "✓ Purchase_orders_header table exists\n";
    echo "Columns:\n";
    foreach ($columns as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "✗ Error checking purchase_orders_header: " . $e->getMessage() . "\n";
}

echo "\n";

// Check purchase_orders_detail table
try {
    $columns = $db->fetchAll("DESCRIBE purchase_orders_detail");
    echo "✓ Purchase_orders_detail table exists\n";
    echo "Columns:\n";
    foreach ($columns as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "✗ Error checking purchase_orders_detail: " . $e->getMessage() . "\n";
}
?>
