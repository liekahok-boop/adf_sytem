<?php
return [
    'business_id' => 'cafe-kopi',
    'business_name' => 'Kopi Kenangan Kudus',
    'business_type' => 'cafe',
    'database' => 'narayana_cafe_kopi',
    
    'enabled_modules' => [
        'cashbook',
        'auth',
        'settings',
        'reports',
        'menu',
        'orders',
        'barista'
    ],
    
    'theme' => [
        'color_primary' => '#92400e',
        'color_secondary' => '#78350f',
        'icon' => '☕'
    ],
    
    'cashbook_columns' => [
        'order_number' => ['label' => 'Order #', 'type' => 'text'],
        'barista_name' => ['label' => 'Barista', 'type' => 'text']
    ]
];
