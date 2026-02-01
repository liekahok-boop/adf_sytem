<?php
/**
 * FRONT DESK - RESERVASI MANAGEMENT
 * Booking Management dengan Direct/OTA + Fee Calculation
 */

define('APP_ACCESS', true);
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// ============================================
// SECURITY & AUTHENTICATION
// ============================================
$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();

if (!$auth->hasPermission('frontdesk')) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$pageTitle = 'Reservasi Management - Direct/OTA Bookings';

// ============================================
// OTA CONFIGURATION (Default if not in DB)
// ============================================
$otaProviders = [
    'walk_in' => ['name' => 'Walk-in', 'fee' => 0, 'icon' => '🚶'],
    'phone' => ['name' => 'Phone Booking', 'fee' => 0, 'icon' => '📞'],
    'online' => ['name' => 'Direct Online', 'fee' => 0, 'icon' => '💻'],
    'agoda' => ['name' => 'Agoda', 'fee' => 15, 'icon' => '🏨'],
    'booking' => ['name' => 'Booking.com', 'fee' => 12, 'icon' => '📱'],
    'tiket' => ['name' => 'Tiket.com', 'fee' => 10, 'icon' => '✈️'],
    'airbnb' => ['name' => 'Airbnb', 'fee' => 3, 'icon' => '🏠'],
    'ota' => ['name' => 'OTA Lainnya', 'fee' => 10, 'icon' => '🌐'],
];

// ============================================
// GET BOOKINGS LIST
// ============================================
try {
    $status_filter = $_GET['status'] ?? 'all';
    
    $query = "
        SELECT 
            b.id, b.booking_code, b.check_in_date, b.check_out_date,
            b.room_price, b.total_price, b.final_price, b.discount,
            b.status, b.payment_status, b.booking_source, b.total_nights,
            b.paid_amount, b.special_request,
            g.guest_name, g.phone, g.email,
            r.room_number,
            rt.type_name
        FROM bookings b
        JOIN guests g ON b.guest_id = g.id
        JOIN rooms r ON b.room_id = r.id
        JOIN room_types rt ON r.room_type_id = rt.id
        WHERE 1=1
    ";
    
    $params = [];
    
    if ($status_filter !== 'all') {
        $query .= " AND b.status = ?";
        $params[] = $status_filter;
    }
    
    $query .= " ORDER BY 
        CASE 
            WHEN b.status = 'confirmed' THEN 1
            WHEN b.status = 'checked_in' THEN 2
            WHEN b.status = 'pending' THEN 3
            ELSE 4
        END,
        b.check_in_date ASC
    LIMIT 50";
    
    $bookings = $db->fetchAll($query, $params);
    
} catch (Exception $e) {
    error_log("Reservasi List Error: " . $e->getMessage());
    $bookings = [];
}

// ============================================
// CALCULATE OTA FEE & NET INCOME
// ============================================
function calculateNetIncome($roomPrice, $otaProvider, $otaProviders) {
    $feePercent = $otaProviders[$otaProvider]['fee'] ?? 0;
    $feeAmount = ($roomPrice * $feePercent) / 100;
    $netIncome = $roomPrice - $feeAmount;
    
    return [
        'gross' => $roomPrice,
        'fee_percent' => $feePercent,
        'fee_amount' => $feeAmount,
        'net' => $netIncome
    ];
}

include '../../includes/header.php';
?>

<style>
/* ============================================
   RESERVASI PAGE - PREMIUM DESIGN
   ============================================ */

:root {
    --glass-blur: 30px;
}

.reservasi-container {
    max-width: 1600px;
    margin: 0 auto;
    padding: 1.5rem 1rem;
}

/* Header */
.reservasi-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    gap: 1rem;
}

.reservasi-header h1 {
    font-size: 1.6rem;
    font-weight: 950;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0;
}

.header-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-primary {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
    font-size: 0.85rem;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(99, 102, 241, 0.3);
}

