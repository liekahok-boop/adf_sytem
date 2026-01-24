<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Check if user is logged in
$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();
$pageTitle = 'Laporan Harian';

// Get filter parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$division_id = isset($_GET['division_id']) ? (int)$_GET['division_id'] : 0;

// Get all divisions for filter
$divisions = $db->fetchAll("SELECT * FROM divisions ORDER BY division_name");

// Build WHERE clause
$whereConditions = ["cb.transaction_date BETWEEN :start_date AND :end_date"];
$params = [
    'start_date' => $start_date,
    'end_date' => $end_date
];

if ($division_id > 0) {
    $whereConditions[] = "cb.division_id = :division_id";
    $params['division_id'] = $division_id;
}

$whereClause = implode(' AND ', $whereConditions);

// Get daily summary
$dailySummary = $db->fetchAll("
    SELECT 
        DATE(cb.transaction_date) as date,
        COALESCE(SUM(CASE WHEN cb.transaction_type = 'income' THEN cb.amount ELSE 0 END), 0) as total_income,
        COALESCE(SUM(CASE WHEN cb.transaction_type = 'expense' THEN cb.amount ELSE 0 END), 0) as total_expense,
        COALESCE(SUM(CASE WHEN cb.transaction_type = 'income' THEN cb.amount ELSE 0 END), 0) - 
        COALESCE(SUM(CASE WHEN cb.transaction_type = 'expense' THEN cb.amount ELSE 0 END), 0) as net_balance,
        COUNT(*) as transaction_count
    FROM cash_book cb
    WHERE $whereClause
    GROUP BY DATE(cb.transaction_date)
    ORDER BY date DESC
", $params);

// Calculate totals
$grandIncome = 0;
$grandExpense = 0;
$grandNet = 0;
$grandTransactions = 0;

foreach ($dailySummary as $day) {
    $grandIncome += $day['total_income'];
    $grandExpense += $day['total_expense'];
    $grandNet += $day['net_balance'];
    $grandTransactions += $day['transaction_count'];
}

include '../../includes/header.php';
?>

<!-- Filter Section -->
<div class="card" style="margin-bottom: 1.25rem;">
    <form method="GET" style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 1rem; align-items: end;">
        <div class="form-group" style="margin: 0;">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>" required>
        </div>
        
        <div class="form-group" style="margin: 0;">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>" required>
        </div>
        
        <div class="form-group" style="margin: 0;">
            <label class="form-label">Divisi</label>
            <select name="division_id" class="form-control">
                <option value="0">-- Semua Divisi --</option>
                <?php foreach ($divisions as $div): ?>
                    <option value="<?php echo $div['id']; ?>" <?php echo $division_id == $div['id'] ? 'selected' : ''; ?>>
                        <?php echo $div['division_name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary" style="height: 42px;">
            <i data-feather="search" style="width: 16px; height: 16px;"></i> Cari
        </button>
    </form>
</div>

<!-- Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
    <div class="card" style="padding: 1rem;">
        <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.5rem;">Total Pemasukan</div>
        <div style="font-size: 1.5rem; font-weight: 800; color: var(--success);">
            <?php echo formatCurrency($grandIncome); ?>
        </div>
    </div>
    
    <div class="card" style="padding: 1rem;">
        <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.5rem;">Total Pengeluaran</div>
        <div style="font-size: 1.5rem; font-weight: 800; color: var(--danger);">
            <?php echo formatCurrency($grandExpense); ?>
        </div>
    </div>
    
    <div class="card" style="padding: 1rem;">
        <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.5rem;">Net Balance</div>
        <div style="font-size: 1.5rem; font-weight: 800; color: <?php echo $grandNet >= 0 ? 'var(--success)' : 'var(--danger)'; ?>;">
            <?php echo formatCurrency($grandNet); ?>
        </div>
    </div>
    
    <div class="card" style="padding: 1rem;">
        <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.5rem;">Total Transaksi</div>
        <div style="font-size: 1.5rem; font-weight: 800; color: var(--primary-color);">
            <?php echo number_format($grandTransactions); ?>
        </div>
    </div>
</div>

<!-- Daily Summary Table -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0 0.5rem 0; border-bottom: 1px solid var(--bg-tertiary); margin-bottom: 1rem;">
        <h3 style="font-size: 0.95rem; color: var(--text-primary); font-weight: 600;">
            📊 Ringkasan Per Hari
        </h3>
        <div style="display: flex; gap: 0.5rem;">
            <button onclick="window.print()" class="btn btn-secondary btn-sm">
                <i data-feather="printer" style="width: 14px; height: 14px;"></i> Print
            </button>
            <button onclick="exportToExcel()" class="btn btn-success btn-sm">
                <i data-feather="download" style="width: 14px; height: 14px;"></i> Export Excel
            </button>
        </div>
    </div>
    
    <?php if (empty($dailySummary)): ?>
        <p style="text-align: center; padding: 2rem; color: var(--text-muted);">
            Tidak ada data untuk periode yang dipilih
        </p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table" id="dailyTable">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Hari</th>
                        <th class="text-right">Pemasukan</th>
                        <th class="text-right">Pengeluaran</th>
                        <th class="text-right">Net Balance</th>
                        <th class="text-center">Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dailySummary as $day): ?>
                        <tr>
                            <td style="font-weight: 600; font-size: 0.813rem;">
                                <?php echo date('d/m/Y', strtotime($day['date'])); ?>
                            </td>
                            <td style="font-size: 0.813rem;">
                                <?php 
                                $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                echo $dayNames[date('w', strtotime($day['date']))]; 
                                ?>
                            </td>
                            <td class="text-right" style="font-weight: 700; font-size: 0.875rem; color: var(--success);">
                                <?php echo formatCurrency($day['total_income']); ?>
                            </td>
                            <td class="text-right" style="font-weight: 700; font-size: 0.875rem; color: var(--danger);">
                                <?php echo formatCurrency($day['total_expense']); ?>
                            </td>
                            <td class="text-right" style="font-weight: 800; font-size: 0.938rem; color: <?php echo $day['net_balance'] >= 0 ? 'var(--success)' : 'var(--danger)'; ?>;">
                                <?php echo formatCurrency($day['net_balance']); ?>
                            </td>
                            <td class="text-center" style="font-size: 0.813rem;">
                                <span style="padding: 0.25rem 0.5rem; background: var(--bg-tertiary); border-radius: 4px;">
                                    <?php echo $day['transaction_count']; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background: var(--bg-tertiary); font-weight: 800;">
                        <td colspan="2">TOTAL</td>
                        <td class="text-right" style="color: var(--success);">
                            <?php echo formatCurrency($grandIncome); ?>
                        </td>
                        <td class="text-right" style="color: var(--danger);">
                            <?php echo formatCurrency($grandExpense); ?>
                        </td>
                        <td class="text-right" style="color: <?php echo $grandNet >= 0 ? 'var(--success)' : 'var(--danger)'; ?>;">
                            <?php echo formatCurrency($grandNet); ?>
                        </td>
                        <td class="text-center">
                            <?php echo number_format($grandTransactions); ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
    feather.replace();
    
    // Export to Excel function
    function exportToExcel() {
        const table = document.getElementById('dailyTable');
        let html = '<table>';
        
        // Get table HTML
        html += table.outerHTML;
        html += '</table>';
        
        // Create downloadable file
        const blob = new Blob([html], {
            type: 'application/vnd.ms-excel'
        });
        
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'laporan-harian-<?php echo date('Y-m-d'); ?>.xls';
        a.click();
        window.URL.revokeObjectURL(url);
    }
</script>

<style>
    @media print {
        .sidebar, .top-bar .user-info, .btn, .form-control, form {
            display: none !important;
        }
        .main-content {
            margin-left: 0;
            padding: 1rem;
        }
        .card {
            box-shadow: none;
            border: 1px solid #ccc;
        }
    }
</style>

<?php include '../../includes/footer.php'; ?>
