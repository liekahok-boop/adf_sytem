<?php
require_once 'config/database.php';
require_once 'includes/procurement_functions.php';
require_once 'includes/auth.php';

echo "Testing Purchase Order Creation...\n\n";

$db = Database::getInstance();

// Check if we have suppliers
try {
    $suppliers = $db->fetchAll("SELECT * FROM suppliers LIMIT 1");
    if (empty($suppliers)) {
        echo "No suppliers found. Creating test supplier...\n";
        
        // Get first user
        $user = $db->fetchOne("SELECT id FROM users LIMIT 1");
        if (!$user) {
            echo "No users found! Cannot create supplier.\n";
            exit;
        }
        
        $supplierData = [
            'supplier_code' => 'TEST-001',
            'supplier_name' => 'Test Supplier',
            'contact_person' => 'John Doe',
            'phone' => '08123456789',
            'email' => 'test@supplier.com',
            'address' => 'Test Address',
            'is_active' => 1,
            'created_by' => $user['id']
        ];
        
        $supplierId = $db->insert('suppliers', $supplierData);
        echo "✓ Test supplier created with ID: $supplierId\n\n";
    } else {
        $supplierId = $suppliers[0]['id'];
        echo "✓ Found existing supplier ID: $supplierId\n\n";
    }
} catch (Exception $e) {
    echo "✗ Error with suppliers: " . $e->getMessage() . "\n";
    exit;
}

// Get division
try {
    $division = $db->fetchOne("SELECT id FROM divisions LIMIT 1");
    if (!$division) {
        echo "✗ No divisions found!\n";
        exit;
    }
    $divisionId = $division['id'];
    echo "✓ Found division ID: $divisionId\n\n";
} catch (Exception $e) {
    echo "✗ Error getting division: " . $e->getMessage() . "\n";
    exit;
}

// Get user
try {
    $user = $db->fetchOne("SELECT id FROM users LIMIT 1");
    if (!$user) {
        echo "✗ No users found!\n";
        exit;
    }
    $userId = $user['id'];
    echo "✓ Found user ID: $userId\n\n";
} catch (Exception $e) {
    echo "✗ Error getting user: " . $e->getMessage() . "\n";
    exit;
}

// Try to create PO
echo "Attempting to create PO...\n";
try {
    // Create mock Auth for createPurchaseOrder
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = 'admin';
    $_SESSION['full_name'] = 'Admin';
    $_SESSION['role'] = 'admin';
    
    $items = [
        [
            'item_name' => 'Test Item',
            'item_description' => 'Test Description',
            'unit_of_measure' => 'pcs',
            'quantity' => 5,
            'unit_price' => 100000,
            'division_id' => $divisionId,
            'notes' => 'Test note'
        ]
    ];
    
    $result = createPurchaseOrder($supplierId, date('Y-m-d'), $items, [
        'expected_delivery_date' => date('Y-m-d', strtotime('+7 days')),
        'discount_amount' => 0,
        'tax_amount' => 0
    ]);
    
    if ($result['success']) {
        echo "✓ PO created successfully!\n";
        echo "  PO ID: " . $result['po_id'] . "\n";
        echo "  PO Number: " . $result['po_number'] . "\n";
    } else {
        echo "✗ Error: " . $result['message'] . "\n";
    }
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
?>