.btn-secondary {
    background: linear-gradient(135deg, #10b981, #34d399);
    color: white;
}

/* Filter Section */
.filter-section {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(30px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 0.75rem;
}
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.filter-group label {
    font-weight: 700;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-primary);
}

.filter-group select,
.filter-group input {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    padding: 0.75rem 1rem;
    color: var(--text-primary);
    font-size: 0.95rem;
}

.filter-group select option {
    background: var(--bg-secondary);
    color: var(--text-primary);
}

/* Bookings Table */
.bookings-table-wrapper {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(30px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 12px;
    padding: 1rem;
    overflow-x: auto;
}

.bookings-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8rem;
}

.bookings-table thead {
    border-bottom: 1px solid rgba(99, 102, 241, 0.3);
}

.bookings-table th {
    padding: 0.6rem;
    text-align: left;
    font-weight: 700;
    color: var(--text-primary);
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.bookings-table td {
    padding: 0.6rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    color: var(--text-secondary);
}

.bookings-table tbody tr {
    transition: all 0.3s ease;
}

.bookings-table tbody tr:hover {
    background: rgba(99, 102, 241, 0.08);
}

/* Badges */
.badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.85rem;
    text-transform: uppercase;
}

.badge-status {
    padding: 0.45rem 0.9rem;
}

.badge-confirmed {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
}

.badge-pending {
    background: rgba(245, 158, 11, 0.2);
    color: #f59e0b;
}

.badge-checked-in {
    background: rgba(59, 130, 246, 0.2);
    color: #3b82f6;
}

.badge-checked-out {
    background: rgba(156, 163, 175, 0.2);
    color: #9ca3af;
}

.badge-cancelled {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
}

.badge-payment-paid {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
}

.badge-payment-partial {
    background: rgba(245, 158, 11, 0.2);
    color: #f59e0b;
}

.badge-payment-unpaid {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
}

/* OTA Badge */
.ota-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 10px;
    background: rgba(139, 92, 246, 0.2);
    color: #8b5cf6;
    font-weight: 700;
    font-size: 0.9rem;
}

.ota-badge .fee {
    background: rgba(0, 0, 0, 0.2);
    padding: 0.2rem 0.6rem;
    border-radius: 4px;
    font-size: 0.85rem;
    margin-left: 0.5rem;
}

/* Price Breakdown */
.price-breakdown {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    font-size: 0.85rem;
}

.price-item {
    display: flex;
    justify-content: space-between;
}

.price-gross {
    color: var(--text-secondary);
}

.price-fee {
    color: #ef4444;
}

.price-net {
    font-weight: 700;
    color: var(--text-primary);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 0.35rem;
    margin-top: 0.35rem;
}

/* Room Badge */
.room-badge {
    display: inline-block;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 700;
}

/* Actions */
.row-actions {
    display: flex;
    gap: 0.3rem;
    flex-wrap: wrap;
}

.action-btn {
    padding: 0.4rem 0.7rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.7rem;
    font-weight: 600;
    transition: all 0.3s ease;
    background: rgba(99, 102, 241, 0.2);
    color: #6366f1;
    white-space: nowrap;
}

.action-btn:hover {
    background: rgba(99, 102, 241, 0.4);
}

.action-btn.action-cancel {
    background: rgba(245, 158, 11, 0.2);
    color: #f59e0b;
}

.action-btn.action-cancel:hover {
    background: rgba(245, 158, 11, 0.4);
}

.action-btn.action-delete {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
}

.action-btn.action-delete:hover {
    background: rgba(239, 68, 68, 0.4);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--text-secondary);
}

/* Responsive */
@media (max-width: 768px) {
    .reservasi-container {
        padding: 1.5rem 1rem;
    }

    .reservasi-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .reservasi-header h1 {
        font-size: 2rem;
    }

    .filter-section {
        grid-template-columns: 1fr;
    }

    .bookings-table {
        font-size: 0.85rem;
    }

    .bookings-table th,
    .bookings-table td {
        padding: 0.75rem;
    }

    .price-breakdown {
        font-size: 0.75rem;
    }
}

@media (max-width: 480px) {
    .header-actions {
        width: 100%;
    }

    .btn-primary {
        width: 100%;
    }

    .bookings-table-wrapper {
        padding: 1rem;
    }

    .row-actions {
        flex-direction: column;
    }

    .action-btn {
        width: 100%;
    }
}
</style>

