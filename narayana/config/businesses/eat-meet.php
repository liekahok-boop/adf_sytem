<?php
return [
    'business_id' => 'eat-meet',
    'name' => 'Eat & Meet Restaurant',
    'business_type' => 'restaurant',
    'database' => 'narayana_db',  // Use same database, different config
    
    'enabled_modules' => [
        'cashbook',
        'auth',
        'settings',
        'reports',
        'divisions',
        'procurement',
        'sales'
        // Module khusus: menu, orders, kitchen (belum dibuat, nanti ditambah)
    ],
    
    'theme' => [
        'color_primary' => '#f59e0b',
        'color_secondary' => '#d97706',
        'icon' => '🍽️'
    ],
    
    'cashbook_columns' => [
        'table_number' => ['label' => 'Table #', 'type' => 'text', 'required' => false],
        'order_number' => ['label' => 'Order #', 'type' => 'text', 'required' => false],
        'waiter_name' => ['label' => 'Server', 'type' => 'text', 'required' => false]
    ],
    
    'dashboard_widgets' => [
        'show_daily_sales' => true,
        'show_orders' => true,
        'show_revenue' => true,
        'show_best_sellers' => true
    ]
];
