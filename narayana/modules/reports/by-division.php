<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();
$pageTitle = 'Laporan Per Divisi';

// Get filter parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

$params = ['start_date' => $start_date, 'end_date' => $end_date];

// Get all divisions with summary
$divisionSummary = $db->fetchAll("
    SELECT 
        d.id,
        d.division_name,
        COALESCE(SUM(CASE WHEN cb.transaction_type = 'income' THEN cb.amount ELSE 0 END), 0) as total_income,
        COALESCE(SUM(CASE WHEN cb.transaction_type = 'expense' THEN cb.amount ELSE 0 END), 0) as total_expense,
        COALESCE(SUM(CASE WHEN cb.transaction_type = 'income' THEN cb.amount ELSE 0 END), 0) - 
        COALESCE(SUM(CASE WHEN cb.transaction_type = 'expense' THEN cb.amount ELSE 0 END), 0) as net_balance,
        COUNT(cb.id) as transaction_count
    FROM divisions d
    LEFT JOIN cash_book cb ON d.id = cb.division_id 
        AND cb.transaction_date BETWEEN :start_date AND :end_date
    GROUP BY d.id, d.division_name
    ORDER BY total_income DESC
", $params);

// Calculate grand totals
$grandIncome = 0;
$grandExpense = 0;
$grandNet = 0;
$grandTransactions = 0;

foreach ($divisionSummary as $div) {
    $grandIncome += $div['total_income'];
    $grandExpense += $div['total_expense'];
    $grandNet += $div['net_balance'];
    $grandTransactions += $div['transaction_count'];
}

include '../../includes/header.php';
?>

<!-- Filter Section -->
<div class="card" style="margin-bottom: 1.25rem;">
    <form method="GET" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; align-items: end;">
        <div class="form-group" style="margin: 0;">
            <label class="form-label">Tanggal Mulai</label>
            <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>" required>
        </div>
        
        <div class="form-group" style="margin: 0;">
            <label class="form-label">Tanggal Akhir</label>
            <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>" required>
        </div>
        
        <button type="submit" class="btn btn-primary" style="height: 42px;">
            <i data-feather="search" style="width: 16px; height: 16px;"></i> Filter
        </button>
    </form>
</div>

<!-- Summary Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
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
                <i data-feather="dollar-sign" style="width: 24px; height: 24px; color: white;"></i>
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
                <i data-feather="grid" style="width: 24px; height: 24px; color: white;"></i>
            </div>
            <div style="flex: 1;">
                <div style="font-size: 0.813rem; color: var(--text-muted); margin-bottom: 0.25rem;">Total Divisi</div>
                <div style="font-size: 1.375rem; font-weight: 700; color: var(--text-primary);">
                    <?php echo count($divisionSummary); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
        <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin: 0;">
            Rekap Per Divisi (<?php echo date('d M Y', strtotime($start_date)); ?> - <?php echo date('d M Y', strtotime($end_date)); ?>)
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
    
    <?php if (empty($divisionSummary)): ?>
        <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
            <i data-feather="inbox" style="width: 48px; height: 48px; margin-bottom: 1rem;"></i>
            <p>Tidak ada data divisi</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th>Nama Divisi</th>
                        <th style="text-align: right;">Pemasukan</th>
                        <th style="text-align: right;">Pengeluaran</th>
                        <th style="text-align: right;">Saldo Bersih</th>
                        <th style="text-align: center; width: 12%;">Transaksi</th>
                        <th style="text-align: center; width: 10%;">Kontribusi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($divisionSummary as $div): 
                        $contribution = $grandIncome > 0 ? ($div['total_income'] / $grandIncome * 100) : 0;
                    ?>
                        <tr>
                            <td style="text-align: center; color: var(--text-muted);">
                                <?php echo $no++; ?>
                            </td>
                            <td style="font-weight: 600; color: var(--text-primary);">
                                <?php echo $div['division_name']; ?>
                            </td>
                            <td style="text-align: right; color: var(--success); font-weight: 600;">
                                Rp <?php echo number_format($div['total_income'], 0, ',', '.'); ?>
                            </td>
                            <td style="text-align: right; color: var(--danger); font-weight: 600;">
                                Rp <?php echo number_format($div['total_expense'], 0, ',', '.'); ?>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: <?php echo $div['net_balance'] >= 0 ? 'var(--success)' : 'var(--danger)'; ?>;">
                                Rp <?php echo number_format($div['net_balance'], 0, ',', '.'); ?>
                            </td>
                            <td style="text-align: center; color: var(--text-muted);">
                                <?php echo number_format($div['transaction_count'], 0, ',', '.'); ?>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; justify-content: center;">
                                    <div style="flex: 1; height: 6px; background: var(--bg-tertiary); border-radius: 3px; overflow: hidden; max-width: 60px;">
                                        <div style="width: <?php echo $contribution; ?>%; height: 100%; background: var(--success); transition: width 0.3s;"></div>
                                    </div>
                                    <span style="font-size: 0.813rem; font-weight: 600; color: var(--text-primary); min-width: 45px;">
                                        <?php echo number_format($contribution, 1); ?>%
                                    </span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot style="border-top: 2px solid var(--bg-tertiary);">
                    <tr style="background: var(--bg-tertiary);">
                        <td colspan="2" style="font-weight: 700; color: var(--text-primary);">TOTAL</td>
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
                        <td style="text-align: center; font-weight: 700; color: var(--text-primary);">
                            100%
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Division Details Cards -->
<div style="margin-top: 1.5rem;">
    <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1rem;">
        Detail Per Divisi
    </h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap: 1rem;">
        <?php foreach ($divisionSummary as $div): 
            if ($div['transaction_count'] == 0) continue;
            $profitMargin = $div['total_income'] > 0 ? (($div['net_balance'] / $div['total_income']) * 100) : 0;
            
            // Get transactions for this division
            $divTransactions = $db->fetchAll("
                SELECT 
                    cb.*,
                    c.category_name
                FROM cash_book cb
                LEFT JOIN categories c ON cb.category_id = c.id
                WHERE cb.division_id = :division_id 
                    AND cb.transaction_date BETWEEN :start_date AND :end_date
                ORDER BY cb.transaction_date DESC, cb.transaction_time DESC
            ", [
                'division_id' => $div['id'],
                'start_date' => $start_date,
                'end_date' => $end_date
            ]);
        ?>
            <div class="card" style="padding: 1.25rem; border-left: 4px solid var(--primary-color);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h4 style="font-size: 1rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                        <?php echo $div['division_name']; ?>
                    </h4>
                    <span style="background: var(--bg-tertiary); padding: 0.25rem 0.625rem; border-radius: var(--radius-md); font-size: 0.75rem; font-weight: 600; color: var(--text-muted);">
                        <?php echo $div['transaction_count']; ?> trx
                    </span>
                </div>
                
                <div style="display: grid; gap: 0.625rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--bg-tertiary);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.813rem; color: var(--text-muted);">Pemasukan</span>
                        <span style="font-weight: 600; color: var(--success);">
                            Rp <?php echo number_format($div['total_income'], 0, ',', '.'); ?>
                        </span>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.813rem; color: var(--text-muted);">Pengeluaran</span>
                        <span style="font-weight: 600; color: var(--danger);">
                            Rp <?php echo number_format($div['total_expense'], 0, ',', '.'); ?>
                        </span>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.625rem; border-top: 1px solid var(--bg-tertiary);">
                        <span style="font-size: 0.813rem; font-weight: 600; color: var(--text-primary);">Net Balance</span>
                        <span style="font-weight: 700; color: <?php echo $div['net_balance'] >= 0 ? 'var(--success)' : 'var(--danger)'; ?>;">
                            Rp <?php echo number_format($div['net_balance'], 0, ',', '.'); ?>
                        </span>
                    </div>
                </div>
                
                <!-- Transaction Details -->
                <div style="max-height: 300px; overflow-y: auto;">
                    <h5 style="font-size: 0.875rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.75rem;">
                        Rincian Transaksi:
                    </h5>
                    <?php if (empty($divTransactions)): ?>
                        <p style="text-align: center; color: var(--text-muted); font-size: 0.813rem; padding: 1rem 0;">
                            Tidak ada transaksi
                        </p>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 0.625rem;">
                            <?php foreach ($divTransactions as $trans): ?>
                                <div style="background: var(--bg-secondary); padding: 0.75rem; border-radius: var(--radius-md); border-left: 3px solid <?php echo $trans['transaction_type'] === 'income' ? 'var(--success)' : 'var(--danger)'; ?>;">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.375rem;">
                                        <div style="flex: 1;">
                                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.125rem;">
                                                <?php echo date('d/m/Y H:i', strtotime($trans['transaction_date'] . ' ' . $trans['transaction_time'])); ?>
                                            </div>
                                            <div style="font-size: 0.813rem; font-weight: 600; color: var(--text-primary);">
                                                <?php echo $trans['category_name']; ?>
                                            </div>
                                        </div>
                                        <div style="text-align: right;">
                                            <span style="font-size: 0.625rem; background: <?php echo $trans['transaction_type'] === 'income' ? 'var(--success)' : 'var(--danger)'; ?>; color: white; padding: 0.125rem 0.375rem; border-radius: 3px; display: inline-block; margin-bottom: 0.25rem;">
                                                <?php echo $trans['transaction_type'] === 'income' ? 'IN' : 'OUT'; ?>
                                            </span>
                                            <div style="font-size: 0.875rem; font-weight: 700; color: <?php echo $trans['transaction_type'] === 'income' ? 'var(--success)' : 'var(--danger)'; ?>;">
                                                <?php echo $trans['transaction_type'] === 'income' ? '+' : '-'; ?> Rp <?php echo number_format($trans['amount'], 0, ',', '.'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if (!empty($trans['description'])): ?>
                                        <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.375rem; padding-top: 0.375rem; border-top: 1px solid var(--bg-tertiary);">
                                            <?php echo htmlspecialchars($trans['description']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    feather.replace();
    
    function exportToPDF() {
        const params = new URLSearchParams(window.location.search);
        params.set('export', 'pdf');
        window.open('division-pdf.php?' + params.toString(), '_blank');
    }
</script>

<?php include '../../includes/footer.php'; ?>
