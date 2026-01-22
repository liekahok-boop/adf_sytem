<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();
$pageTitle = 'Manage Rooms';

// Get all rooms with building and type info
$rooms = $db->fetchAll("
    SELECT 
        r.*,
        b.building_code,
        b.building_name,
        rt.type_name,
        rt.base_price
    FROM rooms r
    LEFT JOIN buildings b ON r.building_id = b.id
    LEFT JOIN room_types rt ON r.room_type_id = rt.id
    ORDER BY b.id, r.floor_number DESC, r.room_number
");

// Get buildings for filter
$buildings = $db->fetchAll("SELECT * FROM buildings ORDER BY id");

// Get room types
$roomTypes = $db->fetchAll("SELECT * FROM room_types ORDER BY type_name");

// Handle delete
if (isset($_GET['delete'])) {
    $roomId = intval($_GET['delete']);
    
    // Check if room has bookings
    $hasBookings = $db->fetchOne("SELECT COUNT(*) as total FROM bookings WHERE room_id = ?", [$roomId])['total'];
    
    if ($hasBookings > 0) {
        setFlash('error', 'Room tidak bisa dihapus karena memiliki booking history!');
    } else {
        $db->delete('rooms', 'id = :id', ['id' => $roomId]);
        setFlash('success', 'Room berhasil dihapus!');
    }
    
    header('Location: manage-rooms.php');
    exit;
}

include '../../includes/header.php';
?>

<style>
.table-container {
    background: var(--bg-secondary);
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--bg-tertiary);
}

.room-table {
    width: 100%;
    border-collapse: collapse;
}

.room-table thead {
    background: var(--bg-tertiary);
}

