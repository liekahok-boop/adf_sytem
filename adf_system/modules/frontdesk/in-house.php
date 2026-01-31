<?php
/**
 * FRONT DESK - TAMU IN HOUSE
 * Daftar semua tamu yang sedang check-in
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

$pageTitle = 'Tamu In House';

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// ============================================
// GET IN-HOUSE GUESTS
// ============================================
$queryError = null;
$debugInfo = [];
try {
    $today = date('Y-m-d');
    
    // Test simple query first
    $testQuery = "SELECT COUNT(*) as total FROM bookings WHERE status = 'checked_in'";
    $testResult = $db->fetchOne($testQuery);
    $debugInfo['simple_count'] = $testResult['total'] ?? 0;
    
    // Test with direct connection
    $conn = $db->getConnection();
    $stmt = $conn->prepare("
        SELECT 
            b.id as booking_id,
            b.booking_code,
            b.check_in_date,
            b.check_out_date,
            b.actual_checkin_time,
            b.room_price,
            b.final_price,
            b.payment_status,
            b.status,
            g.id as guest_id,
            g.guest_name,
            g.phone,
            g.email,
            g.id_card_number,
            g.address,
            r.id as room_id,
            r.room_number,
            r.floor_number,
            rt.type_name as room_type,
            rt.base_price,
            DATEDIFF(b.check_out_date, CURDATE()) as nights_remaining,
            DATEDIFF(CURDATE(), b.check_in_date) as nights_stayed
        FROM bookings b
        INNER JOIN guests g ON b.guest_id = g.id
        INNER JOIN rooms r ON b.room_id = r.id
        LEFT JOIN room_types rt ON r.room_type_id = rt.id
        WHERE b.status = 'checked_in'
        ORDER BY r.room_number ASC
    ");
    
    $stmt->execute();
    $inHouseGuests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $debugInfo['query_result'] = count($inHouseGuests);
    
    error_log("Direct PDO query returned: " . count($inHouseGuests) . " results");
    if (count($inHouseGuests) > 0) {
        error_log("First result: " . print_r($inHouseGuests[0], true));
    }
    
} catch (Exception $e) {
    $queryError = $e->getMessage();
    error_log("In House Query Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $inHouseGuests = [];
}

// Calculate statistics
$totalInHouse = count($inHouseGuests);
$totalRevenue = array_sum(array_column($inHouseGuests, 'final_price'));
$paidCount = count(array_filter($inHouseGuests, fn($g) => $g['payment_status'] === 'paid'));
$unpaidCount = $totalInHouse - $paidCount;

include '../../includes/header.php';
?>

<style>
:root {
    --primary-color: #6366f1;
    --secondary-color: #8b5cf6;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
    --glass-bg: rgba(255, 255, 255, 0.75);
    --glass-border: rgba(255, 255, 255, 0.45);
    --glass-blur: 16px;
}

[data-theme="dark"] {
    --glass-bg: rgba(30, 41, 59, 0.75);
    --glass-border: rgba(71, 85, 105, 0.45);
}

.in-house-container {
    max-width: 1600px;
    margin: 0 auto;
    padding: 2rem 1.5rem;
    background: linear-gradient(135deg, 
        rgba(99, 102, 241, 0.03) 0%, 
        rgba(139, 92, 246, 0.03) 50%,
        rgba(236, 72, 153, 0.03) 100%);
    min-height: 100vh;
}

/* Header */
.page-header {
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-header h1 {
    font-size: 2.2rem;
    font-weight: 900;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #ec4899 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.page-header h1::before {
    content: '🏨';
    font-size: 2.2rem;
    -webkit-text-fill-color: initial;
    filter: drop-shadow(0 4px 12px rgba(99, 102, 241, 0.4));
}

.page-subtitle {
    color: var(--text-secondary);
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.25rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--glass-bg);
    backdrop-filter: blur(var(--glass-blur));
    -webkit-backdrop-filter: blur(var(--glass-blur));
    border: 2px solid transparent;
    background-image: 
        linear-gradient(var(--glass-bg), var(--glass-bg)),
        linear-gradient(135deg, rgba(99, 102, 241, 0.3), rgba(139, 92, 246, 0.3));
    background-origin: border-box;
    background-clip: padding-box, border-box;
    border-radius: 16px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 
        0 4px 20px rgba(0, 0, 0, 0.08),
        0 8px 40px rgba(99, 102, 241, 0.12),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
}

