<?php
return [
    'business_id' => 'karimunjawa-party-boat',
    'business_name' => 'Karimunjawa Party Boat',
    'business_type' => 'tourism',
    'database' => 'narayana',
    
    'enabled_modules' => [
        'cashbook',
        'auth',
        'settings',
        'reports',
        'divisions',
        'sales'
        // Module khusus: bookings, boats, trips, packages (nanti ditambah)
    ],
    
    'theme' => [
        'color_primary' => '#0284c7',
        'color_secondary' => '#0369a1',
        'icon' => '🚤'
    ],
    
    'cashbook_columns' => [
        'booking_code' => ['label' => 'Booking Code', 'type' => 'text', 'required' => false],
        'boat_name' => ['label' => 'Boat Name', 'type' => 'text', 'required' => false],
        'trip_date' => ['label' => 'Trip Date', 'type' => 'date', 'required' => false],
        'guest_name' => ['label' => 'Guest Name', 'type' => 'text', 'required' => false]
    ],
    
    'dashboard_widgets' => [
        'show_bookings' => true,
        'show_trips' => true,
        'show_revenue' => true,
        'show_boats' => true
    ],
    
    'terminology' => [
        'customer' => 'Guest',
        'order' => 'Booking',
        'product' => 'Package'
    ]
];
