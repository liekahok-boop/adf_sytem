<?php
return [
    'business_id' => 'furniture-jepara',
    'name' => 'Furniture Jepara',
    'business_type' => 'furniture',
    'database' => 'narayana_furniture',  // Use same database, different config
    
    'enabled_modules' => [
        'cashbook',
        'auth',
        'settings',
        'reports',
        'divisions',
        'procurement',
        'sales'
        // Module khusus: products, workshop, orders (nanti ditambah)
    ],
    
    'theme' => [
        'color_primary' => '#92400e',
        'color_secondary' => '#78350f',
        'icon' => '🪑'
    ],
    
    'cashbook_columns' => [
        'order_number' => ['label' => 'Order #', 'type' => 'text', 'required' => false],
        'product_name' => ['label' => 'Product', 'type' => 'text', 'required' => false],
        'customer_name' => ['label' => 'Customer', 'type' => 'text', 'required' => false]
    ],
    
    'dashboard_widgets' => [
        'show_orders' => true,
        'show_production' => true,
        'show_revenue' => true,
        'show_inventory' => true
    ],
    
    'terminology' => [
        'customer' => 'Buyer',
        'order' => 'Custom Order',
        'product' => 'Furniture'
    ]
];