<div class="reservasi-container">
    <!-- Header -->
    <div class="reservasi-header">
        <div>
            <h1>📅 Reservasi Management</h1>
        </div>
        <div class="header-actions">
            <button class="btn-primary" onclick="openNewBookingModal()">
                ➕ New Booking
            </button>
            <button class="btn-primary btn-secondary" onclick="window.location='calendar.php'">
                📆 Calendar View
            </button>
            <button class="btn-primary btn-secondary" onclick="window.location='breakfast.php'">
                🍽️ Breakfast List
            </button>
            <button class="btn-primary btn-secondary" onclick="window.location='settings.php'">
                ⚙️ Settings
            </button>
            <button class="btn-primary btn-secondary" onclick="window.location='dashboard.php'">
                🏠 Dashboard
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <div class="filter-group">
            <label>Status Filter</label>
            <select onchange="filterBookings(this.value)">
                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                <option value="checked_in" <?php echo $status_filter === 'checked_in' ? 'selected' : ''; ?>>Checked In</option>
                <option value="checked_out" <?php echo $status_filter === 'checked_out' ? 'selected' : ''; ?>>Checked Out</option>
                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="bookings-table-wrapper">
        <?php if (!empty($bookings)): ?>
        <table class="bookings-table">
            <thead>
                <tr>
                    <th>Booking Code</th>
                    <th>Guest Name</th>
                    <th>Room</th>
                    <th>Check-in / Check-out</th>
                    <th>Nights</th>
                    <th>OTA Source</th>
                    <th>Price Breakdown</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): 
                    $netIncome = calculateNetIncome(
                        $booking['room_price'], 
                        $booking['booking_source'],
                        $otaProviders
                    );
                    $otaIcon = $otaProviders[$booking['booking_source']]['icon'] ?? '🌐';
                    $otaName = $otaProviders[$booking['booking_source']]['name'] ?? 'Other';
                    $otaFee = $otaProviders[$booking['booking_source']]['fee'] ?? 0;
                ?>
                <tr>
                    <!-- Booking Code -->
                    <td>
                        <strong><?php echo htmlspecialchars($booking['booking_code']); ?></strong>
                    </td>

                    <!-- Guest -->
                    <td>
                        <div>
                            <strong><?php echo htmlspecialchars($booking['guest_name']); ?></strong>
                            <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.25rem;">
                                📞 <?php echo htmlspecialchars($booking['phone'] ?? '-'); ?>
                            </div>
                        </div>
                    </td>

                    <!-- Room -->
                    <td>
                        <span class="room-badge">
                            🚪 <?php echo htmlspecialchars($booking['room_number']); ?>
                        </span>
                        <div style="font-size: 0.85rem; margin-top: 0.25rem;">
                            <?php echo htmlspecialchars($booking['type_name']); ?>
                        </div>
                    </td>

                    <!-- Dates -->
                    <td>
                        <div style="font-size: 0.9rem;">
                            📍 <?php echo date('d M', strtotime($booking['check_in_date'])); ?> →
                            <?php echo date('d M', strtotime($booking['check_out_date'])); ?>
                        </div>
                    </td>

                    <!-- Nights -->
                    <td>
                        <strong><?php echo $booking['total_nights']; ?></strong>
                    </td>

                    <!-- OTA Source -->
                    <td>
                        <span class="ota-badge">
                            <?php echo $otaIcon; ?> <?php echo $otaName; ?>
                            <?php if ($otaFee > 0): ?>
                            <span class="fee">-<?php echo $otaFee; ?>%</span>
                            <?php endif; ?>
                        </span>
                    </td>

                    <!-- Price Breakdown -->
                    <td>
                        <div class="price-breakdown">
                            <div class="price-item price-gross">
                                <span>Gross:</span>
                                <span>Rp <?php echo number_format($netIncome['gross'], 0, ',', '.'); ?></span>
                            </div>
                            <?php if ($netIncome['fee_percent'] > 0): ?>
                            <div class="price-item price-fee">
                                <span>Fee (<?php echo $netIncome['fee_percent']; ?>%):</span>
                                <span>-Rp <?php echo number_format($netIncome['fee_amount'], 0, ',', '.'); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="price-item price-net">
                                <span>Net Income:</span>
                                <span>Rp <?php echo number_format($netIncome['net'], 0, ',', '.'); ?></span>
                            </div>
                        </div>
                    </td>

                    <!-- Status -->
                    <td>
                        <span class="badge badge-status badge-<?php echo str_replace('_', '-', $booking['status']); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $booking['status'])); ?>
                        </span>
                    </td>

                    <!-- Payment Status -->
                    <td>
                        <span class="badge badge-payment-<?php echo str_replace('_', '-', $booking['payment_status']); ?>">
                            <?php echo ucfirst($booking['payment_status']); ?>
                        </span>
                        <div style="font-size: 0.8rem; margin-top: 0.25rem;">
                            Rp <?php echo number_format($booking['paid_amount'], 0, ',', '.'); ?>
                        </div>
                    </td>

                    <!-- Actions -->
                    <td>
                        <div class="row-actions">
                            <button class="action-btn" onclick="viewBooking(<?php echo $booking['id']; ?>)">
                                👁️ View
                            </button>
                            <button class="action-btn" onclick="editBooking(<?php echo $booking['id']; ?>)">
                                ✏️ Edit
                            </button>
                            <?php if ($booking['status'] !== 'checked_in' && $booking['status'] !== 'checked_out'): ?>
                            <button class="action-btn action-cancel" onclick="cancelBooking(<?php echo $booking['id']; ?>, '<?php echo htmlspecialchars($booking['booking_code']); ?>')">
                                ❌ Cancel
                            </button>
                            <button class="action-btn action-delete" onclick="deleteBooking(<?php echo $booking['id']; ?>, '<?php echo htmlspecialchars($booking['booking_code']); ?>')">
                                🗑️ Delete
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <p style="font-size: 1.1rem;">📭 Tidak ada reservasi</p>
        </div>
        <?php endif; ?>
    </div>

