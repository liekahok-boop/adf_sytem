<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();
$pageTitle = 'Edit Room';

$roomId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get room data
$room = $db->fetchOne("
    SELECT r.*, rt.type_name, b.building_code, b.building_name
    FROM rooms r
    LEFT JOIN room_types rt ON r.room_type_id = rt.id
    LEFT JOIN buildings b ON r.building_id = b.id
    WHERE r.id = ?
", [$roomId]);

if (!$room) {
    header('Location: master-map.php');
    exit;
}

// Get all room types
$roomTypes = $db->fetchAll("SELECT * FROM room_types ORDER BY type_name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomNumber = trim($_POST['room_number']);
    $roomTypeId = intval($_POST['room_type_id']);
    $floorNumber = intval($_POST['floor_number']);
    $status = $_POST['status'];
    $notes = trim($_POST['notes']);
    
    try {
        $db->update('rooms', [
            'room_number' => $roomNumber,
            'room_type_id' => $roomTypeId,
            'floor_number' => $floorNumber,
            'status' => $status,
            'notes' => $notes
        ], 'id = :id', ['id' => $roomId]);
        
        setFlash('success', 'Room berhasil diupdate!');
        header('Location: master-map.php');
        exit;
    } catch (Exception $e) {
        setFlash('error', 'Error: ' . $e->getMessage());
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
}
</style>

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div>
        <h2 style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.5rem;">
            ✏️ Edit Room
        </h2>
        <p style="color: var(--text-muted); font-size: 0.875rem;">
            <?php echo $room['building_code']; ?> - <?php echo $room['building_name']; ?>
        </p>
    </div>
    <a href="master-map.php" class="btn btn-secondary">
        <i data-feather="arrow-left" style="width: 16px; height: 16px;"></i>
        Back to Map
    </a>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    
    <!-- Edit Form -->
    <div class="card">
        <div style="padding: 1rem; border-bottom: 1px solid var(--bg-tertiary);">
            <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                Room Information
            </h3>
        </div>
        
        <form method="POST" style="padding: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Room Number *</label>
                <input type="text" name="room_number" class="form-control" 
                       value="<?php echo htmlspecialchars($room['room_number']); ?>" 
                       required maxlength="20"
                       placeholder="Contoh: 101, A-201, Suite-A">
                <small style="color: var(--text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                    Nomor kamar unik per gedung
                </small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Room Type *</label>
                <select name="room_type_id" class="form-control" required>
                    <?php foreach ($roomTypes as $type): ?>
                        <option value="<?php echo $type['id']; ?>" 
                                <?php echo $room['room_type_id'] == $type['id'] ? 'selected' : ''; ?>>
                            <?php echo $type['type_name']; ?> - Rp <?php echo number_format($type['base_price'], 0, ',', '.'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Floor Number *</label>
                <input type="number" name="floor_number" class="form-control" 
                       value="<?php echo $room['floor_number']; ?>" 
                       required min="1" max="10">
            </div>
            
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="available" <?php echo $room['status'] == 'available' ? 'selected' : ''; ?>>
                        ✅ Available
                    </option>
                    <option value="occupied" <?php echo $room['status'] == 'occupied' ? 'selected' : ''; ?>>
                        🔴 Occupied
                    </option>
                    <option value="cleaning" <?php echo $room['status'] == 'cleaning' ? 'selected' : ''; ?>>
                        🧹 Cleaning
                    </option>
                    <option value="maintenance" <?php echo $room['status'] == 'maintenance' ? 'selected' : ''; ?>>
                        🔧 Maintenance
                    </option>
                    <option value="blocked" <?php echo $room['status'] == 'blocked' ? 'selected' : ''; ?>>
                        🚫 Blocked
                    </option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3" 
                          placeholder="Catatan khusus untuk kamar ini..."><?php echo htmlspecialchars($room['notes']); ?></textarea>
            </div>
            
            <div style="display: flex; gap: 0.75rem; padding-top: 1rem; border-top: 1px solid var(--bg-tertiary);">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i data-feather="save" style="width: 16px; height: 16px;"></i>
                    Save Changes
                </button>
                <a href="master-map.php" class="btn btn-secondary">Cancel</a>
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
                <div class="room-preview" id="roomPreview">
                    <div style="font-size: 1.5rem; font-weight: 800; color: white;" id="previewNumber">
                        <?php echo $room['room_number']; ?>
                    </div>
                    <div style="font-size: 0.875rem; color: white; opacity: 0.9; margin-top: 0.5rem;" id="previewType">
                        <?php echo $room['type_name']; ?>
                    </div>
                </div>
                <div style="margin-top: 1rem; font-size: 0.875rem; color: var(--text-muted);">
                    Status: <span id="previewStatus" style="font-weight: 600;">
                        <?php echo ucfirst($room['status']); ?>
                    </span>
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
                    <li>Status akan update otomatis saat ada booking</li>
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

document.querySelector('input[name="room_number"]').addEventListener('input', function() {
    document.getElementById('previewNumber').textContent = this.value || '???';
});

document.querySelector('select[name="room_type_id"]').addEventListener('change', function() {
    const selectedText = this.options[this.selectedIndex].text.split(' - ')[0];
    document.getElementById('previewType').textContent = selectedText;
});

document.querySelector('select[name="status"]').addEventListener('change', function() {
    const status = this.value;
    document.getElementById('roomPreview').style.background = statusColors[status];
    document.getElementById('previewStatus').textContent = this.options[this.selectedIndex].text;
});

// Initial preview color
document.getElementById('roomPreview').style.background = statusColors['<?php echo $room['status']; ?>'];
</script>

<?php include '../../includes/footer.php'; ?>
