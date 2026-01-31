<?php
/**
 * FRONT DESK - CALENDAR BOOKING VIEW
 * Interactive Calendar like CloudBeds
 * Horizontal: Dates | Vertical: Room Numbers + Guest Names
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

$pageTitle = 'Calendar Booking';

// ============================================
// GET CALENDAR DATE RANGE
// ============================================
$startDate = $_GET['start'] ?? date('Y-m-d');
$daysToShow = 365; // Show 365 days (1 full year) untuk banyak scrollable content
$dates = [];
for ($i = 0; $i < $daysToShow; $i++) {
    $dates[] = date('Y-m-d', strtotime($startDate . " +{$i} days"));
}

// ============================================
// GET ALL ROOMS WITH TYPES
// ============================================
try {
    $rooms = $db->fetchAll("
        SELECT r.id, r.room_number, r.floor_number, r.status, rt.type_name, rt.base_price, rt.color_code
        FROM rooms r
        LEFT JOIN room_types rt ON r.room_type_id = rt.id
        WHERE r.status != 'maintenance'
        ORDER BY rt.type_name ASC, r.floor_number ASC, r.room_number ASC
    ", []);
} catch (Exception $e) {
    error_log("Rooms Error: " . $e->getMessage());
    $rooms = [];
}

// ============================================
// GET BOOKINGS FOR DATE RANGE
// ============================================
try {
    $endDate = date('Y-m-d', strtotime($startDate . " +{$daysToShow} days"));
    
    // Fetch all bookings that overlap with date range
    $bookings = $db->fetchAll("
        SELECT 
            b.id, 
            b.booking_code, 
            b.room_id, 
            b.check_in_date, 
            b.check_out_date,
            b.status, 
            b.room_price, 
            b.booking_source,
            b.payment_status,
            g.guest_name, 
            g.phone
        FROM bookings b
        LEFT JOIN guests g ON b.guest_id = g.id
        WHERE b.check_in_date < ? 
        AND b.check_out_date > ?
        AND b.status IN ('pending', 'confirmed', 'checked_in')
        ORDER BY b.check_in_date ASC, b.room_id ASC
    ", [$endDate, $startDate]);
    
    echo "<!-- DEBUG: Found " . count($bookings) . " bookings -->\n";
    
} catch (Exception $e) {
    error_log("Bookings Error: " . $e->getMessage());
    $bookings = [];
}

// ============================================
// BUILD BOOKING MATRIX
// ============================================
$bookingMatrix = [];
foreach ($bookings as $booking) {
    $roomId = $booking['room_id'];
    if (!isset($bookingMatrix[$roomId])) {
        $bookingMatrix[$roomId] = [];
    }
    $bookingMatrix[$roomId][$booking['booking_code']] = $booking;
}

// ============================================
// BOOKING COLORS - SIMPLE: Default vs Checked-In
// ============================================
$defaultColor = ['bg' => '#3b82f6', 'text' => 'white'];      // Blue for pending/confirmed bookings
$checkedInColor = ['bg' => '#10b981', 'text' => 'white'];    // Green for checked-in guests (active)

include '../../includes/header.php';
?>

<style>
/* ============================================
   CLOUDBEDS STYLE CALENDAR - SYSTEM THEME
   ============================================ */

.calendar-container {
    max-width: 100%;
    padding: 0.5rem 0.25rem;
    overflow: hidden;
}

/* Scroll Container - MUST BE CONSTRAINED */
.calendar-scroll-container {
    width: 100%;
    max-width: 100vw;
    overflow: hidden;
    position: relative;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Scroll Navigation Buttons */
.scroll-nav-btn {
    flex-shrink: 0;
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border: none;
    border-radius: 50%;
    color: white;
    cursor: pointer;
    font-size: 1.2rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    z-index: 10;
}

.scroll-nav-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 12px rgba(99, 102, 241, 0.4);
}

.scroll-nav-btn:active {
    transform: scale(0.95);
}

.calendar-header {
    display: flex;
    flex-direction: column;
    margin-bottom: 1rem;
    gap: 0.75rem;
}

.calendar-header h1 {
    font-size: 1.75rem;
    font-weight: 800;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0;
}

.calendar-header h1 .icon {
    -webkit-text-fill-color: #6366f1;
    background: none;
}

.calendar-controls {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
}

.btn-nav {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #ffffff !important;
    border: none;
    padding: 0.6rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 700;
    font-size: 0.85rem;
    transition: all 0.3s ease;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    line-height: 1.2;
    text-decoration: none;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.btn-nav:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
    color: #ffffff !important;
}

.btn-nav:visited,
.btn-nav:active,
.btn-nav:focus {
    color: #ffffff !important;
}

.date-display {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text-primary);
}

.nav-date-input {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    padding: 0.5rem 0.8rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.8rem;
}

/* Navigation Bar */
.calendar-nav {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    background: var(--card-bg);
    backdrop-filter: blur(30px);
    border: 0.5px solid var(--border-color);
    border-radius: 12px;
    padding: 0.6rem;
}

.nav-btn {
    background: rgba(99, 102, 241, 0.2);
    color: #6366f1;
    border: 1px solid rgba(99, 102, 241, 0.3);
    padding: 0.5rem 0.8rem;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.8rem;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.nav-btn:hover {
    background: rgba(99, 102, 241, 0.4);
    border-color: rgba(99, 102, 241, 0.6);
    color: white;
}

.nav-date-input {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: var(--text-primary);
    padding: 0.6rem 1rem;
    border-radius: 8px;
    font-weight: 600;
}

/* Calendar Wrapper */
.calendar-wrapper {
    background: var(--card-bg);
    backdrop-filter: blur(30px);
    border: 0.5px solid var(--border-color);
    border-radius: 12px;
    overflow-x: auto !important;
    overflow-y: visible !important;
    padding: 0.5rem;
    user-select: none;
    -webkit-user-select: none;
    -webkit-touch-callout: none;
    cursor: grab;
    width: 100%;
    max-width: calc(100vw - 200px);
    box-sizing: border-box;
    position: relative;
}

/* Light Theme - Make borders more visible */
body[data-theme="light"] .calendar-wrapper,
body[data-theme="light"] .calendar-nav,
body[data-theme="light"] .legend {
    border: 1px solid rgba(51, 65, 85, 0.2);
}
body[data-theme="light"] .calendar-wrapper,
body[data-theme="light"] .calendar-nav,
body[data-theme="light"] .legend {
    border: 1px solid rgba(51, 65, 85, 0.2);
}

body[data-theme="light"] .grid-header-date,
body[data-theme="light"] .grid-date-cell,
body[data-theme="light"] .grid-room-label,
body[data-theme="light"] .grid-room-type-header,
body[data-theme="light"] .grid-header-room {
    border-color: rgba(51, 65, 85, 0.15);
}

body[data-theme="light"] .grid-header-date,
body[data-theme="light"] .grid-date-cell {
    border-right-width: 1px;
    border-bottom-width: 1px;
}

body[data-theme="light"] .grid-room-label,
body[data-theme="light"] .grid-room-type-header {
    border-right-width: 1px;
    border-bottom-width: 1px;
}

.calendar-wrapper::-webkit-scrollbar {
    height: 12px;
}

.calendar-wrapper::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.calendar-wrapper::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.7), rgba(139, 92, 246, 0.7));
    border-radius: 10px;
    border: 2px solid rgba(255, 255, 255, 0.2);
}

