<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();
$pageTitle = 'Kalender Booking';

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
    background: linear-gradient(135deg, #10b981, #06b6d4);
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
            📆 Kalender Booking
        </h2>
        <p style="color: var(--text-muted); font-size: 0.875rem;">Timeline view semua booking per room</p>
    </div>
    <a href="index.php" class="btn btn-secondary">
        <i data-feather="arrow-left" style="width: 16px; height: 16px;"></i>
        Back to Dashboard
    </a>
</div>

<div class="card coming-soon">
    <div class="coming-soon-icon">
        <i data-feather="calendar-check" style="width: 60px; height: 60px; color: white;"></i>
    </div>
    <h3 style="font-size: 2rem; font-weight: 800; color: var(--text-primary); margin-bottom: 1rem;">
        Coming Soon
    </h3>
    <p style="color: var(--text-muted); font-size: 1rem; max-width: 500px; margin: 0 auto;">
        Kalender Booking sedang dalam pengembangan. Fitur ini akan menampilkan timeline view seperti Cloudbed dengan room sebagai baris dan tanggal sebagai kolom, menunjukkan booking spans untuk setiap kamar.
    </p>
</div>

<script>
feather.replace();
</script>

<?php include '../../includes/footer.php'; ?>
