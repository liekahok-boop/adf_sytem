<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();
$pageTitle = 'Laporan Tahunan';

// Get filter parameters
$start_year = isset($_GET['start_year']) ? (int)$_GET['start_year'] : date('Y') - 4;
$end_year = isset($_GET['end_year']) ? (int)$_GET['end_year'] : date('Y');
$division_id = isset($_GET['division_id']) ? (int)$_GET['division_id'] : 0;

// Get all divisions
$divisions = $db->fetchAll("SELECT * FROM divisions ORDER BY division_name");

// Build WHERE clause
$whereConditions = ["YEAR(cb.transaction_date) BETWEEN :start_year AND :end_year"];
$params = ['start_year' => $start_year, 'end_year' => $end_year];

if ($division_id > 0) {
    $whereConditions[] = "cb.division_id = :division_id";
    $params['division_id'] = $division_id;
}

$whereClause = implode(' AND ', $whereConditions);

// Get yearly summary
$yearlySummary = $db->fetchAll("
    SELECT 
        YEAR(cb.transaction_date) as year,
        COALESCE(SUM(CASE WHEN cb.transaction_type = 'income' THEN cb.amount ELSE 0 END), 0) as total_income,
        COALESCE(SUM(CASE WHEN cb.transaction_type = 'expense' THEN cb.amount ELSE 0 END), 0) as total_expense,
        COALESCE(SUM(CASE WHEN cb.transaction_type = 'income' THEN cb.amount ELSE 0 END), 0) - 
        COALESCE(SUM(CASE WHEN cb.transaction_type = 'expense' THEN cb.amount ELSE 0 END), 0) as net_balance,
        COUNT(*) as transaction_count
    FROM cash_book cb
    WHERE $whereClause
    GROUP BY YEAR(cb.transaction_date)
    ORDER BY year
", $params);

// Calculate totals
$grandIncome = 0;
$grandExpense = 0;
$grandNet = 0;
$grandTransactions = 0;

foreach ($yearlySummary as $yearly) {
    $grandIncome += $yearly['total_income'];
    $grandExpense += $yearly['total_expense'];
    $grandNet += $yearly['net_balance'];
    $grandTransactions += $yearly['transaction_count'];
}

include '../../includes/header.php';
?>

<!-- Filter Section -->
<div class="card" style="margin-bottom: 1.25rem;">
    <form method="GET" style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 1rem; align-items: end;">
        <div class="form-group" style="margin: 0;">
            <label class="form-label">Tahun Mulai</label>
            <select name="start_year" class="form-control" required>
                <?php for($y = date('Y'); $y >= 2020; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $start_year == $y ? 'selected' : ''; ?>>
                        <?php echo $y; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        
        <div class="form-group" style="margin: 0;">
            <label class="form-label">Tahun Akhir</label>
            <select name="end_year" class="form-control" required>
                <?php for($y = date('Y'); $y >= 2020; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $end_year == $y ? 'selected' : ''; ?>>
                        <?php echo $y; ?>
                    </option>
                <?php endfor; ?>
            </select>
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
            <i data-feather="search" style="width: 16px; height: 16px;"></i> Filter
        </button>
    </form>
</div>

<!-- Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
    <div class="card" style="padding: 1.25rem; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));">
        <div style="display: flex; align-items: center; gap: 0.875rem;">
            <div style="width: 48px; height: 48px; border-radius: var(--radius-lg); background: var(--success); display: flex; align-items: center; justify-content: center;">
                <i data-feather="trending-up" style="width: 24px; height: 24px; color: white;"></i>
            </div>
            <div style="flex: 1;">
                <div style="font-size: 0.813rem; color: var(--text-muted); margin-bottom: 0.25rem;">Total Pemasukan</div>
                <div style="font-size: 1.375rem; font-weight: 700; color: var(--success);">
                    Rp <?php echo number_format($grandIncome, 0, ',', '.'); ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card" style="padding: 1.25rem; background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.05));">
        <div style="display: flex; align-items: center; gap: 0.875rem;">
            <div style="width: 48px; height: 48px; border-radius: var(--radius-lg); background: var(--danger); display: flex; align-items: center; justify-content: center;">
                <i data-feather="trending-down" style="width: 24px; height: 24px; color: white;"></i>
            </div>
            <div style="flex: 1;">
                <div style="font-size: 0.813rem; color: var(--text-muted); margin-bottom: 0.25rem;">Total Pengeluaran</div>
                <div style="font-size: 1.375rem; font-weight: 700; color: var(--danger);">
                    Rp <?php echo number_format($grandExpense, 0, ',', '.'); ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card" style="padding: 1.25rem; background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(99, 102, 241, 0.05));">
        <div style="display: flex; align-items: center; gap: 0.875rem;">
            <div style="width: 48px; height: 48px; border-radius: var(--radius-lg); background: var(--primary-color); display: flex; align-items: center; justify-content: center;">
                <i data-feather="activity" style="width: 24px; height: 24px; color: white;"></i>
            </div>
            <div style="flex: 1;">
                <div style="font-size: 0.813rem; color: var(--text-muted); margin-bottom: 0.25rem;">Saldo Bersih</div>
                <div style="font-size: 1.375rem; font-weight: 700; color: <?php echo $grandNet >= 0 ? 'var(--success)' : 'var(--danger)'; ?>;">
                    Rp <?php echo number_format($grandNet, 0, ',', '.'); ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card" style="padding: 1.25rem; background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.05));">
        <div style="display: flex; align-items: center; gap: 0.875rem;">
            <div style="width: 48px; height: 48px; border-radius: var(--radius-lg); background: var(--warning); display: flex; align-items: center; justify-content: center;">
                <i data-feather="list" style="width: 24px; height: 24px; color: white;"></i>
            </div>
            <div style="flex: 1;">
                <div style="font-size: 0.813rem; color: var(--text-muted); margin-bottom: 0.25rem;">Total Transaksi</div>
                <div style="font-size: 1.375rem; font-weight: 700; color: var(--text-primary);">
                    <?php echo number_format($grandTransactions, 0, ',', '.'); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
        <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin: 0;">
            Rekap Per Tahun (<?php echo $start_year; ?> - <?php echo $end_year; ?>)
        </h3>
        <div style="display: flex; gap: 0.5rem;">
            <button onclick="window.print()" class="btn btn-secondary btn-sm">
                <i data-feather="printer" style="width: 16px; height: 16px;"></i> Cetak
            </button>
            <button onclick="exportToPDF()" class="btn btn-primary btn-sm">
                <i data-feather="download" style="width: 16px; height: 16px;"></i> Export PDF
            </button>
        </div>
    </div>
    
    <?php if (empty($yearlySummary)): ?>
        <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
            <i data-feather="inbox" style="width: 48px; height: 48px; margin-bottom: 1rem;"></i>
            <p>Tidak ada data untuk tahun yang dipilih</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 10%;">Tahun</th>
                        <th style="text-align: right;">Pemasukan</th>
                        <th style="text-align: right;">Pengeluaran</th>
                        <th style="text-align: right;">Saldo Bersih</th>
                        <th style="text-align: center; width: 15%;">Jumlah Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($yearlySummary as $yearly): ?>
                        <tr>
                            <td style="font-weight: 600; color: var(--text-primary);">
                                <?php echo $yearly['year']; ?>
                            </td>
                            <td style="text-align: right; color: var(--success); font-weight: 600;">
                                Rp <?php echo number_format($yearly['total_income'], 0, ',', '.'); ?>
                            </td>
                            <td style="text-align: right; color: var(--danger); font-weight: 600;">
                                Rp <?php echo number_format($yearly['total_expense'], 0, ',', '.'); ?>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: <?php echo $yearly['net_balance'] >= 0 ? 'var(--success)' : 'var(--danger)'; ?>;">
                                Rp <?php echo number_format($yearly['net_balance'], 0, ',', '.'); ?>
                            </td>
                            <td style="text-align: center; color: var(--text-muted);">
                                <?php echo number_format($yearly['transaction_count'], 0, ',', '.'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot style="border-top: 2px solid var(--bg-tertiary);">
                    <tr style="background: var(--bg-tertiary);">
                        <td style="font-weight: 700; color: var(--text-primary);">TOTAL</td>
                        <td style="text-align: right; color: var(--success); font-weight: 700;">
                            Rp <?php echo number_format($grandIncome, 0, ',', '.'); ?>
                        </td>
                        <td style="text-align: right; color: var(--danger); font-weight: 700;">
                            Rp <?php echo number_format($grandExpense, 0, ',', '.'); ?>
                        </td>
                        <td style="text-align: right; font-weight: 800; color: <?php echo $grandNet >= 0 ? 'var(--success)' : 'var(--danger)'; ?>;">
                            Rp <?php echo number_format($grandNet, 0, ',', '.'); ?>
                        </td>
                        <td style="text-align: center; font-weight: 700;">
                            <?php echo number_format($grandTransactions, 0, ',', '.'); ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
    feather.replace();
    
    function exportToPDF() {
        const params = new URLSearchParams(window.location.search);
        params.set('export', 'pdf');
        window.open('yearly-pdf.php?' + params.toString(), '_blank');
    }
</script>

<?php include '../../includes/footer.php'; ?>