</div>

<script>
function filterBookings(value) {
    window.location.search = '?status=' + value;
}

function openNewBookingModal() {
    alert('Coming Soon: New Booking Modal');
}

function viewBooking(id) {
    alert('Coming Soon: View Booking #' + id);
}

function editBooking(id) {
    alert('Coming Soon: Edit Booking #' + id);
}

function cancelBooking(id, bookingCode) {
    if (!confirm(`⚠️ Yakin ingin CANCEL reservasi ${bookingCode}?\n\nStatus akan berubah menjadi CANCELLED`)) {
        return;
    }
    
    fetch('../../api/cancel-booking.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            booking_id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Reservasi berhasil di-CANCEL');
            location.reload();
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error.message);
        console.error('Error:', error);
    });
}

function deleteBooking(id, bookingCode) {
    if (!confirm(`⚠️ PERINGATAN: Ingin menghapus reservasi ${bookingCode}?\n\nAksi ini TIDAK BISA DIBATALKAN!\n\nData akan dihapus permanen dari sistem.`)) {
        return;
    }
    
    const confirmDelete = prompt(`Ketik "HAPUS" untuk menghapus reservasi ${bookingCode}:`);
    if (confirmDelete !== 'HAPUS') {
        alert('Pembatalan dihapus. Data tetap aman.');
        return;
    }
    
    fetch('../../api/delete-booking.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            booking_id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Reservasi berhasil dihapus permanen');
            location.reload();
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error.message);
        console.error('Error:', error);
    });
}
    </a>
</div>

<div class="card coming-soon">
    <div class="coming-soon-icon">
        <i data-feather="calendar" style="width: 60px; height: 60px; color: white;"></i>
    </div>
    <h3 style="font-size: 2rem; font-weight: 800; color: var(--text-primary); margin-bottom: 1rem;">
        Coming Soon
    </h3>
    <p style="color: var(--text-muted); font-size: 1rem; max-width: 500px; margin: 0 auto;">
        Halaman Reservasi sedang dalam pengembangan. Fitur ini akan memungkinkan Anda untuk membuat booking baru, melihat daftar reservasi, konfirmasi booking, dan mengelola status reservasi tamu.
    </p>
</div>

<script>
feather.replace();
</script>

<?php include '../../includes/footer.php'; ?>
