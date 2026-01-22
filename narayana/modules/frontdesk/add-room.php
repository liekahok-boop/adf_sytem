<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();
$pageTitle = 'Add Room';

// Get buildings
$buildings = $db->fetchAll("SELECT * FROM buildings WHERE is_active = 1 ORDER BY id");

// Get room types
$roomTypes = $db->fetchAll("SELECT * FROM room_types ORDER BY type_name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $buildingId = intval($_POST['building_id']);
    $roomNumber = trim($_POST['room_number']);
    $roomTypeId = intval($_POST['room_type_id']);
    $floorNumber = intval($_POST['floor_number']);
    $status = $_POST['status'];
    $notes = trim($_POST['notes']);
    
    // Check if room number already exists in this building
    $existing = $db->fetchOne("
        SELECT id FROM rooms 
        WHERE building_id = ? AND room_number = ?
    ", [$buildingId, $roomNumber]);
    
    if ($existing) {
        setFlash('error', 'Room number sudah ada di building ini!');
    } else {
        try {
            $db->insert('rooms', [
                'building_id' => $buildingId,
                'room_number' => $roomNumber,
                'room_type_id' => $roomTypeId,
                'floor_number' => $floorNumber,
                'status' => $status,
                'notes' => $notes
            ]);
            
            setFlash('success', 'Room berhasil ditambahkan!');
            header('Location: manage-rooms.php');
            exit;
        } catch (Exception $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
    }
}

include '../../includes/header.php';
?>

<style>
.preview-box {
    background: var(--bg-secondary);
    border: 2px solid var(--bg-tertiary);
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
}

.room-preview {
    width: 150px;
    height: 100px;
    margin: 0 auto;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border: 3px solid white;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    background: #10b981;
}
</style>

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.25rem;">
            ➕ Add New Room
        </h2>
        <p style="color: var(--text-muted); font-size: 0.813rem;">Tambah kamar baru ke building</p>
    </div>
    <a href="manage-rooms.php" class="btn btn-secondary">
        <i data-feather="arrow-left" style="width: 16px; height: 16px;"></i>
        Back to List
    </a>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    
    <!-- Add Form -->
    <div class="card">
        <div style="padding: 1rem; border-bottom: 1px solid var(--bg-tertiary);">
            <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                Room Information
            </h3>
        </div>
        
        <form method="POST" style="padding: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Building *</label>
                <select name="building_id" class="form-control" required id="buildingSelect">
                    <option value="">-- Pilih Building --</option>
                    <?php foreach ($buildings as $building): ?>
                        <option value="<?php echo $building['id']; ?>" data-code="<?php echo $building['building_code']; ?>">
                            <?php echo $building['building_code']; ?> - <?php echo $building['building_name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Room Number *</label>
                <input type="text" name="room_number" class="form-control" 
                       required maxlength="20" id="roomNumber"
                       placeholder="Contoh: 101, A-201, Suite-A">
                <small style="color: var(--text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                    Nomor kamar harus unik per building
                </small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Room Type *</label>
                <select name="room_type_id" class="form-control" required id="roomTypeSelect">
                    <option value="">-- Pilih Tipe --</option>
                    <?php foreach ($roomTypes as $type): ?>
                        <option value="<?php echo $type['id']; ?>" 
                                data-name="<?php echo $type['type_name']; ?>"
                                data-price="<?php echo $type['base_price']; ?>">
                            <?php echo $type['type_name']; ?> - Rp <?php echo number_format($type['base_price'], 0, ',', '.'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Floor Number *</label>
                <input type="number" name="floor_number" class="form-control" 
                       required min="1" max="10" value="1" id="floorNumber">
            </div>
            
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-control" id="statusSelect">
                    <option value="available">✅ Available</option>
                    <option value="cleaning">🧹 Cleaning</option>
                    <option value="maintenance">🔧 Maintenance</option>
                    <option value="blocked">🚫 Blocked</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3" 
                          placeholder="Catatan khusus untuk kamar ini..."></textarea>
            </div>
            
            <div style="display: flex; gap: 0.75rem; padding-top: 1rem; border-top: 1px solid var(--bg-tertiary);">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i data-feather="save" style="width: 16px; height: 16px;"></i>
                    Add Room
                </button>
                <a href="manage-rooms.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    
    <!-- Preview -->
    <div>
        <div class="card" style="margin-bottom: 1.5rem;">
            <div style="padding: 1rem; border-bottom: 1px solid var(--bg-tertiary);">
                <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                    Preview
                </h3>
            </div>
            <div class="preview-box">
                <div style="margin-bottom: 1rem;">
                    <div style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.5rem;">Building</div>
                    <div style="font-size: 1.125rem; font-weight: 700; color: var(--text-primary);" id="previewBuilding">
                        --
                    </div>
                </div>
                
                <div class="room-preview" id="roomPreview">
                    <div style="font-size: 1.5rem; font-weight: 800; color: white;" id="previewNumber">
                        ???
                    </div>
                    <div style="font-size: 0.875rem; color: white; opacity: 0.9; margin-top: 0.5rem;" id="previewType">
                        Room Type
                    </div>
                </div>
                
                <div style="margin-top: 1.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; text-align: left;">
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Floor</div>
                        <div style="font-size: 1rem; font-weight: 700; color: var(--text-primary);" id="previewFloor">1</div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Status</div>
                        <div style="font-size: 1rem; font-weight: 700; color: #10b981;" id="previewStatus">Available</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Info Card -->
        <div class="card">
            <div style="padding: 1rem;">
                <h4 style="font-size: 0.938rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.75rem;">
                    <i data-feather="info" style="width: 16px; height: 16px;"></i>
                    Tips
                </h4>
                <ul style="font-size: 0.813rem; color: var(--text-secondary); line-height: 1.6; margin: 0; padding-left: 1.25rem;">
                    <li>Room number harus unik dalam 1 gedung</li>
                    <li>Format bebas: 101, A-201, Suite-A, dll</li>
                    <li>Floor number untuk urutan lantai di denah</li>
                    <li>Status Available untuk room yang siap dihuni</li>
                    <li>Maintenance untuk room yang sedang perbaikan</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
feather.replace();

// Live preview
const statusColors = {
    'available': '#10b981',
    'occupied': '#ef4444',
    'cleaning': '#f59e0b',
    'maintenance': '#6b7280',
    'blocked': '#6b7280'
};

document.getElementById('buildingSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const buildingCode = selectedOption.getAttribute('data-code');
    document.getElementById('previewBuilding').textContent = buildingCode || '--';
});

document.getElementById('roomNumber').addEventListener('input', function() {
    document.getElementById('previewNumber').textContent = this.value || '???';
});

document.getElementById('roomTypeSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const typeName = selectedOption.getAttribute('data-name');
    document.getElementById('previewType').textContent = typeName || 'Room Type';
});

document.getElementById('floorNumber').addEventListener('input', function() {
    document.getElementById('previewFloor').textContent = this.value || '1';
});

document.getElementById('statusSelect').addEventListener('change', function() {
    const status = this.value;
    document.getElementById('roomPreview').style.background = statusColors[status];
    document.getElementById('previewStatus').textContent = this.options[this.selectedIndex].text;
    document.getElementById('previewStatus').style.color = statusColors[status];
});
</script>

<?php include '../../includes/footer.php'; ?>
