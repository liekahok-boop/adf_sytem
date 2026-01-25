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

// Get company info for print
$company = getCompanyInfo();
$dateRangeText = date('d M Y', strtotime($start_date)) . ' - ' . date('d M Y', strtotime($end_date));
?>

<!-- Hidden Print Content -->
<div style="display: none;" class="no-print"></div>

<script>
window.addEventListener('beforeprint', function() {
    // Show print-specific content
    var printContent = document.getElementById('printContent');
    if (printContent) {
        printContent.style.display = 'block';
    }
});
</script>

<!-- Main Content for Screen -->
<div id="screenContent">

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
        .sidebar, .top-bar .user-info, .btn, .form-control, form, .filter-section, .no-print {
            display: none !important;
        }
        .main-content {
            margin-left: 0;
            padding: 20mm;
        }
        .card {
            box-shadow: none;
            border: 1px solid #ddd;
            page-break-inside: avoid;
        }
        body {
            font-family: "Segoe UI", "Trebuchet MS", sans-serif;
        }
        table {
            page-break-inside: avoid;
        }
        tr {
            page-break-inside: avoid;
        }
    }
    
    .report-header {
        display: flex;
        gap: 1.5rem;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 3px solid var(--primary-color);
        align-items: flex-start;
    }
    
    .report-header-logo {
        flex-shrink: 0;
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bg-secondary);
        border-radius: 8px;
        font-size: 48px;
    }
    
    .report-header-info {
        flex: 1;
    }
    
    .report-header-title {
        font-size: 28px;
        font-weight: 900;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
        letter-spacing: -0.5px;
    }
    
    .report-header-company {
        font-size: 11px;
        color: #666;
        line-height: 1.6;
    }
    
    .report-meta {
        text-align: right;
        min-width: 200px;
    }
    
    .report-meta-item {
        font-size: 12px;
        color: #666;
        margin-bottom: 1rem;
    }
    
    .report-footer {
        margin-top: 2rem;
        padding-top: 1rem;
        border-top: 1px solid #ddd;
        text-align: center;
        font-size: 10px;
        color: #999;
    }
    
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .summary-card-print {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-color) 100%);
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
        page-break-inside: avoid;
    }
</style>

<!-- Print-Only Report Section -->
<div id="printContent" style="display: none;">
    <div style="width: 210mm; min-height: 297mm; margin: 0 auto; padding: 20mm; background: white;">
        <?php echo generateReportHeader('LAPORAN HARIAN', htmlspecialchars($division_id > 0 ? end($divisions)['division_name'] : 'Semua Divisi'), $dateRangeText); ?>
        
        <!-- Summary Cards for Print -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.8rem; margin-bottom: 1.5rem;">
            <?php
            echo generateSummaryCard('💰 TOTAL PEMASUKAN', formatCurrency($grandIncome), '#10b981', '');
            echo generateSummaryCard('💸 TOTAL PENGELUARAN', formatCurrency($grandExpense), '#ef4444', '');
            echo generateSummaryCard('📊 NET BALANCE', formatCurrency($grandNet), $grandNet >= 0 ? '#3b82f6' : '#f59e0b', '');
            echo generateSummaryCard('📈 TOTAL TRANSAKSI', number_format($grandTransactions), '#8b5cf6', '');
            ?>
        </div>
        
        <!-- Daily Summary Table -->
        <h2 style="font-size: 14px; font-weight: 700; color: #1f2937; margin-bottom: 1rem; page-break-after: avoid;">
            Ringkasan Per Hari
        </h2>
        
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; font-size: 11px;">
            <thead>
                <tr style="background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); font-weight: 700;">
                    <th style="padding: 10px; text-align: left; border-bottom: 2px solid #d1d5db;">Tanggal</th>
                    <th style="padding: 10px; text-align: left; border-bottom: 2px solid #d1d5db;">Hari</th>
                    <th style="padding: 10px; text-align: right; border-bottom: 2px solid #d1d5db;">Pemasukan</th>
                    <th style="padding: 10px; text-align: right; border-bottom: 2px solid #d1d5db;">Pengeluaran</th>
                    <th style="padding: 10px; text-align: right; border-bottom: 2px solid #d1d5db;">Net Balance</th>
                    <th style="padding: 10px; text-align: center; border-bottom: 2px solid #d1d5db;">Transaksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                $rowCount = 0;
                foreach ($dailySummary as $day): 
                    $bgColor = ($rowCount % 2 === 0) ? '#f9fafb' : '#ffffff';
                    $netColor = ($day['net_balance'] >= 0) ? '#10b981' : '#ef4444';
                    $rowCount++;
                ?>
                    <tr style="background: <?php echo $bgColor; ?>;">
                        <td style="padding: 8px 10px; border-bottom: 1px solid #e5e7eb; font-weight: 600;">
                            <?php echo date('d/m/Y', strtotime($day['date'])); ?>
                        </td>
                        <td style="padding: 8px 10px; border-bottom: 1px solid #e5e7eb;">
                            <?php echo $dayNames[date('w', strtotime($day['date']))]; ?>
                        </td>
                        <td style="padding: 8px 10px; border-bottom: 1px solid #e5e7eb; text-align: right; color: #10b981; font-weight: 600;">
                            Rp <?php echo number_format($day['total_income'], 0, ',', '.'); ?>
                        </td>
                        <td style="padding: 8px 10px; border-bottom: 1px solid #e5e7eb; text-align: right; color: #ef4444; font-weight: 600;">
                            Rp <?php echo number_format($day['total_expense'], 0, ',', '.'); ?>
                        </td>
                        <td style="padding: 8px 10px; border-bottom: 1px solid #e5e7eb; text-align: right; color: <?php echo $netColor; ?>; font-weight: 700;">
                            Rp <?php echo number_format($day['net_balance'], 0, ',', '.'); ?>
                        </td>
                        <td style="padding: 8px 10px; border-bottom: 1px solid #e5e7eb; text-align: center;">
                            <?php echo $day['transaction_count']; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); font-weight: 800;">
                    <td colspan="2" style="padding: 10px; border-top: 2px solid #d1d5db; border-bottom: 2px solid #d1d5db;">TOTAL</td>
                    <td style="padding: 10px; text-align: right; border-top: 2px solid #d1d5db; border-bottom: 2px solid #d1d5db; color: #10b981;">
                        Rp <?php echo number_format($grandIncome, 0, ',', '.'); ?>
                    </td>
                    <td style="padding: 10px; text-align: right; border-top: 2px solid #d1d5db; border-bottom: 2px solid #d1d5db; color: #ef4444;">
                        Rp <?php echo number_format($grandExpense, 0, ',', '.'); ?>
                    </td>
                    <td style="padding: 10px; text-align: right; border-top: 2px solid #d1d5db; border-bottom: 2px solid #d1d5db; color: <?php echo $grandNet >= 0 ? '#10b981' : '#ef4444'; ?>;">
                        Rp <?php echo number_format($grandNet, 0, ',', '.'); ?>
                    </td>
                    <td style="padding: 10px; text-align: center; border-top: 2px solid #d1d5db; border-bottom: 2px solid #d1d5db;">
                        <?php echo $grandTransactions; ?>
                    </td>
                </tr>
            </tfoot>
        </table>
        
        <?php echo generateSignatureSection(); ?>
        <?php echo generateReportFooter(); ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
