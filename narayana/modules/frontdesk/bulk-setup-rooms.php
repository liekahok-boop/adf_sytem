<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get buildings
    $buildings = $db->fetchAll("SELECT * FROM buildings ORDER BY id");
    
    // Get default room type (first one)
    $defaultType = $db->fetchOne("SELECT id FROM room_types ORDER BY id LIMIT 1");
    
    if (!$defaultType) {
        setFlash('error', 'Silakan buat Room Type terlebih dahulu!');
        header('Location: manage-rooms.php');
        exit;
    }
    
    $defaultTypeId = $defaultType['id'];
    $generatedCount = 0;
    
    foreach ($buildings as $building) {
        $buildingId = $building['id'];
        $targetTotal = isset($_POST["building_$buildingId"]) ? intval($_POST["building_$buildingId"]) : 0;
        
        if ($targetTotal <= 0) continue;
        
        // Get current room count
        $currentCount = $db->fetchOne("SELECT COUNT(*) as total FROM rooms WHERE building_id = ?", [$buildingId])['total'];
        
        // Calculate how many to generate
        $toGenerate = $targetTotal - $currentCount;
        
        if ($toGenerate <= 0) continue;
        
        // Get last room number to continue sequence
        $lastRoom = $db->fetchOne("SELECT room_number FROM rooms WHERE building_id = ? ORDER BY id DESC LIMIT 1", [$buildingId]);
        
        // Extract number from last room (e.g., "101" from "101" or "A-101")
        $startNumber = 101;
        if ($lastRoom) {
            preg_match('/\d+/', $lastRoom['room_number'], $matches);
            if (!empty($matches)) {
                $startNumber = intval($matches[0]) + 1;
            }
        }
        
        // Generate rooms
        for ($i = 0; $i < $toGenerate; $i++) {
            $roomNumber = $building['building_code'] . '-' . ($startNumber + $i);
            
            // Determine floor (assuming 2 floors, alternating)
            $floorNumber = (($currentCount + $i) % 2 == 0) ? 1 : 2;
            
            try {
                $db->insert('rooms', [
                    'building_id' => $buildingId,
                    'room_number' => $roomNumber,
                    'room_type_id' => $defaultTypeId,
                    'floor_number' => $floorNumber,
                    'status' => 'available',
                    'notes' => 'Auto-generated'
                ]);
                $generatedCount++;
            } catch (Exception $e) {
                // Skip if duplicate
                continue;
            }
        }
    }
    
    if ($generatedCount > 0) {
        setFlash('success', "$generatedCount room berhasil di-generate!");
    } else {
        setFlash('info', 'Tidak ada room yang di-generate. Target sudah tercapai.');
    }
}

header('Location: manage-rooms.php');
exit;
?>
