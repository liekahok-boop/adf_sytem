<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();
$pageTitle = 'Master Map - Bird\'s Eye View';

// Get all buildings with room counts
$buildings = $db->fetchAll("
    SELECT 
        b.*,
        COUNT(r.id) as total_rooms,
        SUM(CASE WHEN bk.status = 'checked_in' THEN 1 ELSE 0 END) as occupied_rooms
    FROM buildings b
    LEFT JOIN rooms r ON b.id = r.building_id
    LEFT JOIN bookings bk ON r.id = bk.room_id 
        AND bk.status = 'checked_in'
        AND CURDATE() BETWEEN bk.check_in_date AND DATE_SUB(bk.check_out_date, INTERVAL 1 DAY)
    WHERE b.is_active = 1
    GROUP BY b.id
    ORDER BY b.id
");

// Get rooms for each building
$roomsByBuilding = [];
foreach ($buildings as $building) {
    $rooms = $db->fetchAll("
        SELECT 
            r.*,
            rt.type_name,
            CASE 
                WHEN bk.status = 'checked_in' THEN 'occupied'
                WHEN r.status = 'maintenance' THEN 'maintenance'
                WHEN r.status = 'cleaning' THEN 'cleaning'
                ELSE 'available'
            END as display_status
        FROM rooms r
        LEFT JOIN room_types rt ON r.room_type_id = rt.id
        LEFT JOIN bookings bk ON r.id = bk.room_id 
            AND bk.status = 'checked_in'
            AND CURDATE() BETWEEN bk.check_in_date AND DATE_SUB(bk.check_out_date, INTERVAL 1 DAY)
        WHERE r.building_id = ?
        ORDER BY r.floor_number DESC, r.room_number
    ", [$building['id']]);
    
    $roomsByBuilding[$building['id']] = $rooms;
}

include '../../includes/header.php';
?>

<style>
.master-map-container {
    background: var(--bg-secondary);
    border-radius: 16px;
    padding: 2rem;
    min-height: 600px;
    position: relative;
    overflow: hidden;
}

.building-svg {
    cursor: pointer;
    transition: all 0.4s ease;
}

.building-svg:hover {
    filter: drop-shadow(0 12px 32px rgba(99, 102, 241, 0.5));
}

.room-hover:hover rect {
    stroke-width: 3.5;
    filter: brightness(1.1);
}

.building-info-card {
    background: var(--bg-primary);
    border: 2px solid var(--bg-tertiary);
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.building-info-card:hover {
    border-color: var(--primary-color);
    transform: translateY(-2px);
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.legend-color {
    width: 24px;
    height: 24px;
    border-radius: 6px;
}
</style>

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.25rem;">
            🗺️ Master Map
        </h2>
        <p style="color: var(--text-muted); font-size: 0.813rem;">Bird's Eye View - All Buildings</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="index.php" class="btn btn-secondary">
            <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i>
            Back
        </a>
        <a href="manage-rooms.php" class="btn btn-success">
            <i data-feather="database" style="width: 14px; height: 14px;"></i>
            Setup Rooms
        </a>
        <a href="manage-buildings.php" class="btn btn-primary">
            <i data-feather="settings" style="width: 14px; height: 14px;"></i>
            Buildings
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 250px 1fr; gap: 1.5rem;">
    
    <!-- Building List Sidebar -->
    <div>
        <div class="card" style="padding: 0;">
            <div style="padding: 1rem; border-bottom: 1px solid var(--bg-tertiary);">
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                    Buildings (<?php echo count($buildings); ?>)
                </h3>
            </div>
            <div style="padding: 1rem;">
                <?php foreach ($buildings as $building): ?>
                    <?php
                    $occupancy = $building['total_rooms'] > 0 
                        ? round(($building['occupied_rooms'] / $building['total_rooms']) * 100) 
                        : 0;
                    ?>
                    <div class="building-info-card" onclick="focusBuilding(<?php echo $building['id']; ?>)">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: <?php echo $building['color_theme']; ?>20; display: flex; align-items: center; justify-content: center;">
                                <i data-feather="home" style="width: 20px; height: 20px; color: <?php echo $building['color_theme']; ?>;"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 700; font-size: 0.875rem; color: var(--text-primary);">
                                    <?php echo $building['building_code']; ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    <?php echo $building['building_name']; ?>
                                </div>
                            </div>
                            <button onclick="event.stopPropagation(); editBuilding(<?php echo $building['id']; ?>)" 
                                    style="padding: 0.375rem 0.625rem; background: var(--bg-tertiary); border: 1px solid var(--bg-tertiary); border-radius: 6px; color: var(--text-secondary); cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; gap: 0.25rem;"
                                    onmouseover="this.style.background='var(--primary-color)'; this.style.borderColor='var(--primary-color)'; this.style.color='white';"
                                    onmouseout="this.style.background='var(--bg-tertiary)'; this.style.borderColor='var(--bg-tertiary)'; this.style.color='var(--text-secondary)';">
                                <i data-feather="edit-2" style="width: 14px; height: 14px;"></i>
                            </button>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--bg-tertiary);">
                            <div>
                                <div style="color: var(--text-muted);">Rooms</div>
                                <div style="font-weight: 700; color: var(--text-primary);"><?php echo $building['total_rooms']; ?></div>
                            </div>
                            <div>
                                <div style="color: var(--text-muted);">Occupied</div>
                                <div style="font-weight: 700; color: #ef4444;"><?php echo $building['occupied_rooms']; ?></div>
                            </div>
                            <div>
                                <div style="color: var(--text-muted);">Rate</div>
                                <div style="font-weight: 700; color: <?php echo $building['color_theme']; ?>;"><?php echo $occupancy; ?>%</div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Legend -->
        <div class="card" style="margin-top: 1rem;">
            <h4 style="font-size: 0.875rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.75rem;">
                <i data-feather="info" style="width: 14px; height: 14px;"></i>
                Legend
            </h4>
            <div class="legend-item">
                <div class="legend-color" style="background: #10b981;"></div>
                <span style="font-size: 0.75rem;">Available</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #ef4444;"></div>
                <span style="font-size: 0.75rem;">Occupied</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #f59e0b;"></div>
                <span style="font-size: 0.75rem;">Cleaning</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #6b7280;"></div>
                <span style="font-size: 0.75rem;">Maintenance</span>
            </div>
        </div>
    </div>
    
    <!-- Master Map SVG -->
    <div class="master-map-container">
        <svg viewBox="-400 -300 800 600" width="100%" height="100%" style="background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%); border-radius: 12px; box-shadow: inset 0 2px 8px rgba(0,0,0,0.1);">
            <!-- Subtle Grid Background -->
            <defs>
                <pattern id="grid" width="50" height="50" patternUnits="userSpaceOnUse">
                    <circle cx="25" cy="25" r="1" fill="var(--bg-tertiary)" opacity="0.3"/>
                </pattern>
                <filter id="shadow">
                    <feDropShadow dx="0" dy="4" stdDeviation="6" flood-opacity="0.2"/>
                </filter>
            </defs>
            <rect x="-400" y="-300" width="800" height="600" fill="url(#grid)"/>
            
            <!-- Buildings -->
            <?php foreach ($buildings as $building): ?>
                <?php 
                $buildingRooms = $roomsByBuilding[$building['id']] ?? [];
                $roomColors = [
                    'available' => '#10b981',
                    'occupied' => '#ef4444',
                    'cleaning' => '#f59e0b',
                    'maintenance' => '#6b7280'
                ];
                ?>
                <g class="building-svg" 
                   id="building-<?php echo $building['id']; ?>"
                   transform="translate(<?php echo $building['position_x']; ?>, <?php echo $building['position_y']; ?>) rotate(<?php echo $building['rotation_angle']; ?>)"
                   onclick="goToBuilding(<?php echo $building['id']; ?>)">
                    
                    <!-- Modern building shape with shadow -->
                    <path d="M -100 50 L -100 -50 A 100 50 0 0 1 100 -50 L 100 50 Z" 
                          fill="<?php echo $building['color_theme']; ?>15" 
                          stroke="<?php echo $building['color_theme']; ?>" 
                          stroke-width="2.5"
                          filter="url(#shadow)"
                          rx="5"/>
                    
                    <!-- Building top accent -->
                    <path d="M -100 -50 A 100 50 0 0 1 100 -50" 
                          fill="none" 
                          stroke="<?php echo $building['color_theme']; ?>" 
                          stroke-width="4"
                          opacity="0.4"/>
                    
                    <!-- Floor Separator Line -->
                    <line x1="-100" y1="0" x2="100" y2="0" 
                          stroke="<?php echo $building['color_theme']; ?>" 
                          stroke-width="2" 
                          stroke-dasharray="5,3"
                          opacity="0.6"/>
                    
                    <!-- Floor Labels -->
                    <text x="-85" y="-35" 
                          fill="var(--text-muted)" 
                          font-size="9" 
                          font-weight="600"
                          opacity="0.7">
                        Lantai 2
                    </text>
                    <text x="-85" y="40" 
                          fill="var(--text-muted)" 
                          font-size="9" 
                          font-weight="600"
                          opacity="0.7">
                        Lantai 1
                    </text>
                    
                    <!-- Modern Central Stairs with 3D effect -->
                    <defs>
                        <linearGradient id="stairGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" style="stop-color:var(--bg-tertiary);stop-opacity:1" />
                            <stop offset="100%" style="stop-color:var(--text-muted);stop-opacity:0.5" />
                        </linearGradient>
                    </defs>
                    <rect x="-10" y="-5" width="20" height="60" 
                          fill="url(#stairGradient)" 
                          stroke="<?php echo $building['color_theme']; ?>" 
                          stroke-width="2"
                          rx="2"/>
                    <line x1="-10" y1="5" x2="10" y2="5" stroke="white" stroke-width="1.5" opacity="0.4"/>
                    <line x1="-10" y1="15" x2="10" y2="15" stroke="white" stroke-width="1.5" opacity="0.4"/>
                    <line x1="-10" y1="25" x2="10" y2="25" stroke="white" stroke-width="1.5" opacity="0.4"/>
                    <line x1="-10" y1="35" x2="10" y2="35" stroke="white" stroke-width="1.5" opacity="0.4"/>
                    <line x1="-10" y1="45" x2="10" y2="45" stroke="white" stroke-width="1.5" opacity="0.4"/>
                    
                    <!-- Rooms with dynamic data and modern design -->
                    <?php 
                    $roomPositions = [
                        ['x' => -85, 'y' => -45], // Left L2
                        ['x' => 25, 'y' => -45],  // Right L2
                        ['x' => -85, 'y' => 10],  // Left L1
                        ['x' => 25, 'y' => 10]    // Right L1
                    ];
                    
                    foreach ($buildingRooms as $index => $room):
                        if ($index >= 4) break;
                        $pos = $roomPositions[$index];
                        $color = $roomColors[$room['display_status']] ?? '#10b981';
                    ?>
                        <g onclick="event.stopPropagation(); editRoom(<?php echo $room['id']; ?>)" style="cursor: pointer;" class="room-hover">
                            <!-- Room shadow --><rect x="<?php echo $pos['x'] + 2; ?>" y="<?php echo $pos['y'] + 2; ?>" width="60" height="35" 
                                  fill="black" 
                                  opacity="0.15"
                                  rx="6"/>
                            <!-- Room main -->
                            <rect x="<?php echo $pos['x']; ?>" y="<?php echo $pos['y']; ?>" width="60" height="35" 
                                  fill="<?php echo $color; ?>" 
                                  stroke="white" 
                                  stroke-width="2.5"
                                  rx="6"/>
                            <!-- Room number -->
                            <text x="<?php echo $pos['x'] + 30; ?>" y="<?php echo $pos['y'] + 19; ?>" 
                                  text-anchor="middle" 
                                  fill="white" 
                                  font-size="13" 
                                  font-weight="800">
                                <?php echo $room['room_number']; ?>
                            </text>
                            <!-- Room type -->
                            <text x="<?php echo $pos['x'] + 30; ?>" y="<?php echo $pos['y'] + 31; ?>" 
                                  text-anchor="middle" 
                                  fill="white" 
                                  font-size="8" 
                                  font-weight="600"
                                  opacity="0.95">
                                <?php echo $room['type_name']; ?>
                            </text>
                        </g>
                    <?php endforeach; ?>
                    
                    <!-- Building Label (below the building) -->
                    <text x="0" y="80" 
                          text-anchor="middle" 
                          fill="<?php echo $building['color_theme']; ?>" 
                          font-size="14" 
                          font-weight="700">
                        <?php echo $building['building_code']; ?>
                    </text>
                    <text x="0" y="95" 
                          text-anchor="middle" 
                          fill="var(--text-muted)" 
                          font-size="10" 
                          font-weight="500">
                        <?php echo $building['building_name']; ?>
                    </text>
                    
                    <!-- Occupancy Badge (top center) -->
                    <rect x="-30" y="-70" width="60" height="20" 
                          fill="var(--bg-secondary)" 
                          stroke="<?php echo $building['color_theme']; ?>" 
                          stroke-width="2"
                          rx="10"/>
                    <text x="0" y="-55" 
                          text-anchor="middle" 
                          fill="var(--text-primary)" 
                          font-size="12" 
                          font-weight="700">
                        <?php 
                        $occ = $building['total_rooms'] > 0 ? round(($building['occupied_rooms'] / $building['total_rooms']) * 100) : 0;
                        echo $occ . '%'; 
                        ?>
                    </text>
                </g>
            <?php endforeach; ?>
        </svg>
        
        <!-- Zoom Controls -->
        <div style="position: absolute; bottom: 1rem; right: 1rem; display: flex; flex-direction: column; gap: 0.5rem;">
            <button onclick="zoomIn()" class="btn btn-secondary" style="width: 40px; height: 40px; padding: 0;">
                <i data-feather="plus" style="width: 20px; height: 20px;"></i>
            </button>
            <button onclick="zoomOut()" class="btn btn-secondary" style="width: 40px; height: 40px; padding: 0;">
                <i data-feather="minus" style="width: 20px; height: 20px;"></i>
            </button>
            <button onclick="resetZoom()" class="btn btn-secondary" style="width: 40px; height: 40px; padding: 0;">
                <i data-feather="maximize" style="width: 20px; height: 20px;"></i>
            </button>
        </div>
    </div>
</div>

<script>
feather.replace();

let currentZoom = 1;
const svg = document.querySelector('svg');

function zoomIn() {
    currentZoom += 0.2;
    svg.style.transform = `scale(${currentZoom})`;
}

function zoomOut() {
    if (currentZoom > 0.4) {
        currentZoom -= 0.2;
        svg.style.transform = `scale(${currentZoom})`;
    }
}

function resetZoom() {
    currentZoom = 1;
    svg.style.transform = 'scale(1)';
}

function focusBuilding(buildingId) {
    const building = document.getElementById('building-' + buildingId);
    if (building) {
        building.style.filter = 'drop-shadow(0 0 20px rgba(99, 102, 241, 0.8))';
        setTimeout(() => {
            building.style.filter = '';
        }, 2000);
    }
}

function editBuilding(buildingId) {
    window.location.href = 'edit-building.php?id=' + buildingId;
}

function goToBuilding(buildingId) {
    window.location.href = 'building-view.php?id=' + buildingId;
}

function editRoom(roomId) {
    window.location.href = 'edit-room.php?id=' + roomId;
}
</script>

<?php include '../../includes/footer.php'; ?>
