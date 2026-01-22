<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();
$pageTitle = 'Front Desk';

// Get stats for dashboard
$stats = [];

// Total rooms
$stats['total_rooms'] = $db->fetchOne("SELECT COUNT(*) as total FROM rooms")['total'];

// Occupied today
$stats['occupied'] = $db->fetchOne("
    SELECT COUNT(DISTINCT r.id) as total 
    FROM rooms r
    JOIN bookings b ON r.id = b.room_id
    WHERE b.status = 'checked_in'
    AND CURDATE() BETWEEN b.check_in_date AND DATE_SUB(b.check_out_date, INTERVAL 1 DAY)
")['total'];

// Check-ins today
$stats['checkins_today'] = $db->fetchOne("
    SELECT COUNT(*) as total 
    FROM bookings 
    WHERE check_in_date = CURDATE()
    AND status IN ('confirmed', 'checked_in')
")['total'];

// Check-outs today
$stats['checkouts_today'] = $db->fetchOne("
    SELECT COUNT(*) as total 
    FROM bookings 
    WHERE check_out_date = CURDATE()
    AND status = 'checked_in'
")['total'];

// Revenue this month
$stats['revenue_month'] = $db->fetchOne("
    SELECT COALESCE(SUM(bp.amount), 0) as total
    FROM booking_payments bp
    JOIN bookings b ON bp.booking_id = b.id
    WHERE MONTH(bp.payment_date) = MONTH(CURDATE())
    AND YEAR(bp.payment_date) = YEAR(CURDATE())
")['total'];

// Available rooms
$stats['available'] = $stats['total_rooms'] - $stats['occupied'];

include '../../includes/header.php';
?>

<style>
.menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1.5rem;
}

.menu-card {
    background: linear-gradient(135deg, var(--bg-secondary), var(--bg-tertiary));
    border: 2px solid var(--bg-tertiary);
    border-radius: 12px;
    padding: 1.25rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.menu-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.2);
    border-color: var(--primary-color);
}

.menu-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
}

.menu-icon {
    width: 50px;
    height: 50px;
    margin: 0 auto 0.75rem;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.menu-card:hover .menu-icon {
    transform: scale(1.1) rotate(5deg);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: var(--bg-secondary);
    border: 2px solid var(--bg-tertiary);
    border-radius: 10px;
    padding: 1rem;
    text-align: center;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 800;
    margin-bottom: 0.25rem;
}

.stat-label {
    color: var(--text-muted);
    font-size: 0.75rem;
    font-weight: 600;
}
</style>

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.25rem;">
            🏨 Front Desk Dashboard
        </h2>
        <p style="color: var(--text-muted); font-size: 0.813rem;">
            <?php echo date('l, d F Y'); ?>
        </p>
    </div>
</div>

<!-- Stats Overview -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value" style="color: #6366f1;">
            <?php echo $stats['total_rooms']; ?>
        </div>
        <div class="stat-label">Total Rooms</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value" style="color: #10b981;">
            <?php echo $stats['available']; ?>
        </div>
        <div class="stat-label">Available</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value" style="color: #ef4444;">
            <?php echo $stats['occupied']; ?>
        </div>
        <div class="stat-label">Occupied</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value" style="color: #f59e0b;">
            <?php echo $stats['checkins_today']; ?>
        </div>
        <div class="stat-label">Check-in Today</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value" style="color: #8b5cf6;">
            <?php echo $stats['checkouts_today']; ?>
        </div>
        <div class="stat-label">Check-out Today</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value" style="color: #06b6d4; font-size: 1rem;">
            Rp <?php echo number_format($stats['revenue_month'], 0, ',', '.'); ?>
        </div>
        <div class="stat-label">Revenue Bulan Ini</div>
    </div>
</div>

<!-- Main Menu -->
<div class="card" style="padding: 1.5rem;">
    <h3 style="font-size: 1.125rem; font-weight: 800; color: var(--text-primary); margin-bottom: 1rem; text-align: center;">
        📋 Menu Front Desk
    </h3>
    
    <div class="menu-grid">
        <!-- Reservasi -->
        <a href="reservasi.php" style="text-decoration: none;">
            <div class="menu-card">
                <div class="menu-icon" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                    <i data-feather="calendar" style="width: 24px; height: 24px; color: white;"></i>
                </div>
                <h4 style="font-size: 0.938rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem;">
                    Reservasi
                </h4>
                <p style="color: var(--text-muted); font-size: 0.75rem; margin: 0;">
                    Buat & kelola booking reservasi tamu
                </p>
            </div>
        </a>
        
        <!-- Kalender Booking -->
        <a href="kalender.php" style="text-decoration: none;">
            <div class="menu-card">
                <div class="menu-icon" style="background: linear-gradient(135deg, #10b981, #06b6d4);">
                    <i data-feather="calendar-check" style="width: 24px; height: 24px; color: white;"></i>
                </div>
                <h4 style="font-size: 0.938rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem;">
                    Kalender Booking
                </h4>
                <p style="color: var(--text-muted); font-size: 0.75rem; margin: 0;">
                    Timeline view semua booking per room
                </p>
            </div>
        </a>
        
        <!-- Denah -->
        <a href="master-map.php" style="text-decoration: none;">
            <div class="menu-card">
                <div class="menu-icon" style="background: linear-gradient(135deg, #f59e0b, #ec4899);">
                    <i data-feather="map" style="width: 24px; height: 24px; color: white;"></i>
                </div>
                <h4 style="font-size: 0.938rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem;">
                    Denah
                </h4>
                <p style="color: var(--text-muted); font-size: 0.75rem; margin: 0;">
                    Bird's eye view semua gedung & kamar
                </p>
            </div>
        </a>
        
        <!-- Laporan -->
        <a href="laporan.php" style="text-decoration: none;">
            <div class="menu-card">
                <div class="menu-icon" style="background: linear-gradient(135deg, #ef4444, #f59e0b);">
                    <i data-feather="file-text" style="width: 24px; height: 24px; color: white;"></i>
                </div>
                <h4 style="font-size: 0.938rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem;">
                    Laporan
                </h4>
                <p style="color: var(--text-muted); font-size: 0.75rem; margin: 0;">
                    Report occupancy, revenue & transaksi
                </p>
            </div>
        </a>
        
        <!-- Master Data -->
        <a href="master.php" style="text-decoration: none;">
            <div class="menu-card">
                <div class="menu-icon" style="background: linear-gradient(135deg, #8b5cf6, #6366f1);">
                    <i data-feather="database" style="width: 24px; height: 24px; color: white;"></i>
                </div>
                <h4 style="font-size: 0.938rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem;">
                    Master Data
                </h4>
                <p style="color: var(--text-muted); font-size: 0.75rem; margin: 0;">
                    Kelola buildings, rooms, tipe kamar
                </p>
            </div>
        </a>
    </div>
</div>

<script>
feather.replace();
</script>

<?php include '../../includes/footer.php'; ?>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(99, 102, 241, 0.15); display: flex; align-items: center; justify-content: center;">
                <i data-feather="home" style="width: 24px; height: 24px; color: var(--primary-color);"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Total Rooms</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary);"><?php echo $totalRooms; ?></div>
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(16, 185, 129, 0.15); display: flex; align-items: center; justify-content: center;">
                <i data-feather="check-circle" style="width: 24px; height: 24px; color: #10b981;"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Available</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: #10b981;"><?php echo $available; ?></div>
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(239, 68, 68, 0.15); display: flex; align-items: center; justify-content: center;">
                <i data-feather="users" style="width: 24px; height: 24px; color: #ef4444;"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Occupied</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: #ef4444;"><?php echo $occupied; ?></div>
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(139, 92, 246, 0.15); display: flex; align-items: center; justify-content: center;">
                <i data-feather="trending-up" style="width: 24px; height: 24px; color: #8b5cf6;"></i>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Occupancy</div>
                <div style="font-size: 1.75rem; font-weight: 800; color: #8b5cf6;"><?php echo $occupancyRate; ?>%</div>
            </div>
        </div>
    </div>
</div>

<!-- Date Navigator -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="date-navigator">
        <a href="?date=<?php echo date('Y-m-d', strtotime($selectedDate . ' -1 day')); ?>&building=<?php echo $selectedBuilding; ?>" class="btn btn-secondary btn-sm">
            <i data-feather="chevron-left" style="width: 16px; height: 16px;"></i>
            Prev
        </a>
        
        <div style="text-align: center;">
            <div style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary);">
                <?php echo $viewDate->format('l, d F Y'); ?>
            </div>
            <div style="font-size: 0.875rem; color: var(--text-muted);">
                <?php echo $viewDate->format('d/m/Y') === date('d/m/Y') ? 'Today' : ''; ?>
            </div>
        </div>
        
        <a href="?date=<?php echo date('Y-m-d', strtotime($selectedDate . ' +1 day')); ?>&building=<?php echo $selectedBuilding; ?>" class="btn btn-secondary btn-sm">
            Next
            <i data-feather="chevron-right" style="width: 16px; height: 16px;"></i>
        </a>
    </div>
    
    <div style="display: flex; align-items: center; gap: 1rem; padding-top: 1rem; border-top: 1px solid var(--bg-tertiary);">
        <a href="?date=<?php echo date('Y-m-d'); ?>&building=<?php echo $selectedBuilding; ?>" class="btn btn-primary btn-sm">
            <i data-feather="calendar" style="width: 14px; height: 14px;"></i>
            Today
        </a>
        <input type="date" value="<?php echo $selectedDate; ?>" onchange="window.location.href='?date='+this.value+'&building=<?php echo $selectedBuilding; ?>'" style="padding: 0.5rem; border: 1px solid var(--bg-tertiary); border-radius: 6px; background: var(--bg-secondary); color: var(--text-primary);">
        
        <select onchange="window.location.href='?date=<?php echo $selectedDate; ?>&building='+this.value" style="padding: 0.5rem; border: 1px solid var(--bg-tertiary); border-radius: 6px; background: var(--bg-secondary); color: var(--text-primary); margin-left: auto;">
            <option value="0" <?php echo $selectedBuilding == 0 ? 'selected' : ''; ?>>All Buildings</option>
            <?php foreach ($buildings as $bld): ?>
                <option value="<?php echo $bld['id']; ?>" <?php echo $selectedBuilding == $bld['id'] ? 'selected' : ''; ?>>
                    <?php echo $bld['building_code']; ?> - <?php echo $bld['building_name']; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<!-- Room Grid by Building -->
