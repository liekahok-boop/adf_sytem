<?php
/**
 * MULTI-BUSINESS MANAGEMENT SYSTEM
 * Buku Kas Besar - List & Overview
 */

define('APP_ACCESS', true);
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();
$db = Database::getInstance();

// Load business configuration
$businessConfig = require '../../config/businesses/' . ACTIVE_BUSINESS_ID . '.php';

$pageTitle = BUSINESS_ICON . ' ' . BUSINESS_NAME . ' - Buku Kas Besar';
$pageSubtitle = 'Pencatatan Transaksi Keuangan';

// Filtering
$filterDate = getGet('date', date('Y-m-d'));
$filterMonth = getGet('month', '');
$filterType = getGet('type', 'all');
$filterDivision = getGet('division', 'all');
$filterPayment = getGet('payment', 'all');

// Build query with filters
$whereClauses = ["1=1"];
$params = [];

if ($filterDate !== 'all' && !empty($filterDate)) {
    $whereClauses[] = "cb.transaction_date = :date";
    $params['date'] = $filterDate;
} elseif (!empty($filterMonth)) {
    $whereClauses[] = "DATE_FORMAT(cb.transaction_date, '%Y-%m') = :month";
    $params['month'] = $filterMonth;
}

if ($filterType !== 'all') {
    $whereClauses[] = "cb.transaction_type = :type";
    $params['type'] = $filterType;
}

if ($filterDivision !== 'all') {
    $whereClauses[] = "cb.division_id = :division";
    $params['division'] = $filterDivision;
}

if ($filterPayment !== 'all') {
    $whereClauses[] = "cb.payment_method = :payment";
    $params['payment'] = $filterPayment;
}

$whereSQL = implode(' AND ', $whereClauses);

// Get transactions
$transactions = $db->fetchAll(
    "SELECT 
        cb.*,
        d.division_name,
        d.division_code,
        c.category_name,
        u.full_name as created_by_name
    FROM cash_book cb
    JOIN divisions d ON cb.division_id = d.id
    JOIN categories c ON cb.category_id = c.id
    JOIN users u ON cb.created_by = u.id
    WHERE {$whereSQL}
    ORDER BY cb.transaction_date DESC, cb.transaction_time DESC",
    $params
);

// Get divisions for filter
$divisions = $db->fetchAll("SELECT * FROM divisions WHERE is_active = 1 ORDER BY division_name");

// Calculate totals
$totalIncome = 0;
$totalExpense = 0;
foreach ($transactions as $trans) {
    if ($trans['transaction_type'] === 'income') {
        $totalIncome += $trans['amount'];
    } else {
        $totalExpense += $trans['amount'];
    }
}
$balance = $totalIncome - $totalExpense;

include '../../includes/header.php';
?>

<?php if (isset($_SESSION['success'])): ?>
    <div style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border-left: 4px solid #10b981; padding: 1.25rem 1.5rem; border-radius: 0.75rem; margin-bottom: 1.5rem; box-shadow: 0 4px 12px rgba(16,185,129,0.15); animation: slideInDown 0.5s ease-out;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 48px; height: 48px; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i data-feather="check-circle" style="width: 24px; height: 24px; color: white;"></i>
            </div>
            <div style="flex: 1;">
                <div style="font-weight: 700; color: #065f46; font-size: 1.125rem; margin-bottom: 0.25rem;">✅ Berhasil!</div>
                <div style="color: #047857; font-size: 0.95rem;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            </div>
            <button onclick="this.parentElement.parentElement.style.display='none'" style="background: none; border: none; color: #059669; font-size: 1.5rem; cursor: pointer; padding: 0; width: 32px; height: 32px;">&times;</button>
        </div>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-left: 4px solid #ef4444; padding: 1.25rem 1.5rem; border-radius: 0.75rem; margin-bottom: 1.5rem; box-shadow: 0 4px 12px rgba(239,68,68,0.15); animation: slideInDown 0.5s ease-out;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 48px; height: 48px; background: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i data-feather="x-circle" style="width: 24px; height: 24px; color: white;"></i>
            </div>
            <div style="flex: 1;">
                <div style="font-weight: 700; color: #991b1b; font-size: 1.125rem; margin-bottom: 0.25rem;">❌ Error!</div>
                <div style="color: #b91c1c; font-size: 0.95rem;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            </div>
            <button onclick="this.parentElement.parentElement.style.display='none'" style="background: none; border: none; color: #dc2626; font-size: 1.5rem; cursor: pointer; padding: 0; width: 32px; height: 32px;">&times;</button>
        </div>
    </div>
