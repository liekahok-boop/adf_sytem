<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();
$pageTitle = 'Laporan';

include '../../includes/header.php';
?>

<style>
.coming-soon {
    text-align: center;
    padding: 4rem 2rem;
}

.coming-soon-icon {
    width: 120px;
    height: 120px;
    margin: 0 auto 2rem;
    border-radius: 50%;
    background: linear-gradient(135deg, #ef4444, #f59e0b);
    display: flex;
    align-items: center;
    justify-content: center;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.5rem;">
            📊 Laporan
        </h2>
        <p style="color: var(--text-muted); font-size: 0.875rem;">Report occupancy, revenue & transaksi</p>
    </div>
    <a href="index.php" class="btn btn-secondary">
        <i data-feather="arrow-left" style="width: 16px; height: 16px;"></i>
        Back to Dashboard
    </a>
</div>

<div class="card coming-soon">
    <div class="coming-soon-icon">
        <i data-feather="file-text" style="width: 60px; height: 60px; color: white;"></i>
    </div>
    <h3 style="font-size: 2rem; font-weight: 800; color: var(--text-primary); margin-bottom: 1rem;">
        Coming Soon
    </h3>
    <p style="color: var(--text-muted); font-size: 1rem; max-width: 500px; margin: 0 auto;">
        Halaman Laporan sedang dalam pengembangan. Fitur ini akan menampilkan laporan occupancy rate, revenue harian/bulanan/tahunan, statistik check-in/check-out, dan analisis performa per building dan room type.
    </p>
</div>

<script>
feather.replace();
</script>

<?php include '../../includes/footer.php'; ?>