.stat-card:hover {
    transform: translateY(-4px);
}

.stat-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.stat-value {
    font-size: 2rem;
    font-weight: 900;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.75rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.5px;
}

/* Guest Cards Grid */
.guests-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 1.25rem;
}

.guest-card {
    background: var(--glass-bg);
    backdrop-filter: blur(var(--glass-blur));
    -webkit-backdrop-filter: blur(var(--glass-blur));
    border: 2px solid transparent;
    background-image: 
        linear-gradient(var(--glass-bg), var(--glass-bg)),
        linear-gradient(135deg, rgba(99, 102, 241, 0.3), rgba(139, 92, 246, 0.3));
    background-origin: border-box;
    background-clip: padding-box, border-box;
    border-radius: 12px;
    padding: 1.25rem;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 
        0 4px 20px rgba(0, 0, 0, 0.08),
        0 8px 40px rgba(99, 102, 241, 0.12),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
    position: relative;
    overflow: hidden;
}

.guest-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
}

.guest-card:hover {
    transform: translateY(-6px);
    box-shadow: 
        0 12px 32px rgba(0, 0, 0, 0.12),
        0 16px 48px rgba(99, 102, 241, 0.2);
}

.guest-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.guest-name {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 0.25rem 0;
}

.booking-code {
    font-size: 0.75rem;
    color: var(--text-secondary);
    font-family: 'Courier New', monospace;
}

.room-badge {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.guest-info {
    margin-bottom: 1rem;
}

.info-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--glass-border);
}

.info-row:last-child {
    border-bottom: none;
}

.info-icon {
    font-size: 1rem;
    width: 20px;
    text-align: center;
}

.info-label {
    font-size: 0.75rem;
    color: var(--text-secondary);
    font-weight: 600;
    min-width: 100px;
}

.info-value {
    font-size: 0.875rem;
    color: var(--text-primary);
    font-weight: 600;
}

.guest-footer {
    display: flex;
    gap: 0.75rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--glass-border);
}

.btn {
    flex: 1;
    padding: 0.65rem 1rem;
    border-radius: 8px;
    border: none;
    font-weight: 700;
    font-size: 0.8rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.3s;
    text-decoration: none;
}