<?php if ($selectedBuilding > 0): ?>
    <!-- Single Building View -->
    <?php foreach ($roomsByFloor as $floor => $floorRooms): ?>
    <div class="floor-section">
        <div class="floor-header">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center;">
                <span style="font-size: 1.125rem; font-weight: 800; color: white;"><?php echo $floor; ?></span>
            </div>
            <div>
                <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                    Lantai <?php echo $floor; ?>
                </h3>
                <p style="font-size: 0.813rem; color: var(--text-muted); margin: 0;">
                    <?php echo count($floorRooms); ?> rooms
                </p>
            </div>
        </div>
        
        <div class="room-grid">
            <?php foreach ($floorRooms as $room): ?>
                <div class="room-card <?php echo $room['booking_id'] ? 'occupied' : 'available'; ?>" 
                     onclick="viewRoomDetail('<?php echo $room['room_number']; ?>', <?php echo $room['id']; ?>)">
                    <div class="room-number"><?php echo $room['room_number']; ?></div>
                    <div class="room-type"><?php echo $room['type_name']; ?></div>
                    
                    <?php if ($room['booking_id']): ?>
                        <span class="room-status-badge status-occupied">Occupied</span>
                        <div class="guest-info">
                            <i data-feather="user" style="width: 12px; height: 12px;"></i>
                            <?php echo $room['guest_name']; ?>
                        </div>
                        <div style="font-size: 0.688rem; color: var(--text-muted); margin-top: 0.25rem;">
                            CO: <?php echo date('d M', strtotime($room['check_out_date'])); ?>
                        </div>
                    <?php else: ?>
                        <span class="room-status-badge status-available">Available</span>
                        <div style="margin-top: 0.5rem; font-size: 0.813rem; font-weight: 700; color: var(--primary-color);">
                            Rp <?php echo number_format($room['base_price'], 0, ',', '.'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>
<?php else: ?>
    <!-- All Buildings View -->
    <?php foreach ($roomsByBuilding as $buildingCode => $buildingRooms): ?>
        <?php $firstRoom = $buildingRooms[0]; ?>
        <div class="floor-section">
            <div class="floor-header">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: <?php echo $firstRoom['color_theme']; ?>20; display: flex; align-items: center; justify-content: center; border: 2px solid <?php echo $firstRoom['color_theme']; ?>;">
                    <i data-feather="home" style="width: 24px; height: 24px; color: <?php echo $firstRoom['color_theme']; ?>;"></i>
                </div>
                <div>
                    <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                        <?php echo $buildingCode; ?> - <?php echo $firstRoom['building_name']; ?>
                    </h3>
                    <p style="font-size: 0.813rem; color: var(--text-muted); margin: 0;">
                        <?php echo count($buildingRooms); ?> rooms
                    </p>
                </div>
            </div>
            
            <div class="room-grid">
                <?php foreach ($buildingRooms as $room): ?>
                    <div class="room-card <?php echo $room['booking_id'] ? 'occupied' : 'available'; ?>" 
                         onclick="viewRoomDetail('<?php echo $room['room_number']; ?>', <?php echo $room['id']; ?>)">
                        <div class="room-number"><?php echo $room['room_number']; ?></div>
                        <div class="room-type"><?php echo $room['type_name']; ?></div>
                        
                        <?php if ($room['booking_id']): ?>
                            <span class="room-status-badge status-occupied">Occupied</span>
                            <div class="guest-info">
                                <i data-feather="user" style="width: 12px; height: 12px;"></i>
                                <?php echo $room['guest_name']; ?>
                            </div>
                            <div style="font-size: 0.688rem; color: var(--text-muted); margin-top: 0.25rem;">
                                CO: <?php echo date('d M', strtotime($room['check_out_date'])); ?>
                            </div>
                        <?php else: ?>
                            <span class="room-status-badge status-available">Available</span>
                            <div style="margin-top: 0.5rem; font-size: 0.813rem; font-weight: 700; color: var(--primary-color);">
                                Rp <?php echo number_format($room['base_price'], 0, ',', '.'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
feather.replace();

function viewRoomDetail(roomNumber, roomId) {
    window.location.href = 'room-detail.php?id=' + roomId;
}
</script>

<?php include '../../includes/footer.php'; ?>
