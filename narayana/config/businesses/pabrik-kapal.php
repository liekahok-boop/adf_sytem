<?php
return [
    'business_id' => 'pabrik-kapal',
    'business_name' => 'Pabrik Kapal Indonesia',
    'business_type' => 'manufacturing',
    'database' => 'narayana',  // Use same database, different config
    
    'enabled_modules' => [
        'cashbook',
        'auth',
        'settings',
        'reports',
        'divisions',
        'procurement',
        'sales'
        // Module khusus: production, projects, inventory (nanti ditambah)
    ],
    
    'theme' => [
        'color_primary' => '#0891b2',
        'color_secondary' => '#0e7490',
        'icon' => '⛵'
    ],
    
    'cashbook_columns' => [
        'project_code' => ['label' => 'Project Code', 'type' => 'text', 'required' => false],
        'ship_name' => ['label' => 'Ship Name', 'type' => 'text', 'required' => false],
        'supplier_name' => ['label' => 'Supplier/Client', 'type' => 'text', 'required' => false]
    ],
    
    'dashboard_widgets' => [
        'show_projects' => true,
        'show_production' => true,
        'show_revenue' => true,
        'show_materials' => true
    ],
    
    'terminology' => [
        'customer' => 'Client',
        'order' => 'Project',
        'product' => 'Ship/Vessel'
    ]
];