.calendar-wrapper::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.9), rgba(139, 92, 246, 0.9));
}

.calendar-grid {
    display: grid;
    gap: 0;
    grid-template-columns: 110px repeat(<?php echo count($dates); ?>, 100px);
    width: fit-content;
    min-width: fit-content;
    max-width: none;
}

/* Header Row */
.calendar-grid-header {
    display: contents;
}

.grid-header-room {
    background: rgba(99, 102, 241, 0.15);
    backdrop-filter: blur(10px);
    border: 0.5px solid var(--border-color);
    padding: 0.35rem 0.2rem;
    font-weight: 900;
    text-align: center;
    position: sticky;
    left: 0;
    z-index: 20;
    font-size: 0.85rem;
    color: var(--text-primary);
    box-shadow: 1px 1px 4px rgba(0, 0, 0, 0.1);
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Light theme - better header visibility */
body[data-theme="light"] .grid-header-room {
    background: rgba(99, 102, 241, 0.1);
    font-weight: 900;
    border: 1px solid rgba(51, 65, 85, 0.2);
}

.grid-header-date {
    background: linear-gradient(180deg, rgba(99, 102, 241, 0.15), rgba(99, 102, 241, 0.05));
    border-right: 0.5px solid var(--border-color);
    border-bottom: 0.5px solid var(--border-color);
    padding: 0.3rem 0.15rem;
    text-align: center;
    font-weight: 600;
    font-size: 0.6rem;
    color: var(--text-primary);
    position: relative;
}

/* Light theme - visible borders */
body[data-theme="light"] .grid-header-date {
    border-right: 1px solid rgba(51, 65, 85, 0.15);
    border-bottom: 1px solid rgba(51, 65, 85, 0.15);
    background: transparent;
}

/* TODAY HIGHLIGHT - SIMPLE & ELEGANT */
.grid-header-date.today {
    background: rgba(99, 102, 241, 0.1) !important;
}

.grid-header-date.today .grid-header-date-num {
    color: #6366f1;
    font-weight: 900;
}

.grid-date-cell.today {
    background: rgba(99, 102, 241, 0.05) !important;
}

/* Light theme - more visible today highlight */
body[data-theme="light"] .grid-header-date.today {
    background: rgba(99, 102, 241, 0.15) !important;
}

body[data-theme="light"] .grid-date-cell.today {
    background: rgba(99, 102, 241, 0.08) !important;
}

.grid-header-date-day {
    display: block;
    font-size: 0.55rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 0.1rem;
    color: var(--text-secondary);
    font-weight: 600;
}

.grid-header-date-num {
    display: block;
    font-size: 0.85rem;
    font-weight: 900;
    margin-bottom: 0.1rem;
    line-height: 1;
    color: var(--text-primary);
}

.grid-header-price {
    display: block;
    font-size: 0.6rem;
    color: #6366f1;
    font-weight: 800;
    margin-top: 0.1rem;
    line-height: 1;
    background: rgba(99, 102, 241, 0.1);
    padding: 0.1rem 0.2rem;
    border-radius: 3px;
}

/* Room Row */
.grid-room-label {
    background: var(--sidebar-bg);
    backdrop-filter: blur(10px);
    border-right: 0.5px solid var(--border-color);
    border-bottom: 0.5px solid var(--border-color);
    padding: 0.3rem 0.25rem;
    font-weight: 800;
    color: var(--text-primary);
    position: sticky;
    left: 0;
    z-index: 15;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 0.05rem;
    min-width: 90px;
    cursor: grab;
    font-size: 0.75rem;
    min-height: 28px;
    box-shadow: 1px 0 4px rgba(0, 0, 0, 0.1);
}

/* Light theme - better room label contrast */
body[data-theme="light"] .grid-room-label {
    background: rgba(248, 250, 252, 0.98);
    font-weight: 900;
    border-right: 1px solid rgba(51, 65, 85, 0.15);
    border-bottom: 1px solid rgba(51, 65, 85, 0.15);
}

.grid-room-type-header {
    background: linear-gradient(90deg, rgba(99, 102, 241, 0.15), rgba(99, 102, 241, 0.1));
    backdrop-filter: blur(10px);
    border-right: 0.5px solid var(--border-color);
    border-bottom: 0.5px solid var(--border-color);
    padding: 0.25rem 0.3rem;
    font-weight: 800;
    color: var(--text-primary);
    position: sticky;
    left: 0;
    z-index: 15;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    font-size: 0.9rem;
    gap: 0.25rem;
    min-height: 18px;
    box-shadow: 1px 0 4px rgba(0, 0, 0, 0.1);
}

/* Light theme - better type header visibility */
body[data-theme="light"] .grid-room-type-header {
    background: rgba(99, 102, 241, 0.08);
    font-weight: 900;
    border-right: 1px solid rgba(51, 65, 85, 0.15);
    border-bottom: 1px solid rgba(51, 65, 85, 0.15);
}

/* Ensure booking bar text stays white in light theme */
body[data-theme="light"] .booking-bar,
body[data-theme="light"] .booking-bar span,
body[data-theme="light"] .booking-bar * {
    color: #ffffff !important;
}

/* Maximum specificity - force white text in all scenarios */
.booking-bar,
.booking-bar span,
.booking-bar > span,
body .booking-bar,
body .booking-bar span,
body .booking-bar > span {
    color: #fff !important;
    -webkit-text-fill-color: #fff !important;
}

.grid-room-number {
    font-size: 0.9rem;
    color: var(--text-primary);
    font-weight: 900;
    line-height: 1.1;
    letter-spacing: 0.3px;
}

.grid-room-type {
    font-size: 0.65rem;
    color: var(--text-secondary);
    font-weight: 600;
    line-height: 1;
}

.grid-room-price {
    display: none;
}

/* Date Cells */
.grid-date-cell {
    border-right: 0.5px solid var(--border-color);
    border-bottom: 0.5px solid var(--border-color);
    padding: 0.2rem 0.1rem;
    min-height: 28px;
    position: relative;
    background: transparent;
    cursor: pointer;
    transition: background 0.2s ease;
}

/* Same-day turnover divider (checkout left, checkin right) */
.grid-date-cell.has-turnover::before {
    content: '';
    position: absolute;
    left: 50%;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(180deg, #ef4444, #f97316, #ef4444);
    z-index: 5;
    box-shadow: 0 0 6px rgba(239, 68, 68, 0.6);
    transform: translateX(-50%);
}

/* Light theme - visible cell borders */
body[data-theme="light"] .grid-date-cell {
    border-right: 1px solid rgba(51, 65, 85, 0.15);
    border-bottom: 1px solid rgba(51, 65, 85, 0.15);
}

.grid-date-cell:last-child {
    border-right: none;
}

.grid-date-cell:hover {
    background: rgba(99, 102, 241, 0.05);
}

/* Booking Bars - CLOUDBED STYLE (Noon to Noon) */
.booking-bar-container {
    position: absolute;
    top: 1px;
    left: 1px;
    height: 26px;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    overflow: visible;
    pointer-events: auto;
    z-index: 10;
}

.booking-bar {
    width: 100%;
    height: 24px;
    padding: 0 0.5rem;
    cursor: pointer;
    overflow: visible;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    transition: all 0.3s ease;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2), 0 2px 4px rgba(0, 0, 0, 0.15);
    font-weight: 800;
    font-size: 0.9rem;
    line-height: 1;
    position: relative;
    pointer-events: auto;
    border-radius: 3px;
    white-space: nowrap;
    transform: skewX(-20deg);
    background: linear-gradient(135deg, #06b6d4, #22d3ee) !important;
    color: #ffffff !important;
}

.booking-bar > span {
    transform: skewX(20deg);
    color: #ffffff !important;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.7);
    font-weight: 800;
    display: block;
}

.booking-bar *,
.booking-bar > * {
    color: #ffffff !important;
}

.booking-bar::before {
    content: '';
    position: absolute;
    left: -8px;
    top: 50%;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-top: 12px solid transparent;
    border-bottom: 12px solid transparent;
    border-right: 8px solid;
    border-right-color: inherit;
}

.booking-bar::after {
    content: '';
    position: absolute;
    right: -8px;
    top: 50%;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-top: 12px solid transparent;
    border-bottom: 12px solid transparent;
    border-left: 8px solid;
    border-left-color: inherit;
}

.booking-bar:hover {
    transform: skewX(-20deg) scaleY(1.15);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    z-index: 20;
}

/* Past Booking Styling - Samar-samar Abu-abu Transparan */
.booking-bar.booking-past {
    opacity: 0.4 !important;
    background: linear-gradient(135deg, #9ca3af, #d1d5db) !important;
    border-right-color: #9ca3af !important;
    border-left-color: #d1d5db !important;
}

.booking-bar.booking-past > span {
    color: #6b7280 !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1) !important;
}

.booking-bar.booking-past:hover {
    opacity: 0.6 !important;
    transform: skewX(-20deg) scaleY(1.1);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

/* Status specific bars */
.booking-confirmed {
    background: linear-gradient(135deg, #06b6d4, #22d3ee) !important;
    border-right-color: #06b6d4;
    border-left-color: #22d3ee;
}

.booking-pending {
    background: linear-gradient(135deg, #0ea5e9, #38bdf8) !important;
    border-right-color: #0ea5e9;
    border-left-color: #38bdf8;
}

.booking-checked-in {
    background: linear-gradient(135deg, #0284c7, #0ea5e9) !important;
    border-right-color: #0284c7;
    border-left-color: #0ea5e9;
}

.booking-bar-guest,
.booking-bar-code,
.booking-bar-status {
    color: #ffffff !important;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.6);
    font-weight: 800;
}

/* Legend */
.legend {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-top: 0.75rem;
    padding: 0.75rem;
    background: var(--card-bg);
    backdrop-filter: blur(30px);
    border: 0.5px solid var(--border-color);
    border-radius: 12px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.legend-color {
    width: 28px;
    height: 28px;
    border-radius: 5px;
    border: 0.5px solid var(--border-color);
}

.legend-label {
    font-weight: 700;
    font-size: 0.75rem;
    color: var(--text-primary);
}

/* Responsive */
@media (max-width: 768px) {
    .calendar-container {
        padding: 0.75rem 0.25rem;
    }

    .calendar-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .calendar-header h1 {
        font-size: 1.75rem;
    }

    .grid-header-date {
        padding: 0.55rem 0.25rem;
        font-size: 0.72rem;
    }

    .grid-header-date-num {
        font-size: 1rem;
    }

    .grid-room-label {
        padding: 0.6rem 0.35rem;
        min-width: 80px;
    }

    .grid-date-cell {
        min-height: 85px;
    }

    .booking-bar {
        height: 80px;
        font-size: 0.65rem;
    }

    .booking-bar-guest {
        font-size: 0.7rem;
    }

    .calendar-nav {
        flex-direction: column;
        gap: 1rem;
    }
}

@media (max-width: 480px) {
    .calendar-wrapper {
        padding: 0.5rem;
    }

    .grid-room-label {
        padding: 0.5rem;
        font-size: 0.72rem;
        min-width: 60px;
    }

    .grid-date-cell {
        min-height: 75px;
        padding: 0.25rem;
    }

    .booking-bar {
        height: 70px;
        font-size: 0.6rem;
        padding: 0.2rem;
        color: #ffffff !important;
    }

    .booking-bar-guest {
        font-size: 0.65rem;
        color: #ffffff !important;
    }

    .grid-header-date-num {
        font-size: 0.9rem;
    }

    .legend {
        flex-direction: column;
        gap: 1rem;
    }
}

/* ============================================
   MODAL POPUP STYLES
   ============================================ */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(5px);
    z-index: 100;
    align-items: center;
    justify-content: center;
}

.modal-overlay.active {
    display: flex;
}

.modal-content {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 1.5rem;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
    animation: slideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    z-index: 101;
}

body[data-theme="light"] .modal-content {
    background: white;
    border: 1px solid rgba(51, 65, 85, 0.15);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    margin-bottom: 1rem;
    text-align: center;
}

.modal-header h2 {
    color: var(--text-primary);
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.modal-header p {
    color: var(--text-secondary);
    font-size: 0.8rem;
}

.modal-close {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    background: rgba(239, 68, 68, 0.15);
    border: 2px solid rgba(239, 68, 68, 0.3);
    color: #ef4444;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.5rem;
    font-weight: 700;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    z-index: 1000;
}

.modal-close:hover {
    background: rgba(239, 68, 68, 0.25);
    border-color: rgba(239, 68, 68, 0.5);
    color: #dc2626;
    transform: rotate(90deg) scale(1.1);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.modal-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.modal-btn {
    padding: 1rem;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.modal-btn-primary {
    background: linear-gradient(135deg, #10b981, #34d399);
    color: white;
}

.modal-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(16, 185, 129, 0.3);
}

.modal-btn-secondary {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
}

.modal-btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(99, 102, 241, 0.3);
}

.modal-date-info {
    background: rgba(99, 102, 241, 0.15);
    border: 1px solid rgba(99, 102, 241, 0.3);
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1rem;
    text-align: center;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

/* RESERVATION FORM STYLES */
.modal-content-large {
    max-width: 650px;
    max-height: 85vh;
    overflow-y: auto;
}

.modal-content-medium {
    max-width: 500px;
    max-height: 80vh;
    overflow-y: auto;
}

/* Booking Details Modal Styles */
.booking-details-content {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin: 1rem 0;
}

.detail-section {
    background: rgba(99, 102, 241, 0.05);
    border: 1px solid rgba(99, 102, 241, 0.15);
    border-radius: 8px;
    padding: 0.75rem;
}

body[data-theme="light"] .detail-section {
    background: rgba(248, 250, 252, 0.8);
    border: 1px solid rgba(51, 65, 85, 0.15);
}

.detail-section h3 {
    color: var(--text-primary);
    font-size: 0.8rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.4rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.3rem 0;
}

.detail-label {
    color: var(--text-secondary);
    font-size: 0.75rem;
    font-weight: 600;
}

.detail-value {
    color: var(--text-primary);
    font-size: 0.8rem;
    font-weight: 700;
    text-align: right;
}

.status-badge {
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-badge.status-confirmed {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
}

.status-badge.status-pending {
    background: rgba(245, 158, 11, 0.2);
    color: #f59e0b;
}

.status-badge.status-checked_in {
    background: rgba(59, 130, 246, 0.2);
    color: #3b82f6;
}

.status-badge.status-paid {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
}

.status-badge.status-unpaid {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
}

.status-badge.status-partial {
    background: rgba(245, 158, 11, 0.2);
    color: #f59e0b;
}

/* Booking Action Buttons */
.booking-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
}

.btn-action {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.85rem 1rem;
    border: none;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-checkin {
    background: linear-gradient(135deg, #10b981, #34d399);
    color: white;
}

.btn-checkin:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

.btn-checkout {
    background: linear-gradient(135deg, #3b82f6, #60a5fa);
    color: white;
}

.btn-checkout:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
}

.btn-move {
    background: linear-gradient(135deg, #f59e0b, #fbbf24);
    color: white;
}

.btn-move:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
}

.form-grid {
    display: grid;
    gap: 1rem;
}

.form-section {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1rem;
}

body[data-theme="light"] .form-section {
    background: rgba(248, 250, 252, 0.5);
    border: 1px solid rgba(51, 65, 85, 0.15);
}

.form-section h3 {
    color: var(--text-primary);
    font-size: 0.85rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 0.75rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.form-group label {
    color: var(--text-secondary);
    font-size: 0.75rem;
    font-weight: 600;
}

.form-group input,
.form-group select,
.form-group textarea {
    background: var(--sidebar-bg);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    padding: 0.6rem 0.75rem;
    color: var(--text-primary);
    font-size: 0.85rem;
    transition: all 0.2s ease;
}

body[data-theme="light"] .form-group input,
body[data-theme="light"] .form-group select,
body[data-theme="light"] .form-group textarea {
    background: white;
    border: 1px solid rgba(51, 65, 85, 0.2);
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15);
}

.form-group textarea {
    resize: vertical;
    font-family: inherit;
    min-height: 70px;
}

.price-summary {
    background: var(--sidebar-bg);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    padding: 0.75rem;
    margin-top: 0.5rem;
}

body[data-theme="light"] .price-summary {
    background: rgba(16, 185, 129, 0.05);
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.price-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.4rem 0;
    color: var(--text-secondary);
    font-size: 0.8rem;
}

.price-row strong {
    color: var(--text-primary);
    font-size: 0.85rem;
    font-weight: 600;
}

.price-row-total {
    border-top: 1px solid var(--border-color);
    margin-top: 0.4rem;
    padding-top: 0.6rem;
    font-size: 0.9rem;
    font-weight: 700;
}

body[data-theme="light"] .price-row-total {
    border-top-color: rgba(16, 185, 129, 0.3);
}

.price-row-total strong {
    color: #10b981;
    font-size: 1rem;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
}

.btn-primary,
.btn-secondary {
    padding: 0.6rem 1.25rem;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    font-size: 0.85rem;
    transition: all 0.2s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #10b981, #34d399);
    color: white !important;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-secondary {
    background: var(--sidebar-bg);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}

body[data-theme="light"] .btn-secondary {
    background: white;
    border: 1px solid rgba(51, 65, 85, 0.2);
}

.btn-secondary:hover {
    background: rgba(99, 102, 241, 0.1);
    border-color: #6366f1;
}

.modal-date-info strong {
    color: var(--text-primary);
    font-size: 1.1rem;
    display: block;
    margin-top: 0.5rem;
}
</style>

<div class="calendar-container">
    <!-- Header -->
    <div class="calendar-header">
        <div>
            <h1><span class="icon">📆</span> Calendar Booking</h1>
        </div>
        <div class="calendar-controls">
            <a href="<?php echo BASE_URL; ?>/modules/frontdesk/reservasi.php" class="btn-nav">
                📋 List View
            </a>
            <a href="<?php echo BASE_URL; ?>/modules/frontdesk/breakfast.php" class="btn-nav">
                🍽️ Breakfast List
            </a>
            <a href="<?php echo BASE_URL; ?>/modules/frontdesk/settings.php" class="btn-nav">
                ⚙️ Settings
            </a>
            <a href="<?php echo BASE_URL; ?>/modules/frontdesk/dashboard.php" class="btn-nav">
                📊 Dashboard
            </a>
        </div>
    </div>

    <!-- Navigation -->
    <div class="calendar-nav">
        <button class="nav-btn" onclick="prevMonth()">← Previous 30 Days</button>
        <input type="date" class="nav-date-input" id="dateInput" value="<?php echo $startDate; ?>" onchange="changeDate()">
        <button class="nav-btn" onclick="nextMonth()">Next 30 Days →</button>
        <span class="date-display">
            <?php echo date('M d', strtotime($startDate)); ?> - <?php echo date('M d, Y', strtotime($startDate . ' +29 days')); ?>
        </span>
    </div>

    <!-- Calendar Grid - WRAPPED IN SCROLL CONTAINER -->
    <div class="calendar-scroll-container">
        <button class="scroll-nav-btn" id="scrollLeftBtn" onclick="scrollCalendarLeft()" title="Scroll Left">←</button>
        <div class="calendar-wrapper" style="flex: 1; overflow-x: auto; overflow-y: auto;">
            <div class="calendar-grid">
            <!-- Header Row -->
            <div class="calendar-grid-header">
                <div class="grid-header-room">ROOMS</div>
                <?php foreach ($dates as $date): ?>
                <div class="grid-header-date<?php echo ($date === date('Y-m-d')) ? ' today' : ''; ?>">
                    <span class="grid-header-date-day">
                        <?php echo date('D', strtotime($date)); ?>
                    </span>
                    <span class="grid-header-date-num">
                        <?php echo date('d', strtotime($date)); ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Room Rows -->
            <?php 
            // Group rooms by type
            $roomsByType = [];
            foreach ($rooms as $room) {
                $typeKey = $room['type_name'];
                if (!isset($roomsByType[$typeKey])) {
                    $roomsByType[$typeKey] = [];
                }
                $roomsByType[$typeKey][] = $room;
            }
            
            // Display rooms grouped by type with type headers
            foreach ($roomsByType as $typeName => $typeRooms): 
                // Get base price from first room of this type
                $typePrice = $typeRooms[0]['base_price'] ?? 0;
            ?>
                <!-- Type Header Row -->
                <div class="grid-room-type-header">
                    📂 <?php echo htmlspecialchars($typeName); ?>
                </div>
                <?php foreach ($dates as $date): ?>
                <div style="background: rgba(99, 102, 241, 0.08); border-right: 2px solid rgba(99, 102, 241, 0.4); border-bottom: 1px solid rgba(255, 255, 255, 0.1); min-height: 30px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 800; color: rgba(99, 102, 241, 0.9);">
                    Rp<?php echo number_format($typePrice / 1000, 0, ',', '.'); ?>K
                </div>
                <?php endforeach; ?>
                
                <!-- Individual Rooms of This Type -->
                <?php foreach ($typeRooms as $room): ?>
                <div class="grid-room-label">
                    <span class="grid-room-number"><?php echo htmlspecialchars($room['room_number']); ?></span>
                </div>

                <?php foreach ($dates as $date): ?>
                <?php
                // Check for same-day turnover (checkout + checkin on same day)
                $hasTurnover = false;
                if (isset($bookingMatrix[$room['id']])) {
                    $checkouts = 0;
                    $checkins = 0;
                    foreach ($bookingMatrix[$room['id']] as $booking) {
                        $checkinDate = date('Y-m-d', strtotime($booking['check_in_date']));
                        $checkoutDate = date('Y-m-d', strtotime($booking['check_out_date']));
                        if ($checkoutDate === $date) $checkouts++;
                        if ($checkinDate === $date) $checkins++;
                    }
                    $hasTurnover = ($checkouts > 0 && $checkins > 0);
                }
                ?>
                <div class="grid-date-cell<?php echo ($date === date('Y-m-d')) ? ' today' : ''; ?><?php echo $hasTurnover ? ' has-turnover' : ''; ?>" 
                     data-date="<?php echo $date; ?>"
                     data-room-number="<?php echo htmlspecialchars($room['room_number']); ?>"
                     data-room-id="<?php echo $room['id']; ?>"
                     title="<?php echo htmlspecialchars($room['room_number']); ?> - <?php echo date('d M Y', strtotime($date)); ?><?php echo $hasTurnover ? ' (Turnover: CO + CI)' : ''; ?>">
                    <?php
                    // Find bookings for this room and date - CLOUDBED STYLE (bar from noon to noon)
                    if (isset($bookingMatrix[$room['id']])) {
                        foreach ($bookingMatrix[$room['id']] as $booking) {
                            $checkinDate = strtotime($booking['check_in_date']);
                            $checkoutDate = strtotime($booking['check_out_date']);
                            $currentDate = strtotime($date);
                            
                            // Only render bar on check-in date
                            if ($currentDate === $checkinDate) {
                                // Calculate nights (days between check-in and check-out)
                                $totalNights = ceil(($checkoutDate - $checkinDate) / 86400);
                                
                                // Calculate width: start from 50% of check-in cell, end at 50% of check-out cell
                                // Width = (nights × 100px) = full span from noon to noon
                                $barWidth = ($totalNights * 100); // 100px per day
                                
                                $statusClass = 'booking-' . str_replace('_', '-', $booking['status']);
                                
                                // Check if booking is past (check-out date is before today)
                                $today = strtotime(date('Y-m-d'));
                                $isPastBooking = ($checkoutDate < $today);
                                if ($isPastBooking) {
                                    $statusClass .= ' booking-past';
                                }
                                
                                // Determine color based on check-in status
                                $isCheckedIn = ($booking['status'] === 'checked_in');
                                $bookingColor = $isCheckedIn ? $checkedInColor : $defaultColor;
                                
                                // Add checkmark icon for checked-in guests
                                $statusIcon = $isCheckedIn ? '✓ ' : '';
                                
                                $guestName = htmlspecialchars(substr($booking['guest_name'] ?? 'Guest', 0, 12));
                                $bookingCode = htmlspecialchars($booking['booking_code']);
                                $shortCode = substr($bookingCode, 0, 8); // Show first 8 chars
                                $statusText = ucfirst(str_replace('_', ' ', $booking['status']));
                                ?>
                                <div class="booking-bar-container" style="left: 50%; width: <?php echo $barWidth; ?>px;">
                                    <div class="booking-bar <?php echo $statusClass; ?>" 
                                         style="background: linear-gradient(135deg, <?php echo $bookingColor['bg']; ?>, <?php echo $bookingColor['bg']; ?>dd) !important; border-right-color: <?php echo $bookingColor['bg']; ?>; border-left-color: <?php echo $bookingColor['bg']; ?>dd;"
                                         onclick="event.stopPropagation(); viewBooking(<?php echo $booking['id']; ?>, event);"
                                         title="<?php echo $statusIcon . $guestName; ?> (<?php echo $bookingCode; ?>) - <?php echo $statusText; ?><?php echo $isPastBooking ? ' [PAST]' : ''; ?>">
                                        <span><?php echo $statusIcon . $guestName; ?> • <?php echo $shortCode; ?></span>
                                    </div>
                                </div>
                                <?php
                                break; // Only one bar per booking
                            }
                        }
                    }
                    ?>
                </div>
                <?php endforeach; // End dates loop for each room ?>
                <?php endforeach; // End individual rooms loop ?>
            <?php endforeach; // End room types loop
            ?>
        </div>
    </div>
        <button class="scroll-nav-btn" id="scrollRightBtn" onclick="scrollCalendarRight()" title="Scroll Right">→</button>
    </div>

    <!-- Legend -->
    <div class="legend">
        <div class="legend-item">
            <div class="legend-color" style="background: linear-gradient(135deg, #3b82f6, #60a5fa);"></div>
            <span class="legend-label">📋 Booking (Confirmed/Pending)</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background: linear-gradient(135deg, #10b981, #34d399);"></div>
            <span class="legend-label">✓ Checked In (Active)</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background: linear-gradient(135deg, #9ca3af, #d1d5db); opacity: 0.4;"></div>
            <span class="legend-label">📭 Past Booking (History)</span>
        </div>
    </div>

</div>

<script>
function viewBooking(id, event) {
    event.preventDefault();
    event.stopPropagation();
    
    console.log('📋 Loading booking details:', id);
    
    // Fetch booking details via AJAX - use relative path from modules/frontdesk/
    fetch('../../api/get-booking-details.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showBookingDetailsModal(data.booking);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching booking:', error);
            alert('Failed to load booking details: ' + error.message);
        });
}

function showBookingDetailsModal(booking) {
    const modal = document.getElementById('bookingDetailsModal');
    
    // Populate modal with booking data
    document.getElementById('detailGuestName').textContent = booking.guest_name;
    document.getElementById('detailGuestPhone').textContent = booking.guest_phone || '-';
    document.getElementById('detailGuestEmail').textContent = booking.guest_email || '-';
    document.getElementById('detailRoomNumber').textContent = booking.room_number;
    document.getElementById('detailRoomType').textContent = booking.room_type;
    document.getElementById('detailCheckIn').textContent = formatDateFull(booking.check_in_date);
    document.getElementById('detailCheckOut').textContent = formatDateFull(booking.check_out_date);
    document.getElementById('detailNights').textContent = booking.total_nights + ' night(s)';
    document.getElementById('detailBookingCode').textContent = booking.booking_code;
    document.getElementById('detailPaymentStatus').textContent = booking.payment_status.toUpperCase();
    document.getElementById('detailPaymentStatus').className = 'status-badge status-' + booking.payment_status;
    document.getElementById('detailBookingStatus').textContent = booking.status.toUpperCase().replace('_', ' ');
    document.getElementById('detailBookingStatus').className = 'status-badge status-' + booking.status;
    document.getElementById('detailTotalPrice').textContent = 'Rp ' + formatNumberIDR(booking.final_price);
    
    // Set booking ID for action buttons
    modal.dataset.bookingId = booking.id;
    modal.dataset.bookingStatus = booking.status;
    modal.dataset.paymentStatus = booking.payment_status;
    
    // Show/hide action buttons based on status
    updateActionButtons(booking.status, booking.payment_status);
    
    modal.classList.add('active');
}

function closeBookingDetailsModal() {
    document.getElementById('bookingDetailsModal').classList.remove('active');
}

function formatDateFull(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
}

function formatNumberIDR(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

function updateActionButtons(status, paymentStatus) {
    const checkInBtn = document.getElementById('btnCheckIn');
    const checkOutBtn = document.getElementById('btnCheckOut');
    const moveBtn = document.getElementById('btnMove');
    const payBtn = document.getElementById('btnPay');
    
    // Show/hide buttons based on status
    if (status === 'confirmed' || status === 'pending') {
        checkInBtn.style.display = 'flex';
        checkOutBtn.style.display = 'none';
        moveBtn.style.display = 'flex';
    } else if (status === 'checked_in') {
        checkInBtn.style.display = 'none';
        checkOutBtn.style.display = 'flex';
        moveBtn.style.display = 'flex';
    } else {
        checkInBtn.style.display = 'none';
        checkOutBtn.style.display = 'none';
        moveBtn.style.display = 'none';
    }
    
    // Show Pay button if unpaid or partial
    if (paymentStatus === 'unpaid' || paymentStatus === 'partial') {
        payBtn.style.display = 'flex';
    } else {
        payBtn.style.display = 'none';
    }
}

function doCheckIn() {
    const modal = document.getElementById('bookingDetailsModal');
    const bookingId = modal.dataset.bookingId;
    const guestName = document.getElementById('detailGuestName').textContent;
    const roomNumber = document.getElementById('detailRoomNumber').textContent;
    
    if (confirm(`Check-in ${guestName} ke Room ${roomNumber} sekarang?`)) {
        // Show loading state
        const checkInBtn = document.getElementById('btnCheckIn');
        const originalText = checkInBtn.innerHTML;
        checkInBtn.innerHTML = '<span>⏳</span><span>Processing...</span>';
        checkInBtn.disabled = true;
        
        // Call check-in API
        fetch('<?php echo BASE_URL; ?>/api/checkin-guest.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            credentials: 'include', // Important: Send session cookies
            body: 'booking_id=' + bookingId
        })
        .then(response => {
            // Check if response is JSON
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
                // Reload page to reflect changes
                window.location.reload();
            } else {
                alert('❌ Error: ' + data.message);
                checkInBtn.innerHTML = originalText;
                checkInBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Terjadi kesalahan sistem: ' + error.message);
            checkInBtn.innerHTML = originalText;
            checkInBtn.disabled = false;
        });
    }
}

function doCheckOut() {
    const modal = document.getElementById('bookingDetailsModal');
    const bookingId = modal.dataset.bookingId;
    const guestName = document.getElementById('detailGuestName').textContent;
    const roomNumber = document.getElementById('detailRoomNumber').textContent;
    
    if (confirm(`Check-out ${guestName} dari Room ${roomNumber} sekarang?`)) {
        // Show loading state
        const checkOutBtn = document.getElementById('btnCheckOut');
        const originalText = checkOutBtn.innerHTML;
        checkOutBtn.innerHTML = '<span>⏳</span><span>Processing...</span>';
        checkOutBtn.disabled = true;
        
        // Call check-out API
        fetch('<?php echo BASE_URL; ?>/api/checkout-guest.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            credentials: 'include', // Important: Send session cookies
            body: 'booking_id=' + bookingId
        })
        .then(response => {
            // Check if response is JSON
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
                // Reload page to reflect changes
                window.location.reload();
            } else {
                alert('❌ Error: ' + data.message);
                checkOutBtn.innerHTML = originalText;
                checkOutBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Terjadi kesalahan sistem: ' + error.message);
            checkOutBtn.innerHTML = originalText;
            checkOutBtn.disabled = false;
        });
    }
}

function doMoveRoom() {
    const modal = document.getElementById('bookingDetailsModal');
    const bookingId = modal.dataset.bookingId;
    
    // TODO: Implement move room modal
    alert('Move room feature coming soon for booking #' + bookingId);
}

function doPayment() {
    const modal = document.getElementById('bookingDetailsModal');
    const bookingId = modal.dataset.bookingId;
    
    // TODO: Implement payment modal
    alert('Payment feature coming soon for booking #' + bookingId);
}

function changeDate() {
    const dateInput = document.getElementById('dateInput');
    window.location.search = '?start=' + dateInput.value;
}

function prevMonth() {
    const dateInput = document.getElementById('dateInput');
    const currentDate = new Date(dateInput.value);
    currentDate.setDate(currentDate.getDate() - 30);
    dateInput.value = currentDate.toISOString().split('T')[0];
    changeDate();
}

function nextMonth() {
    const dateInput = document.getElementById('dateInput');
    const currentDate = new Date(dateInput.value);
    currentDate.setDate(currentDate.getDate() + 30);
    dateInput.value = currentDate.toISOString().split('T')[0];
    changeDate();
}

// Modal Functions for Calendar Column Click
let selectedDate = null;
let selectedRoom = null;

function openColumnModal(date, roomNumber, roomId) {
    selectedDate = date;
    selectedRoom = { number: roomNumber, id: roomId };
    
    console.log('📅 Cell clicked - Date:', date, 'Room:', roomNumber, 'ID:', roomId);
    
    const modal = document.getElementById('columnModal');
    const dateInfo = document.getElementById('modalDateInfo');
    
    // Format date nicely
    const dateObj = new Date(date);
    const formattedDate = dateObj.toLocaleDateString('id-ID', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    dateInfo.innerHTML = `
        <span>Room ${roomNumber}</span><br>
        <strong>${formattedDate}</strong>
    `;
    
    modal.classList.add('active');
}

function closeColumnModal() {
    const modal = document.getElementById('columnModal');
    modal.classList.remove('active');
    selectedDate = null;
    selectedRoom = null;
}

function createNewReservation() {
    if (!selectedDate || !selectedRoom) {
        alert('Error: Date or Room not selected');
        return;
    }
    
    // Open reservasi page with pre-filled data
    const url = '<?php echo BASE_URL; ?>/modules/frontdesk/reservasi.php?date=' + selectedDate + '&room=' + selectedRoom.id;
    window.location.href = url;
}

function showReservationForm() {
    // IMPORTANT: Save date and room BEFORE closing modal (which resets them)
    const savedDate = selectedDate;
    const savedRoom = selectedRoom;
    
    console.log('📅 Opening reservation form with date:', savedDate, 'room:', savedRoom);
    
    closeColumnModal();
    
    const modal = document.getElementById('reservationModal');
    
    // Pre-fill form with SAVED data (not selectedDate which is now null)
    if (savedDate) {
        console.log('✅ Setting check-in date:', savedDate);
        
        // Set check-in date from selected date
        const checkInInput = document.getElementById('checkInDate');
        checkInInput.value = savedDate;
        
        // Set check-out date to next day
        const checkOut = new Date(savedDate);
        checkOut.setDate(checkOut.getDate() + 1);
        const checkOutDate = checkOut.toISOString().split('T')[0];
        
        const checkOutInput = document.getElementById('checkOutDate');
        checkOutInput.value = checkOutDate;
        checkOutInput.min = checkOutDate;
        
        console.log('Check-in:', checkInInput.value, 'Check-out:', checkOutInput.value);
    } else {
        console.error('❌ No savedDate available!');
    }
    
    if (savedRoom && savedRoom.id) {
        console.log('✅ Setting room:', savedRoom.id);
        document.getElementById('roomSelect').value = savedRoom.id;
        // Trigger change to update price
        document.getElementById('roomSelect').dispatchEvent(new Event('change'));
    }
    
    // Calculate initial nights
    calculateNights();
    
    // Show modal AFTER setting all values
    modal.classList.add('active');
}

function closeReservationModal() {
    const modal = document.getElementById('reservationModal');
    modal.classList.remove('active');
    document.getElementById('reservationForm').reset();
    selectedDate = null;
    selectedRoom = null;
}

function calculateNights() {
    const checkIn = document.getElementById('checkInDate').value;
    const checkOut = document.getElementById('checkOutDate').value;
    
    if (checkIn && checkOut) {
        const start = new Date(checkIn);
        const end = new Date(checkOut);
        const nights = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
        
        document.getElementById('totalNights').value = nights > 0 ? nights : 0;
        calculatePrice();
    }
}

function calculatePrice() {
    const roomPrice = parseFloat(document.getElementById('roomPrice').value) || 0;
    const nights = parseInt(document.getElementById('totalNights').value) || 0;
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    
    const total = roomPrice * nights;
    const final = total - discount;
    
    document.getElementById('totalPrice').textContent = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('discountAmount').textContent = '- Rp ' + discount.toLocaleString('id-ID');
    document.getElementById('finalPrice').textContent = 'Rp ' + final.toLocaleString('id-ID');
}

function submitReservation(event) {
    event.preventDefault();
    
    const formData = new FormData(document.getElementById('reservationForm'));
    
    // Add calculated values
    const roomPrice = parseFloat(document.getElementById('roomPrice').value) || 0;
    const nights = parseInt(document.getElementById('totalNights').value) || 0;
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    
    formData.append('total_price', roomPrice * nights);
    formData.append('final_price', (roomPrice * nights) - discount);
    formData.append('action', 'create_reservation');
    
    // Show loading
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '⏳ Saving...';
    submitBtn.disabled = true;
    
    // Submit via AJAX
    fetch('<?php echo BASE_URL; ?>/api/create-reservation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Reservation created successfully!\nBooking Code: ' + data.booking_code);
            closeReservationModal();
            location.reload(); // Reload to show new booking
        } else {
            alert('❌ Error: ' + (data.message || 'Failed to create reservation'));
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Connection error. Please try again.');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}


// ========================================
// CALENDAR SCROLL FUNCTIONS
// ========================================
function scrollCalendarLeft() {
    const wrapper = document.querySelector('.calendar-wrapper');
    if (!wrapper) return;
    
    const columnWidth = 100; // Width of each date column
    const currentScroll = wrapper.scrollLeft;
    const targetScroll = currentScroll - (columnWidth * 5); // Scroll 5 columns back
    
    wrapper.scrollTo({
        left: Math.max(0, targetScroll),
        behavior: 'smooth'
    });
    
    console.log('⬅️ Scroll Left');
}

function scrollCalendarRight() {
    const wrapper = document.querySelector('.calendar-wrapper');
    if (!wrapper) return;
    
    const columnWidth = 100; // Width of each date column
    const currentScroll = wrapper.scrollLeft;
    const maxScroll = wrapper.scrollWidth - wrapper.clientWidth;
    const targetScroll = currentScroll + (columnWidth * 5); // Scroll 5 columns forward
    
    wrapper.scrollTo({
        left: Math.min(maxScroll, targetScroll),
        behavior: 'smooth'
    });
    
    console.log('➡️ Scroll Right');
}


function blockRoom() {
    if (!selectedDate || !selectedRoom) {
        alert('Error: Date or Room not selected');
        return;
    }
    
    const reason = prompt('Alasan block room:', 'Maintenance');
    if (reason === null) return;
    
    // Here you would implement the actual block room functionality
    // For now, show a message
    alert('Room ' + selectedRoom.number + ' blocked for ' + selectedDate + '\nReason: ' + reason);
    closeColumnModal();
}

// Setup form event listeners (removed click-outside-to-close functionality)
document.addEventListener('DOMContentLoaded', function() {
    
    // Form event listeners
    const checkInDate = document.getElementById('checkInDate');
    const checkOutDate = document.getElementById('checkOutDate');
    const roomSelect = document.getElementById('roomSelect');
    const roomPriceInput = document.getElementById('roomPrice');
    const discountInput = document.getElementById('discount');
    
    if (checkInDate) checkInDate.addEventListener('change', calculateNights);
    if (checkOutDate) checkOutDate.addEventListener('change', calculateNights);
    if (roomPriceInput) roomPriceInput.addEventListener('input', calculatePrice);
    if (discountInput) discountInput.addEventListener('input', calculatePrice);
    
    if (roomSelect) {
        roomSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            if (price) {
                document.getElementById('roomPrice').value = price;
                calculatePrice();
            }
        });
    }
    
    // ========================================
    // DRAG SCROLL WITH FORCED SCROLLING
    // ========================================
    const wrapper = document.querySelector('.calendar-wrapper');
    const grid = document.querySelector('.calendar-grid');
    
    if (!wrapper || !grid) {
        console.error('❌ Elements not found');
        return;
    }
    
    // Force wrapper to be scrollable
    wrapper.style.overflowX = 'auto';
    grid.style.width = 'fit-content';
    
    console.log('✅ Drag initialized');
    console.log('Wrapper:', wrapper.clientWidth, 'Grid:', grid.offsetWidth, 'Scrollable:', grid.offsetWidth - wrapper.clientWidth);
    
    let isDown = false;
    let startX;
    let scrollLeft;
    let moved = false;
    
    wrapper.addEventListener('mousedown', (e) => {
        if (e.target.closest('.booking-bar')) return;
        
        isDown = true;
        moved = false;
        startX = e.pageX - wrapper.offsetLeft;
        scrollLeft = wrapper.scrollLeft;
        wrapper.style.cursor = 'grabbing';
        
        console.log('⬇️ START scrollLeft:', scrollLeft);
    });
    
    wrapper.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        
        const x = e.pageX - wrapper.offsetLeft;
        const walk = (x - startX); // Drag direction: move mouse right = scroll left, move left = scroll right
        const newScroll = scrollLeft - walk;
        
        wrapper.scrollLeft = newScroll;
        
        if (Math.abs(walk) > 5) moved = true;
        
        console.log('↔️ DRAG walk:', Math.round(walk), 'scroll:', wrapper.scrollLeft);
    });
    
    wrapper.addEventListener('mouseup', () => {
        if (!isDown) return;
        
        isDown = false;
        wrapper.style.cursor = 'grab';
        
        // SNAP TO NEAREST COLUMN
        const columnWidth = 100; // Width of each date column
        const roomLabelWidth = 110; // Width of room label column
        const currentScroll = wrapper.scrollLeft;
        
        // Calculate which column we're closest to
        const scrollPosition = currentScroll;
        const columnIndex = Math.round(scrollPosition / columnWidth);
        const targetScroll = columnIndex * columnWidth;
        
        // Smooth snap animation
        wrapper.scrollTo({
            left: targetScroll,
            behavior: 'smooth'
        });
        
        console.log('⬆️ END moved:', moved, 'snap to column:', columnIndex, 'scroll:', targetScroll);
        setTimeout(() => { moved = false; }, 150);
    });
    
    wrapper.addEventListener('mouseleave', () => {
        isDown = false;
        wrapper.style.cursor = 'grab';
    });
    
    wrapper.addEventListener('click', (e) => {
        if (moved) {
            console.log('🚫 BLOCKED - Drag detected');
            e.preventDefault();
            e.stopPropagation();
            return;
        }
        
        // Check if clicked on booking bar - don't open Actions modal
        if (e.target.closest('.booking-bar')) {
            console.log('📋 Booking bar clicked - ignoring cell click');
            return;
        }
        
        const cell = e.target.closest('.grid-date-cell');
        if (!cell) return;
        
        console.log('📅 Cell clicked - opening Actions modal');
        openColumnModal(cell.dataset.date, cell.dataset.roomNumber, cell.dataset.roomId);
    });
});
</script>

<!-- Modal Popup - Actions -->
<div id="columnModal" class="modal-overlay">
    <div class="modal-content">
        <button class="modal-close" onclick="closeColumnModal()">×</button>
        
        <div class="modal-header">
            <h2>📅 Actions</h2>
            <p>Select an action for this date</p>
        </div>
        
        <div class="modal-date-info" id="modalDateInfo">
            <!-- Will be filled by JavaScript -->
        </div>
        
        <div class="modal-actions">
            <button class="modal-btn modal-btn-primary" onclick="showReservationForm()">
                <span>➕</span>
                <span>New Reservation</span>
            </button>
            <button class="modal-btn modal-btn-secondary" onclick="blockRoom()">
                <span>🚫</span>
                <span>Block Room</span>
            </button>
        </div>
    </div>
</div>

<!-- Modal Popup - Reservation Form -->
<div id="reservationModal" class="modal-overlay">
    <div class="modal-content modal-content-large">
        <button class="modal-close" onclick="closeReservationModal()">×</button>
        
        <div class="modal-header">
            <h2>➕ New Reservation</h2>
            <p>Fill in the guest and booking details</p>
        </div>
        
        <form id="reservationForm" onsubmit="submitReservation(event)">
            <div class="form-grid">
                <!-- Guest Information -->
                <div class="form-section">
                    <h3>👤 Guest Information</h3>
                    <div class="form-group">
                        <label>Guest Name *</label>
                        <input type="text" id="guestName" name="guest_name" required placeholder="Enter guest name">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" id="guestPhone" name="guest_phone" placeholder="+62">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" id="guestEmail" name="guest_email" placeholder="guest@email.com">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>ID Number</label>
                        <input type="text" id="guestId" name="guest_id_number" placeholder="KTP/Passport">
                    </div>
                </div>

                <!-- Booking Details -->
                <div class="form-section">
                    <h3>📅 Booking Details</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Check-in Date *</label>
                            <input type="date" id="checkInDate" name="check_in_date" required>
                        </div>
                        <div class="form-group">
                            <label>Check-out Date *</label>
                            <input type="date" id="checkOutDate" name="check_out_date" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Room *</label>
                            <select id="roomSelect" name="room_id" required>
                                <option value="">Select Room</option>
                                <?php foreach ($rooms as $room): ?>
                                <option value="<?php echo $room['id']; ?>" data-price="<?php echo $room['base_price']; ?>">
                                    Room <?php echo $room['room_number']; ?> - <?php echo $room['type_name']; ?> (Rp <?php echo number_format($room['base_price'], 0, ',', '.'); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Total Nights</label>
                            <input type="number" id="totalNights" name="total_nights" readonly value="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Booking Source *</label>
                        <select id="bookingSource" name="booking_source" required>
                            <option value="walk_in">🚶 Walk-in (0% fee)</option>
                            <option value="phone">📞 Phone Booking (0% fee)</option>
                            <option value="online">💻 Direct Online (0% fee)</option>
                            <option value="agoda">🏨 Agoda (15% fee)</option>
                            <option value="booking">📱 Booking.com (12% fee)</option>
                            <option value="tiket">✈️ Tiket.com (10% fee)</option>
                            <option value="airbnb">🏠 Airbnb (3% fee)</option>
                            <option value="ota">🌐 OTA Lainnya (10% fee)</option>
                        </select>
                    </div>
                </div>

                <!-- Price Calculation -->
                <div class="form-section">
                    <h3>💰 Price Details</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Room Price/Night *</label>
                            <input type="number" id="roomPrice" name="room_price" required placeholder="0" min="0">
                        </div>
                        <div class="form-group">
                            <label>Discount (Rp)</label>
                            <input type="number" id="discount" name="discount" value="0" min="0">
                        </div>
                    </div>
                    <div class="price-summary">
                        <div class="price-row">
                            <span>Room Price × Nights:</span>
                            <strong id="totalPrice">Rp 0</strong>
                        </div>
                        <div class="price-row">
                            <span>Discount:</span>
                            <strong id="discountAmount">Rp 0</strong>
                        </div>
                        <div class="price-row price-row-total">
                            <span>Final Price:</span>
                            <strong id="finalPrice">Rp 0</strong>
                        </div>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="form-section">
                    <h3>📝 Additional Information</h3>
                    <div class="form-group">
                        <label>Special Request</label>
                        <textarea id="specialRequest" name="special_request" rows="3" placeholder="Any special requests..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Payment Status</label>
                        <select id="paymentStatus" name="payment_status">
                            <option value="unpaid">Unpaid</option>
                            <option value="partial">Partial</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Paid Amount (Rp)</label>
                        <input type="number" id="paidAmount" name="paid_amount" value="0" min="0">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeReservationModal()">Cancel</button>
                <button type="submit" class="btn-primary">💾 Save Reservation</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Popup - Booking Details -->
<div id="bookingDetailsModal" class="modal-overlay">
    <div class="modal-content modal-content-medium">
        <button class="modal-close" onclick="closeBookingDetailsModal()">×</button>
        
        <div class="modal-header">
            <h2>📋 Booking Details</h2>
            <p id="detailBookingCode" style="font-weight: 700; color: #6366f1;"></p>
        </div>
        
        <div class="booking-details-content">
            <!-- Guest Information -->
            <div class="detail-section">
                <h3>👤 Guest Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value" id="detailGuestName">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value" id="detailGuestPhone">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value" id="detailGuestEmail">-</span>
                    </div>
                </div>
            </div>
            
            <!-- Room Information -->
            <div class="detail-section">
                <h3>🏨 Room Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Room Number:</span>
                        <span class="detail-value" id="detailRoomNumber">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Room Type:</span>
                        <span class="detail-value" id="detailRoomType">-</span>
                    </div>
                </div>
            </div>
            
            <!-- Booking Information -->
            <div class="detail-section">
                <h3>📅 Booking Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Check-in:</span>
                        <span class="detail-value" id="detailCheckIn">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Check-out:</span>
                        <span class="detail-value" id="detailCheckOut">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Total Nights:</span>
                        <span class="detail-value" id="detailNights">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Booking Status:</span>
                        <span class="detail-value" id="detailBookingStatus">-</span>
                    </div>
                </div>
            </div>
            
            <!-- Payment Information -->
            <div class="detail-section">
                <h3>💰 Payment Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Payment Status:</span>
                        <span class="detail-value" id="detailPaymentStatus">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Total Amount:</span>
                        <span class="detail-value" id="detailTotalPrice" style="font-weight: 800; color: #10b981;">-</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="booking-actions">
            <button id="btnCheckIn" class="btn-action btn-checkin" onclick="doCheckIn()">
                <span>✓</span>
                <span>Check In</span>
            </button>
            <button id="btnCheckOut" class="btn-action btn-checkout" onclick="doCheckOut()">
                <span>→</span>
                <span>Check Out</span>
            </button>
            <button id="btnPay" class="btn-action btn-pay" onclick="doPayment()">
                <span>💳</span>
                <span>Pay</span>
            </button>
            <button id="btnMove" class="btn-action btn-move" onclick="doMoveRoom()">
                <span>↔</span>
                <span>Move Room</span>
            </button>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>


