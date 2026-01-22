<?php
return [
    'business_id' => 'minimarket-212',
    'business_name' => 'Minimarket 212 Pati',
    'business_type' => 'retail',
    'database' => 'narayana_minimarket_212',
    
    'enabled_modules' => [
        'cashbook',
        'auth',
        'settings',
        'reports',
        'pos',
        'inventory',
        'products',
        'suppliers'
    ],
    
    'theme' => [
        'color_primary' => '#16a34a',
        'color_secondary' => '#15803d',
        'icon' => '🏪'
    ],
    
    'cashbook_columns' => [
        'product_code' => ['label' => 'Product Code', 'type' => 'text'],
        'quantity' => ['label' => 'Qty', 'type' => 'number']
    ]
];
