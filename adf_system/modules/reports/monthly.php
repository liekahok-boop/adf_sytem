<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';
require_once '../../includes/report_helper.php';

// Check if user is logged in
$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();
$pageTitle = 'Laporan Bulanan';

// Get filter parameters
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$division_id = isset($_GET['division_id']) ? (int)$_GET['division_id'] : 0;

// Get all divisions for filter
$divisions = $db->fetchAll("SELECT * FROM divisions ORDER BY division_name");

// Build WHERE clause
$whereConditions = ["YEAR(cb.transaction_date) = :year"];
$params = ['year' => $year];

if ($division_id > 0) {
    $whereConditions[] = "cb.division_id = :division_id";
    $params['division_id'] = $division_id;
}

$whereClause = implode(' AND ', $whereConditions);

// Get monthly summary
$monthlySummary = $db->fetchAll("
    SELECT 
        MONTH(cb.transaction_date) as month,
        COALESCE(SUM(CASE WHEN cb.transaction_type = 'income' THEN cb.amount ELSE 0 END), 0) as total_income,
        COALESCE(SUM(CASE WHEN cb.transaction_type = 'expense' THEN cb.amount ELSE 0 END), 0) as total_expense,
        COALESCE(SUM(CASE WHEN cb.transaction_type = 'income' THEN cb.amount ELSE 0 END), 0) - 
        COALESCE(SUM(CASE WHEN cb.transaction_type = 'expense' THEN cb.amount ELSE 0 END), 0) as net_balance,
        COUNT(*) as transaction_count
    FROM cash_book cb
    WHERE $whereClause
    GROUP BY MONTH(cb.transaction_date)
    ORDER BY month
", $params);

// Calculate totals
$grandIncome = 0;
$grandExpense = 0;
$grandNet = 0;
$grandTransactions = 0;

foreach ($monthlySummary as $month) {
    $grandIncome += $month['total_income'];
    $grandExpense += $month['total_expense'];
    $grandNet += $month['net_balance'];
    $grandTransactions += $month['transaction_count'];
}

// Month names in Indonesian
$monthNames = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

include '../../includes/header.php';
?>

<!-- Filter Section -->
<div class="card" style="margin-bottom: 1.25rem;">
    <form method="GET" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; align-items: end;">
        <div class="form-group" style="margin: 0;">
            <label class="form-label">Tahun</label>
            <select name="year" class="form-control" required>
                <?php for($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>>
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

<!-- Monthly Chart -->
<div class="card" style="margin-bottom: 1.25rem;">
    <h3 style="font-size: 0.95rem; color: var(--text-primary); font-weight: 600; margin-bottom: 1rem;">
        📊 Grafik Bulanan <?php echo $year; ?>
    </h3>
    <canvas id="monthlyChart" style="max-height: 300px;"></canvas>
</div>

<!-- Monthly Summary Table -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0 0.5rem 0; border-bottom: 1px solid var(--bg-tertiary); margin-bottom: 1rem;">
        <h3 style="font-size: 0.95rem; color: var(--text-primary); font-weight: 600;">
            📊 Ringkasan Per Bulan
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
    
    <?php if (empty($monthlySummary)): ?>
        <p style="text-align: center; padding: 2rem; color: var(--text-muted);">
            Tidak ada data untuk tahun yang dipilih
        </p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table" id="monthlyTable">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th class="text-right">Pemasukan</th>
                        <th class="text-right">Pengeluaran</th>
                        <th class="text-right">Net Balance</th>
                        <th class="text-center">Transaksi</th>
                        <th class="text-center">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($monthlySummary as $month): ?>
                        <tr>
                            <td style="font-weight: 600; font-size: 0.813rem;">
                                <?php echo $monthNames[$month['month']]; ?> <?php echo $year; ?>
                            </td>
                            <td class="text-right" style="font-weight: 700; font-size: 0.875rem; color: var(--success);">
                                <?php echo formatCurrency($month['total_income']); ?>
                            </td>
                            <td class="text-right" style="font-weight: 700; font-size: 0.875rem; color: var(--danger);">
                                <?php echo formatCurrency($month['total_expense']); ?>
                            </td>
                            <td class="text-right" style="font-weight: 800; font-size: 0.938rem; color: <?php echo $month['net_balance'] >= 0 ? 'var(--success)' : 'var(--danger)'; ?>;">
                                <?php echo formatCurrency($month['net_balance']); ?>
                            </td>
                            <td class="text-center" style="font-size: 0.813rem;">
                                <span style="padding: 0.25rem 0.5rem; background: var(--bg-tertiary); border-radius: 4px;">
                                    <?php echo $month['transaction_count']; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="daily.php?start_date=<?php echo $year . '-' . sprintf('%02d', $month['month']) . '-01'; ?>&end_date=<?php echo date('Y-m-t', strtotime($year . '-' . $month['month'] . '-01')); ?><?php echo $division_id > 0 ? '&division_id=' . $division_id : ''; ?>" 
                                   class="btn btn-primary btn-sm">
                                    <i data-feather="eye" style="width: 14px; height: 14px;"></i> Lihat
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background: var(--bg-tertiary); font-weight: 800;">
                        <td>TOTAL</td>
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
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    feather.replace();
    
    // Prepare chart data
    const chartLabels = <?php echo json_encode(array_map(function($m) use ($monthNames) { return $monthNames[$m['month']]; }, $monthlySummary)); ?>;
    const incomeData = <?php echo json_encode(array_column($monthlySummary, 'total_income')); ?>;
    const expenseData = <?php echo json_encode(array_column($monthlySummary, 'total_expense')); ?>;
    
    // Create chart
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [
                {
                    label: 'Pemasukan',
                    data: incomeData,
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Pengeluaran',
                    data: expenseData,
                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                    borderColor: 'rgba(239, 68, 68, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
    
    // Export to Excel function
    function exportToExcel() {
        const table = document.getElementById('monthlyTable');
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
        a.download = 'laporan-bulanan-<?php echo $year; ?>.xls';
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
        canvas {
            max-width: 100%;
        }
    }
</style>

<?php include '../../includes/footer.php'; ?>
