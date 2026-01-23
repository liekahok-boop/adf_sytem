<?php
/**
 * Business Configuration
 * Defines all businesses with separate databases
 */

$BUSINESSES = [
    [
        'id' => 1,
        'name' => "Ben's Cafe",
        'database' => 'narayana_benscafe',
        'type' => 'restaurant',
        'active' => true
    ],
    [
        'id' => 2,
        'name' => 'Hotel',
        'database' => 'narayana_hotel',
        'type' => 'hotel',
        'active' => true
    ],
    [
        'id' => 3,
        'name' => 'Eat & Meet Restaurant',
        'database' => 'narayana_eatmeet',
        'type' => 'restaurant',
        'active' => true
    ],
    [
        'id' => 4,
        'name' => 'Pabrik Kapal',
        'database' => 'narayana_pabrikkapal',
        'type' => 'manufacture',
        'active' => true
    ],
    [
        'id' => 5,
        'name' => 'Furniture',
        'database' => 'narayana_furniture',
        'type' => 'retail',
        'active' => true
    ],
    [
        'id' => 6,
        'name' => 'Karimunjawa Tourism',
        'database' => 'narayana_karimunjawa',
        'type' => 'tourism',
        'active' => true
    ]
];

// Helper function to get business by ID
function getBusinessById($id) {
    global $BUSINESSES;
    foreach ($BUSINESSES as $business) {
        if ($business['id'] == $id) {
            return $business;
        }
    }
    return null;
}

// Helper function to get business by database name
function getBusinessByDatabase($dbName) {
    global $BUSINESSES;
    foreach ($BUSINESSES as $business) {
        if ($business['database'] == $dbName) {
            return $business;
        }
    }
    return null;
}

// Helper function to get all active businesses
function getActiveBusinesses() {
    global $BUSINESSES;
    return array_filter($BUSINESSES, function($b) {
        return $b['active'] === true;
    });
}
