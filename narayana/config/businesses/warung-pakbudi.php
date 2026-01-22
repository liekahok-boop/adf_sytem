<?php
return [
    'business_id' => 'warung-pakbudi',
    'business_name' => 'Warung Pak Budi',
    'business_type' => 'restaurant',
    'database' => 'narayana_warung_pakbudi',
    
    'enabled_modules' => [
        'cashbook',
        'auth',
        'settings',
        'reports',
        'menu',
        'orders',
        'kitchen',
        'tables'
    ],
    
    'theme' => [
        'color_primary' => '#f97316',
        'color_secondary' => '#ea580c',
        'icon' => '🍽️'
    ],
    
    'cashbook_columns' => [
        'table_number' => ['label' => 'Table #', 'type' => 'text'],
        'waiter_name' => ['label' => 'Waiter', 'type' => 'text']
    ]
];
