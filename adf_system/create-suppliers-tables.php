<?php
/**
 * Create Suppliers Table
 * Add suppliers table to all business databases
 */

require_once 'config/database.php';

echo "Creating suppliers and procurement tables across all businesses...\n\n";

$db = Database::getInstance();

// Get list of all business databases
$databases = [];
$result = $db->getConnection()->query("SHOW DATABASES LIKE 'adf_%'");
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    foreach ($row as $value) {
        if (!empty($value) && strpos($value, 'adf_') === 0) {
            $databases[] = $value;
        }
    }
}

echo "Found " . count($databases) . " business databases\n\n";

$suppliersSQL = "
CREATE TABLE IF NOT EXISTS suppliers (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    supplier_code VARCHAR(50) NOT NULL UNIQUE,
    supplier_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255),
    phone VARCHAR(50),
    email VARCHAR(255),
    address TEXT,
    tax_number VARCHAR(100),
    payment_terms ENUM('cash', 'net_7', 'net_14', 'net_30', 'net_45', 'net_60') DEFAULT 'net_30',
    is_active TINYINT(1) DEFAULT 1,
    created_by INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

$poHeaderSQL = "
CREATE TABLE IF NOT EXISTS purchase_orders_header (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    po_number VARCHAR(50) NOT NULL UNIQUE,
    supplier_id INT(11) NOT NULL,
    po_date DATE NOT NULL,
    expected_delivery_date DATE,
    status ENUM('draft', 'submitted', 'approved', 'rejected', 'partially_received', 'completed', 'cancelled') DEFAULT 'draft',
    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(15,2) DEFAULT 0,
    tax_amount DECIMAL(15,2) DEFAULT 0,
    grand_total DECIMAL(15,2) NOT NULL DEFAULT 0,
    notes TEXT,
    approved_by INT(11),
    approved_at TIMESTAMP NULL,
    created_by INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE RESTRICT,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_po_date (po_date),
    INDEX idx_status (status),
    INDEX idx_supplier (supplier_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

$poDetailSQL = "
CREATE TABLE IF NOT EXISTS purchase_orders_detail (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    po_header_id INT(11) NOT NULL,
    line_number INT(11) NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    item_description TEXT,
    unit_of_measure VARCHAR(50) DEFAULT 'pcs',
    quantity DECIMAL(15,2) NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    division_id INT(11) NOT NULL COMMENT 'Cost Center',
    received_quantity DECIMAL(15,2) DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (po_header_id) REFERENCES purchase_orders_header(id) ON DELETE CASCADE,
    FOREIGN KEY (division_id) REFERENCES divisions(id) ON DELETE RESTRICT,
    INDEX idx_po_header (po_header_id),
    INDEX idx_division (division_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

foreach ($databases as $dbName) {
    echo "Processing: $dbName\n";
    
    try {
        // Switch to database
        $db->getConnection()->exec("USE $dbName");
        
        // Check if suppliers table exists
        $suppliersExists = $db->fetchOne("SHOW TABLES LIKE 'suppliers'");
        
        if (!$suppliersExists) {
            // Create suppliers table
            $db->getConnection()->exec($suppliersSQL);
            echo "  ✓ Created suppliers table\n";
            
            // Create purchase_orders_header table
            $db->getConnection()->exec($poHeaderSQL);
            echo "  ✓ Created purchase_orders_header table\n";
            
            // Create purchase_orders_detail table
            $db->getConnection()->exec($poDetailSQL);
            echo "  ✓ Created purchase_orders_detail table\n";
        } else {
            echo "  ℹ Suppliers table already exists\n";
        }
        
    } catch (Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n✅ Suppliers and procurement tables setup complete!\n";
?>
