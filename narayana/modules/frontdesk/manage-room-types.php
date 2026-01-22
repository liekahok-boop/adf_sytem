<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();
$pageTitle = 'Manage Room Types';

// Get all room types with room count
$roomTypes = $db->fetchAll("
    SELECT 
        rt.*,
        COUNT(r.id) as room_count
    FROM room_types rt
    LEFT JOIN rooms r ON rt.id = r.room_type_id
    GROUP BY rt.id
    ORDER BY rt.type_name
");

// Handle delete
if (isset($_GET['delete'])) {
    $typeId = intval($_GET['delete']);
    
    // Check if type is used by any room
    $roomCount = $db->fetchOne("SELECT COUNT(*) as total FROM rooms WHERE room_type_id = ?", [$typeId])['total'];
    
    if ($roomCount > 0) {
        setFlash('error', 'Room type tidak bisa dihapus karena masih digunakan oleh ' . $roomCount . ' room!');
    } else {
        $db->delete('room_types', 'id = :id', ['id' => $typeId]);
        setFlash('success', 'Room type berhasil dihapus!');
    }
    
    header('Location: manage-room-types.php');
    exit;
}

include '../../includes/header.php';
?>

<style>
.type-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.type-card {
    background: var(--bg-secondary);
    border: 2px solid var(--bg-tertiary);
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
    position: relative;
}

.type-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    border-color: var(--primary-color);
}

.type-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.type-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.type-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--bg-tertiary);
}

.stat-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    font-size: 0.875rem;
}

.stat-label {
    color: var(--text-muted);
}

.stat-value {
    font-weight: 700;
    color: var(--text-primary);
}
</style>

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.25rem;">
            🏷️ Manage Room Types
        </h2>
        <p style="color: var(--text-muted); font-size: 0.813rem;">Kelola tipe kamar & harga</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="master.php" class="btn btn-secondary">
            <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i>
            Back
        </a>
        <a href="add-room-type.php" class="btn btn-primary">
            <i data-feather="plus" style="width: 14px; height: 14px;"></i>
            Add Type
        </a>
    </div>
</div>

<!-- Stats Summary -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="card" style="padding: 1rem; text-align: center;">
        <div style="font-size: 1.75rem; font-weight: 800; color: #6366f1; margin-bottom: 0.25rem;">
            <?php echo count($roomTypes); ?>
        </div>
        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Total Types</div>
    </div>
    
    <div class="card" style="padding: 1rem; text-align: center;">
        <div style="font-size: 1.75rem; font-weight: 800; color: #10b981; margin-bottom: 0.25rem;">
            <?php echo array_sum(array_column($roomTypes, 'room_count')); ?>
        </div>
        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Total Rooms Using</div>
    </div>
    
    <div class="card" style="padding: 1rem; text-align: center;">
        <div style="font-size: 1.75rem; font-weight: 800; color: #f59e0b; margin-bottom: 0.25rem;">
            Rp <?php echo !empty($roomTypes) ? number_format(min(array_column($roomTypes, 'base_price')), 0, ',', '.') : '0'; ?>
        </div>
        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Lowest Price</div>
    </div>
    
    <div class="card" style="padding: 1rem; text-align: center;">
        <div style="font-size: 1.75rem; font-weight: 800; color: #ef4444; margin-bottom: 0.25rem;">
            Rp <?php echo !empty($roomTypes) ? number_format(max(array_column($roomTypes, 'base_price')), 0, ',', '.') : '0'; ?>
        </div>
        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Highest Price</div>
    </div>
</div>

<?php if (empty($roomTypes)): ?>
    <div class="card" style="padding: 3rem; text-align: center;">
        <div style="font-size: 4rem; margin-bottom: 1rem;">🏷️</div>
        <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">
            Belum Ada Room Type
        </h3>
        <p style="color: var(--text-muted); margin-bottom: 1.5rem;">
            Mulai dengan menambahkan tipe kamar pertama Anda
        </p>
        <a href="add-room-type.php" class="btn btn-primary">
            <i data-feather="plus" style="width: 16px; height: 16px;"></i>
            Add First Room Type
        </a>
    </div>
<?php else: ?>
    <!-- Room Types Grid -->
    <div class="type-grid">
        <?php foreach ($roomTypes as $type): ?>
            <div class="type-card">
                <div class="type-header">
                    <div class="type-icon" style="background: <?php echo $type['color_code']; ?>20;">
                        <i data-feather="layers" style="width: 30px; height: 30px; color: <?php echo $type['color_code']; ?>;"></i>
                    </div>
                    <div style="flex: 1;">
                        <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem;">
                            <?php echo htmlspecialchars($type['type_name']); ?>
                        </h3>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                            <?php echo $type['room_count']; ?> room(s) menggunakan
                        </div>
                    </div>
                </div>
                
                <div style="background: var(--bg-tertiary); border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                    <div class="stat-row">
                        <span class="stat-label">Base Price</span>
                        <span class="stat-value" style="color: #10b981;">
                            Rp <?php echo number_format($type['base_price'], 0, ',', '.'); ?>
                        </span>
                    </div>
                    
                    <?php if ($type['description']): ?>
                        <div style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--bg-secondary);">
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">Description</div>
                            <div style="font-size: 0.813rem; color: var(--text-secondary);">
                                <?php echo nl2br(htmlspecialchars($type['description'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="type-actions">
                    <a href="edit-room-type.php?id=<?php echo $type['id']; ?>" class="btn btn-primary" style="flex: 1;">
                        <i data-feather="edit-2" style="width: 14px; height: 14px;"></i>
                        Edit
                    </a>
                    <button onclick="deleteType(<?php echo $type['id']; ?>, '<?php echo addslashes($type['type_name']); ?>', <?php echo $type['room_count']; ?>)" 
                            class="btn btn-danger">
                        <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                        Delete
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
feather.replace();

function deleteType(id, typeName, roomCount) {
    if (roomCount > 0) {
        alert(`Room type "${typeName}" tidak bisa dihapus karena masih digunakan oleh ${roomCount} room!`);
        return;
    }
    
    if (confirm(`Yakin ingin menghapus room type "${typeName}"?`)) {
        window.location.href = 'manage-room-types.php?delete=' + id;
    }
}
</script>

<?php include '../../includes/footer.php'; ?>