<?php endif; ?>

<style>
@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<!-- Summary Cards -->
<div class="dashboard-grid" style="margin-bottom: 2rem;">
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Total Pemasukan</div>
                <div class="card-value text-success"><?php echo formatCurrency($totalIncome); ?></div>
            </div>
            <div class="card-icon income">
                <i data-feather="arrow-down-circle"></i>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Total Pengeluaran</div>
                <div class="card-value text-danger"><?php echo formatCurrency($totalExpense); ?></div>
            </div>
            <div class="card-icon expense">
                <i data-feather="arrow-up-circle"></i>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Saldo</div>
                <div class="card-value <?php echo $balance >= 0 ? 'text-success' : 'text-danger'; ?>">
                    <?php echo formatCurrency($balance); ?>
                </div>
            </div>
            <div class="card-icon balance">
                <i data-feather="dollar-sign"></i>
            </div>
        </div>
    </div>
</div>

<!-- Transactions Table -->
<div class="table-container">
    <div class="table-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 40px; height: 40px; border-radius: var(--radius-md); background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center;">
                <i data-feather="book" style="width: 20px; height: 20px; color: white;"></i>
            </div>
            <div>
                <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                    Daftar Transaksi
                </h3>
                <p style="font-size: 0.813rem; color: var(--text-muted); margin: 0;">
                    <?php echo count($transactions); ?> transaksi ditemukan
                </p>
            </div>
        </div>
        <div class="table-actions" style="display: flex; gap: 0.5rem;">
            <a href="logs.php" class="btn btn-secondary" style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-feather="activity" style="width: 16px; height: 16px;"></i>
                <span>Audit Log</span>
            </a>
            <a href="add.php" class="btn btn-primary">
                <i data-feather="plus" style="width: 16px; height: 16px;"></i> Tambah Transaksi
            </a>
            <button onclick="window.print()" class="btn btn-secondary">
                <i data-feather="printer" style="width: 16px; height: 16px;"></i> Cetak
            </button>
        </div>
    </div>
    
    <!-- Filters -->
    <form method="GET" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 0.75rem; margin-bottom: 1.5rem; padding: 1.25rem; background: var(--bg-secondary); border-radius: var(--radius-lg); border: 1px solid var(--bg-tertiary);">
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.25rem;">Tanggal</label>
            <input type="date" name="date" value="<?php echo $filterDate !== 'all' ? $filterDate : ''; ?>" class="form-control" style="height: 38px; font-size: 0.875rem;">
        </div>
        
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.25rem;">Bulan</label>
            <input type="month" name="month" value="<?php echo $filterMonth; ?>" class="form-control" style="height: 38px; font-size: 0.875rem;">
        </div>
        
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.25rem;">Tipe</label>
            <select name="type" class="form-control" style="height: 38px; font-size: 0.875rem;">
                <option value="all" <?php echo $filterType === 'all' ? 'selected' : ''; ?>>Semua</option>
                <option value="income" <?php echo $filterType === 'income' ? 'selected' : ''; ?>>Pemasukan</option>
                <option value="expense" <?php echo $filterType === 'expense' ? 'selected' : ''; ?>>Pengeluaran</option>
            </select>
        </div>
        
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.25rem;">Divisi</label>
            <select name="division" class="form-control" style="height: 38px; font-size: 0.875rem;">
                <option value="all">Semua Divisi</option>
                <?php foreach ($divisions as $div): ?>
                    <option value="<?php echo $div['id']; ?>" <?php echo $filterDivision == $div['id'] ? 'selected' : ''; ?>>
                        <?php echo $div['division_name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.25rem;">Jenis Pembayaran</label>
            <select name="payment" class="form-control" style="height: 38px; font-size: 0.875rem;">
                <option value="all" <?php echo $filterPayment === 'all' ? 'selected' : ''; ?>>Semua</option>
                <option value="cash" <?php echo $filterPayment === 'cash' ? 'selected' : ''; ?>>Cash</option>
                <option value="transfer" <?php echo $filterPayment === 'transfer' ? 'selected' : ''; ?>>Transfer</option>
                <option value="qr" <?php echo $filterPayment === 'qr' ? 'selected' : ''; ?>>QR Code</option>
            </select>
        </div>
        
        <div style="display: flex; align-items: flex-end; gap: 0.625rem; grid-column: span 5;">
            <button type="submit" class="btn btn-primary" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 0.5rem; height: 40px;">
                <i data-feather="filter" style="width: 16px; height: 16px;"></i> 
                <span>Filter</span>
            </button>
            <a href="index.php" class="btn btn-secondary" style="flex: 0 0 auto; display: flex; align-items: center; justify-content: center; gap: 0.5rem; height: 40px; padding: 0 1.25rem;">
                <i data-feather="x" style="width: 16px; height: 16px;"></i> 
                <span>Reset</span>
            </a>
        </div>
    </form>
    
    <!-- Table -->
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Divisi</th>
                    <th>Kategori</th>
                    <th>Tipe</th>
                    <th style="text-align: right;">Jumlah</th>
                    <th>Deskripsi</th>
                    <th>Dibuat Oleh</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            <i data-feather="inbox" style="width: 48px; height: 48px; margin-bottom: 1rem;"></i>
                            <div>Belum ada transaksi</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $trans): ?>
                        <tr>
                            <td class="date-cell" data-date="<?php echo $trans['transaction_date']; ?>">
                                <?php echo formatDate($trans['transaction_date']); ?>
                            </td>
                            <td><?php echo date('H:i', strtotime($trans['transaction_time'])); ?></td>
                            <td>
                                <strong><?php echo $trans['division_name']; ?></strong><br>
                                <span style="font-size: 0.813rem; color: var(--text-muted);"><?php echo $trans['division_code']; ?></span>
                            </td>
                            <td><?php echo $trans['category_name']; ?></td>
                            <td>
                                <span class="badge <?php echo $trans['transaction_type']; ?>">
                                    <?php echo $trans['transaction_type'] === 'income' ? 'Masuk' : 'Keluar'; ?>
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: <?php echo $trans['transaction_type'] === 'income' ? 'var(--success)' : 'var(--danger)'; ?>;">
                                <?php echo formatCurrency($trans['amount']); ?>
                            </td>
                            <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?php if (isset($trans['source_type']) && $trans['source_type'] != 'manual'): ?>
                                    <span style="display: inline-flex; align-items: center; gap: 0.375rem; background: #dbeafe; color: #1e40af; padding: 0.25rem 0.5rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 600; margin-right: 0.5rem;">
                                        <i data-feather="shopping-cart" style="width: 12px; height: 12px;"></i>
                                        PO #<?php echo $trans['source_id']; ?>
                                    </span>
                                <?php endif; ?>
                                <?php echo $trans['description'] ?: '-'; ?>
                            </td>
                            <td style="font-size: 0.875rem;"><?php echo $trans['created_by_name']; ?></td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <?php if (isset($trans['is_editable']) && $trans['is_editable'] == 1): ?>
                                        <a href="edit.php?id=<?php echo $trans['id']; ?>" class="btn btn-sm btn-secondary" title="Edit">
                                            <i data-feather="edit-2" style="width: 16px; height: 16px;"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled title="Tidak dapat diedit - Dari PO" style="opacity: 0.5; cursor: not-allowed;">
                                            <i data-feather="lock" style="width: 16px; height: 16px;"></i>
                                        </button>
                                    <?php endif; ?>
                                    <a href="delete.php?id=<?php echo $trans['id']; ?>" 
                                       onclick="return confirm('Yakin ingin menghapus transaksi ini?')" 
                                       class="btn btn-sm btn-danger" 
                                       title="Hapus">
                                        <i data-feather="trash-2" style="width: 16px; height: 16px;"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    feather.replace();
</script>

<?php include '../../includes/footer.php'; ?>