.room-table th {
    padding: 0.875rem 1rem;
    text-align: left;
    font-size: 0.813rem;
    font-weight: 700;
    color: var(--text-primary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.room-table td {
    padding: 0.875rem 1rem;
    border-bottom: 1px solid var(--bg-tertiary);
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.room-table tbody tr:hover {
    background: var(--bg-tertiary);
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.625rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-available {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
}

.status-occupied {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
}

.status-cleaning {
    background: rgba(245, 158, 11, 0.15);
    color: #f59e0b;
}

.status-maintenance {
    background: rgba(107, 114, 128, 0.15);
    color: #6b7280;
}

.status-blocked {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
}

.action-btns {
    display: flex;
    gap: 0.5rem;
}

.btn-icon {
    padding: 0.375rem 0.625rem;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
}

.btn-edit {
    background: #6366f1;
    color: white;
}

.btn-edit:hover {
    background: #4f46e5;
}

.btn-delete {
    background: #ef4444;
    color: white;
}

.btn-delete:hover {
    background: #dc2626;
}

.filter-bar {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    align-items: center;
}

.stats-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-box {
    background: var(--bg-secondary);
    border: 2px solid var(--bg-tertiary);
    border-radius: 10px;
    padding: 1rem;
    text-align: center;
}

.stat-number {
    font-size: 1.75rem;
    font-weight: 800;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.75rem;
    color: var(--text-muted);
    font-weight: 600;
}
</style>

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.25rem;">
            🏠 Manage Rooms
        </h2>
        <p style="color: var(--text-muted); font-size: 0.813rem;">Database semua kamar hotel</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="master.php" class="btn btn-secondary">
            <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i>
            Back
        </a>
        <button onclick="showBulkSetup()" class="btn btn-info">
            <i data-feather="settings" style="width: 14px; height: 14px;"></i>
            Setup Total Rooms
        </button>
        <a href="add-room.php" class="btn btn-primary">
            <i data-feather="plus" style="width: 14px; height: 14px;"></i>
            Add Room
        </a>
    </div>
</div>

<!-- Bulk Room Setup Modal -->
<div id="bulkSetupModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: var(--bg-primary); border-radius: 16px; padding: 2rem; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-primary); margin: 0;">
                ⚙️ Setup Total Rooms per Building
            </h3>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted);">×</button>
        </div>
        
        <form method="POST" action="bulk-setup-rooms.php">
            <?php foreach ($buildings as $building): ?>
                <?php 
                $currentRoomCount = $db->fetchOne("SELECT COUNT(*) as total FROM rooms WHERE building_id = ?", [$building['id']])['total'];
                ?>
                <div style="background: var(--bg-secondary); border-radius: 10px; padding: 1.25rem; margin-bottom: 1rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.75rem;">
                        <div style="width: 50px; height: 50px; border-radius: 10px; background: <?php echo $building['color_theme']; ?>20; display: flex; align-items: center; justify-content: center;">
                            <i data-feather="home" style="width: 24px; height: 24px; color: <?php echo $building['color_theme']; ?>;"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 700; font-size: 1rem; color: var(--text-primary);">
                                <?php echo $building['building_code']; ?>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">
                                <?php echo $building['building_name']; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Current Rooms</label>
                            <div style="font-size: 1.5rem; font-weight: 800; color: #6366f1;"><?php echo $currentRoomCount; ?></div>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Target Total *</label>
                            <input type="number" name="building_<?php echo $building['id']; ?>" 
                                   class="form-control" 
                                   value="<?php echo $currentRoomCount; ?>" 
                                   min="<?php echo $currentRoomCount; ?>" 
                                   max="100"
                                   style="font-size: 1rem; font-weight: 700;">
                        </div>
                    </div>
                    <small style="display: block; margin-top: 0.5rem; font-size: 0.688rem; color: var(--text-muted);">
                        ℹ️ Sistem akan generate room otomatis sesuai target
                    </small>
                </div>
            <?php endforeach; ?>
            
            <div style="display: flex; gap: 0.75rem; padding-top: 1rem; border-top: 2px solid var(--bg-tertiary);">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i data-feather="check" style="width: 14px; height: 14px;"></i>
                    Generate Rooms
                </button>
                <button type="button" onclick="closeModal()" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Stats Summary -->
<div class="stats-summary">
    <?php
    $totalRooms = count($rooms);
    $availableCount = count(array_filter($rooms, fn($r) => $r['status'] === 'available'));
    $occupiedCount = count(array_filter($rooms, fn($r) => $r['status'] === 'occupied'));
    $maintenanceCount = count(array_filter($rooms, fn($r) => $r['status'] === 'maintenance'));
    ?>
    
    <div class="stat-box">
        <div class="stat-number" style="color: #6366f1;"><?php echo $totalRooms; ?></div>
        <div class="stat-label">Total Rooms</div>
    </div>
    
    <div class="stat-box">
        <div class="stat-number" style="color: #10b981;"><?php echo $availableCount; ?></div>
        <div class="stat-label">Available</div>
    </div>
    
    <div class="stat-box">
        <div class="stat-number" style="color: #ef4444;"><?php echo $occupiedCount; ?></div>
        <div class="stat-label">Occupied</div>
    </div>
    
    <div class="stat-box">
        <div class="stat-number" style="color: #f59e0b;"><?php echo $maintenanceCount; ?></div>
        <div class="stat-label">Maintenance</div>
    </div>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
    <div style="flex: 1;">
        <input type="text" id="searchInput" class="form-control" placeholder="🔍 Cari room number atau type..." style="max-width: 400px;">
    </div>
    <select id="buildingFilter" class="form-control" style="max-width: 200px;">
        <option value="">Semua Building</option>
        <?php foreach ($buildings as $building): ?>
            <option value="<?php echo $building['id']; ?>"><?php echo $building['building_code']; ?></option>
        <?php endforeach; ?>
    </select>
    <select id="statusFilter" class="form-control" style="max-width: 200px;">
        <option value="">Semua Status</option>
        <option value="available">Available</option>
        <option value="occupied">Occupied</option>
        <option value="cleaning">Cleaning</option>
        <option value="maintenance">Maintenance</option>
        <option value="blocked">Blocked</option>
    </select>
</div>

<!-- Rooms Table -->
<div class="table-container">
    <table class="room-table">
        <thead>
            <tr>
                <th style="width: 60px;">ID</th>
                <th>Building</th>
                <th>Room Number</th>
                <th>Room Type</th>
                <th>Floor</th>
                <th>Status</th>
                <th>Price</th>
                <th style="width: 150px; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody id="roomTableBody">
            <?php foreach ($rooms as $room): ?>
                <tr data-building="<?php echo $room['building_id']; ?>" data-status="<?php echo $room['status']; ?>" data-search="<?php echo strtolower($room['room_number'] . ' ' . $room['type_name'] . ' ' . $room['building_code']); ?>">
                    <td style="font-weight: 700; color: var(--text-primary);"><?php echo $room['id']; ?></td>
                    <td>
                        <div style="font-weight: 600; color: var(--text-primary);"><?php echo $room['building_code']; ?></div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo $room['building_name']; ?></div>
                    </td>
                    <td style="font-weight: 700; color: var(--text-primary); font-size: 0.938rem;">
                        <?php echo $room['room_number']; ?>
                    </td>
                    <td><?php echo $room['type_name']; ?></td>
                    <td>
                        <span style="font-weight: 600;">Lantai <?php echo $room['floor_number']; ?></span>
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo $room['status']; ?>">
                            <?php echo ucfirst($room['status']); ?>
                        </span>
                    </td>
                    <td style="font-weight: 600; color: var(--text-primary);">
                        Rp <?php echo number_format($room['base_price'], 0, ',', '.'); ?>
                    </td>
                    <td>
                        <div class="action-btns" style="justify-content: center;">
                            <a href="edit-room.php?id=<?php echo $room['id']; ?>" class="btn-icon btn-edit">
                                <i data-feather="edit-2" style="width: 14px; height: 14px;"></i>
                                Edit
                            </a>
                            <button onclick="deleteRoom(<?php echo $room['id']; ?>, '<?php echo $room['room_number']; ?>')" class="btn-icon btn-delete">
                                <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
feather.replace();

// Search filter
document.getElementById('searchInput').addEventListener('input', filterTable);
document.getElementById('buildingFilter').addEventListener('change', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);

function filterTable() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const buildingFilter = document.getElementById('buildingFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    const rows = document.querySelectorAll('#roomTableBody tr');
    
    rows.forEach(row => {
        const searchText = row.getAttribute('data-search');
        const building = row.getAttribute('data-building');
        const status = row.getAttribute('data-status');
        
        const matchSearch = searchText.includes(searchTerm);
        const matchBuilding = !buildingFilter || building === buildingFilter;
        const matchStatus = !statusFilter || status === statusFilter;
        
        if (matchSearch && matchBuilding && matchStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function deleteRoom(id, roomNumber) {
    if (confirm(`Yakin ingin menghapus room ${roomNumber}?\n\nRoom yang memiliki booking history tidak bisa dihapus.`)) {
        window.location.href = 'manage-rooms.php?delete=' + id;
    }
}

function showBulkSetup() {
    document.getElementById('bulkSetupModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('bulkSetupModal').style.display = 'none';
}
</script>

<?php include '../../includes/footer.php'; ?>
