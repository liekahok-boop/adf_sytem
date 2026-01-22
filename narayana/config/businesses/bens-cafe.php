<?php
return [
    'business_id' => 'bens-cafe',
    'business_name' => 'Bens Cafe',
    'business_type' => 'cafe',
    'database' => 'narayana',
    
    'enabled_modules' => [
        'cashbook',
        'auth',
        'settings',
        'reports',
        'divisions',
        'procurement',
        'sales'
        // Module khusus: menu, orders, beverages (nanti ditambah)
    ],
    
    'theme' => [
        'color_primary' => '#92400e',
        'color_secondary' => '#78350f',
        'icon' => '☕'
    ],
    
    'cashbook_columns' => [
        'order_number' => ['label' => 'Order #', 'type' => 'text', 'required' => false],
        'table_number' => ['label' => 'Table #', 'type' => 'text', 'required' => false],
        'barista_name' => ['label' => 'Barista', 'type' => 'text', 'required' => false]
    ],
    
    'dashboard_widgets' => [
        'show_daily_sales' => true,
        'show_orders' => true,
        'show_revenue' => true,
        'show_best_drinks' => true
    ]
];
