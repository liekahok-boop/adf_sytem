<?php
return [
    'business_id' => 'fitness-zone',
    'business_name' => 'Fitness Zone Semarang',
    'business_type' => 'gym',
    'database' => 'narayana_fitness_zone',
    
    'enabled_modules' => [
        'cashbook',
        'auth',
        'settings',
        'reports',
        'members',
        'classes',
        'trainers',
        'equipment'
    ],
    
    'theme' => [
        'color_primary' => '#dc2626',
        'color_secondary' => '#991b1b',
        'icon' => '🏋️'
    ],
    
    'cashbook_columns' => [
        'member_id' => ['label' => 'Member ID', 'type' => 'text'],
        'package_name' => ['label' => 'Package', 'type' => 'text']
    ]
];
