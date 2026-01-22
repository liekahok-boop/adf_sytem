<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();
$pageTitle = 'Database Settings';

// Get counts
$roomCount = $db->fetchOne("SELECT COUNT(*) as total FROM rooms")['total'];
$roomTypeCount = $db->fetchOne("SELECT COUNT(*) as total FROM room_types")['total'];

include '../../includes/header.php';
?>

<style>
.db-menu {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.db-card {
    flex: 1;
    background: var(--bg-secondary);
    border: 2px solid var(--bg-tertiary);
    border-radius: 10px;
    padding: 1.25rem;
    transition: all 0.3s ease;
    cursor: pointer;
}

.db-card:hover {
    border-color: var(--primary-color);
    transform: translateY(-2px);
}

.db-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.75rem;
}
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.25rem;">
            ⚙️ Database Settings
        </h2>
        <p style="color: var(--text-muted); font-size: 0.813rem;">Kelola data room & tipe</p>
    </div>
    <a href="index.php" class="btn btn-secondary">
        <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i>
        Back
    </a>
</div>

<div class="db-menu">
    
    <!-- Buildings -->
    
    <!-- Rooms -->
    <a href="manage-rooms.php" style="text-decoration: none;">
        <div class="db-card">
            <div class="db-icon" style="background: linear-gradient(135deg, #10b981, #06b6d4);">
                <i data-feather="box" style="width: 24px; height: 24px; color: white;"></i>
            </div>
            <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem;">
                Rooms Database
            </h3>
            <p style="color: var(--text-muted); font-size: 0.75rem; margin-bottom: 0.75rem;">
                Kelola semua kamar hotel
            </p>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.625rem; background: var(--bg-tertiary); border-radius: 6px;">
                <span style="font-size: 0.688rem; color: var(--text-muted);">Total</span>
                <span style="font-size: 1.25rem; font-weight: 800; color: #10b981;"><?php echo $roomCount; ?></span>
            </div>
        </div>
    </a>
    
    <!-- Room Types -->
    <a href="manage-room-types.php" style="text-decoration: none;">
        <div class="db-card">
            <div class="db-icon" style="background: linear-gradient(135deg, #f59e0b, #ec4899);">
                <i data-feather="layers" style="width: 24px; height: 24px; color: white;"></i>
            </div>
            <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem;">
                Room Types
            </h3>
            <p style="color: var(--text-muted); font-size: 0.75rem; margin-bottom: 0.75rem;">
                Kelola tipe & harga kamar
            </p>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.625rem; background: var(--bg-tertiary); border-radius: 6px;">
                <span style="font-size: 0.688rem; color: var(--text-muted);">Total</span>
                <span style="font-size: 1.25rem; font-weight: 800; color: #f59e0b;"><?php echo $roomTypeCount; ?></span>
            </div>
        </div>
    </a>
    
</div>

<script>
feather.replace();
</script>

<?php include '../../includes/footer.php'; ?>