.btn-checkout {
    background: linear-gradient(135deg, #ef4444, #f87171);
    color: white;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn-checkout:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
}

.btn-breakfast {
    background: linear-gradient(135deg, #f59e0b, #fbbf24);
    color: white;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.btn-breakfast:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(245, 158, 11, 0.4);
}

.btn-details {
    background: var(--glass-bg);
    color: var(--text-primary);
    border: 1px solid var(--glass-border);
}

.btn-details:hover {
    background: rgba(99, 102, 241, 0.1);
    border-color: rgba(99, 102, 241, 0.3);
}

.status-badge {
    padding: 0.35rem 0.65rem;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-paid {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.status-unpaid {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.3);
}

.status-partial {
    background: rgba(245, 158, 11, 0.15);
    color: #f59e0b;
    border: 1px solid rgba(245, 158, 11, 0.3);
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--glass-bg);
    backdrop-filter: blur(var(--glass-blur));
    border-radius: 16px;
    border: 2px dashed var(--glass-border);
}

.empty-state-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.empty-state-text {
    font-size: 1.25rem;
    color: var(--text-secondary);
    margin: 0;
}

@media (max-width: 768px) {
    .guests-grid {
        grid-template-columns: 1fr;
    }
    
    .page-header h1 {
        font-size: 1.75rem;
    }
}
</style>

<div class="in-house-container">
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1>Tamu In House</h1>
            <p class="page-subtitle">Daftar tamu yang sedang menginap • <?php echo date('l, d F Y'); ?></p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">🏨</div>
            <div class="stat-value"><?php echo $totalInHouse; ?></div>
            <div class="stat-label">Total In House</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-value"><?php echo $paidCount; ?></div>
            <div class="stat-label">Lunas</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⚠️</div>
            <div class="stat-value"><?php echo $unpaidCount; ?></div>
            <div class="stat-label">Belum Bayar</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-value">Rp <?php echo number_format($totalRevenue, 0, ',', '.'); ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>

    <!-- Guest Cards -->
    <?php if (count($inHouseGuests) > 0): ?>
    <div class="guests-grid">
        <?php foreach ($inHouseGuests as $guest): ?>
        <div class="guest-card">
            <div class="guest-header">
                <div>
                    <h3 class="guest-name"><?php echo htmlspecialchars($guest['guest_name']); ?></h3>
                    <p class="booking-code">#<?php echo htmlspecialchars($guest['booking_code']); ?></p>
                </div>
                <div class="room-badge">
                    <span>🚪</span>
                    <span>Room <?php echo htmlspecialchars($guest['room_number']); ?></span>
                </div>
            </div>

            <div class="guest-info">
                <div class="info-row">
                    <span class="info-icon">📱</span>
                    <span class="info-label">Phone</span>
                    <span class="info-value"><?php echo htmlspecialchars($guest['phone'] ?: '-'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-icon">📅</span>
                    <span class="info-label">Check-in</span>
                    <span class="info-value"><?php echo date('d M', strtotime($guest['check_in_date'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-icon">📤</span>
                    <span class="info-label">Check-out</span>
                    <span class="info-value"><?php echo date('d M', strtotime($guest['check_out_date'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-icon">💳</span>
                    <span class="info-label">Payment</span>
                    <span class="status-badge status-<?php echo $guest['payment_status']; ?>">
                        <?php echo strtoupper($guest['payment_status']); ?>
                    </span>
                </div>
            </div>

            <div class="guest-footer">
                <button class="btn btn-breakfast" onclick="selectBreakfast(<?php echo $guest['booking_id']; ?>, '<?php echo htmlspecialchars($guest['guest_name']); ?>')">
                    <span>🍳</span>
                    <span>Breakfast</span>
                </button>
                <button class="btn btn-checkout" onclick="doCheckOutGuest(<?php echo $guest['booking_id']; ?>, '<?php echo htmlspecialchars($guest['guest_name']); ?>', '<?php echo $guest['room_number']; ?>')">
                    <span>🚪</span>
                    <span>Check-out</span>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon">🏖️</div>
        <p class="empty-state-text">Tidak ada tamu in house saat ini</p>
    </div>
    <?php endif; ?>
</div>

<!-- Breakfast Orders Modal -->
<div id="breakfastModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: var(--bg-primary); border-radius: 20px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; padding: 2rem; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="margin: 0; font-size: 1.5rem; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                🍳 Breakfast Orders
            </h2>
            <button onclick="closeBreakfastModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-secondary);">✕</button>
        </div>
        <div id="breakfastContent" style="color: var(--text-primary);">
            <div style="text-align: center; padding: 2rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">⏳</div>
                <p>Loading breakfast orders...</p>
            </div>
        </div>
        <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
            <button onclick="closeBreakfastModal()" style="flex: 1; padding: 0.75rem; border: 1px solid var(--bg-tertiary); background: var(--bg-secondary); color: var(--text-primary); border-radius: 10px; font-weight: 600; cursor: pointer;">
                Tutup
            </button>
            <button onclick="addNewBreakfast()" style="flex: 1; padding: 0.75rem; border: none; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; border-radius: 10px; font-weight: 600; cursor: pointer;">
                + Tambah Order
            </button>
        </div>
    </div>
</div>

<script>
let currentBookingId = null;
let currentGuestName = null;

function doCheckOutGuest(bookingId, guestName, roomNumber) {
    if (!confirm(`Check-out ${guestName} dari Room ${roomNumber}?`)) {
        return;
    }
    
    // Show loading
    const btn = event.target.closest('.btn-checkout');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<span>⏳</span><span>Processing...</span>';
    btn.disabled = true;
    
    fetch('<?php echo BASE_URL; ?>/api/checkout-guest.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        credentials: 'include',
        body: 'booking_id=' + bookingId
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                console.error('Non-JSON response:', text);
                throw new Error('Server mengembalikan response non-JSON');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            window.location.reload();
        } else {
            alert('❌ Error: ' + data.message);
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Terjadi kesalahan sistem: ' + error.message);
        btn.innerHTML = originalHTML;
        btn.disabled = false;
    });
}

// Select Breakfast - Show Modal with Orders
function selectBreakfast(bookingId, guestName) {
    currentBookingId = bookingId;
    currentGuestName = guestName;
    
    // Show modal
    document.getElementById('breakfastModal').style.display = 'flex';
    
    // Fetch breakfast orders
    fetch('<?php echo BASE_URL; ?>/api/get-breakfast-orders.php?booking_id=' + bookingId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayBreakfastOrders(data.orders, guestName);
            } else {
                document.getElementById('breakfastContent').innerHTML = `
                    <div style="text-align: center; padding: 2rem;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">❌</div>
                        <p>Error: ${data.message}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('breakfastContent').innerHTML = `
                <div style="text-align: center; padding: 2rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">⚠️</div>
                    <p>Gagal memuat data breakfast orders</p>
                </div>
            `;
        });
}

function displayBreakfastOrders(orders, guestName) {
    const content = document.getElementById('breakfastContent');
    
    if (orders.length === 0) {
        content.innerHTML = `
            <div style="text-align: center; padding: 2rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🍽️</div>
                <p style="color: var(--text-secondary);">${guestName} belum memiliki breakfast order</p>
            </div>
        `;
        return;
    }
    
    let html = `<div style="margin-bottom: 1rem;"><strong>${guestName}</strong></div>`;
    
    orders.forEach(order => {
        const orderDate = new Date(order.breakfast_date + ' ' + order.breakfast_time);
        const formattedDate = orderDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
        const formattedTime = orderDate.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        
        html += `
            <div style="background: var(--bg-secondary); border-radius: 12px; padding: 1rem; margin-bottom: 1rem; border: 1px solid var(--bg-tertiary);">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.75rem;">
                    <div>
                        <div style="font-weight: 600; color: var(--primary);">${formattedDate} ${formattedTime}</div>
                        <div style="font-size: 0.9rem; color: var(--text-secondary);">Room ${order.room_number} • ${order.total_pax} pax</div>
                    </div>
                    <span style="padding: 0.25rem 0.75rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600; ${order.location === 'restaurant' ? 'background: rgba(99, 102, 241, 0.2); color: #6366f1;' : 'background: rgba(139, 92, 246, 0.2); color: #8b5cf6;'}">
                        ${order.location === 'restaurant' ? '🍽️ Restaurant' : '🚪 Room'}
                    </span>
                </div>
                <div style="padding-left: 0.5rem;">
                    <strong style="font-size: 0.9rem;">Menu:</strong>
                    <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
        `;
        
        order.menu_items.forEach(item => {
            html += `<li style="margin: 0.25rem 0;"><span style="color: var(--primary); font-weight: 600;">x${item.quantity}</span> ${item.menu_name}</li>`;
        });
        
        html += `
                    </ul>
                </div>
            </div>
        `;
    });
    
    content.innerHTML = html;
}

function closeBreakfastModal() {
    document.getElementById('breakfastModal').style.display = 'none';
    currentBookingId = null;
    currentGuestName = null;
}

function addNewBreakfast() {
    window.location.href = 'breakfast.php?booking_id=' + currentBookingId;
}

// Close modal when clicking outside
document.getElementById('breakfastModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeBreakfastModal();
    }
});
</script>

<?php include '../../includes/footer.php'; ?>

