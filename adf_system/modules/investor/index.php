<?php
// Enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define APP_ACCESS constant
define('APP_ACCESS', true);

// Get base path
$base_path = dirname(dirname(dirname(__FILE__)));

require_once $base_path . '/config/config.php';
require_once $base_path . '/config/database.php';
require_once $base_path . '/includes/auth.php';
require_once $base_path . '/includes/InvestorManager.php';

// Check permission
$auth = new Auth();
$auth->requireLogin();

if (!$auth->hasPermission('investor')) {
    header('HTTP/1.1 403 Forbidden');
    echo "You do not have permission to access this module.";
    exit;
}

$db = Database::getInstance()->getConnection();
$investor = new InvestorManager($db);

// Get all investors
$investors = $investor->getAllInvestors();

// Calculate total capital across all investors
$total_capital = 0;
$total_expenses = 0;
$total_balance = 0;

foreach ($investors as $inv) {
    $total_capital += $inv['total_capital_idr'] ?? 0;
    $total_expenses += $inv['total_expenses_idr'] ?? 0;
    $total_balance += $inv['remaining_balance_idr'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modul Investor - Manajemen Dana</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        .main-content {
            background: var(--bg-primary) !important;
            min-height: 100vh !important;
        }
        
        .investor-container {
            padding: 2rem;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .card-header {
            font-size: 0.875rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .card-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .card-subtext {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .investor-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--bg-secondary);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .investor-table thead {
            background: var(--bg-tertiary);
            border-bottom: 2px solid var(--border-color);
        }

        .investor-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--text-primary);
        }

        .investor-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .investor-table tbody tr:hover {
            background: var(--bg-tertiary);
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
        }

        .status-inactive {
            background: rgba(107, 114, 128, 0.1);
            color: #6b7280;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: var(--border-color);
        }

        .btn-small {
            padding: 0.35rem 0.75rem;
            font-size: 0.75rem;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .header-section h1 {
            margin: 0;
            font-size: 2rem;
        }

        .chart-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .chart-box {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .chart-box h3 {
            margin-top: 0;
            margin-bottom: 1rem;
            font-size: 1.1rem;
            color: var(--text-primary);
        }

        .chart-wrapper {
            position: relative;
            height: 320px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--bg-secondary);
            border-radius: 8px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-header h2 {
            margin: 0;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-muted);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background: var(--bg-tertiary);
            color: var(--text-primary);
            font-family: inherit;
            font-size: 1rem;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }

        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            border: 1px solid #22c55e;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid #ef4444;
        }
    </style>
</head>
<body>
    <?php include '../../includes/header-simple.php'; ?>

    <main class="main-content">
        <div class="investor-container">
            <!-- Header Section -->
            <div class="header-section">
                <h1>📊 Manajemen Investor</h1>
                <button class="btn btn-primary" onclick="openAddInvestorModal()">
                    <i data-feather="plus" style="display: inline; margin-right: 0.5rem; width: 18px; height: 18px;"></i>
                    Tambah Investor
                </button>
            </div>

            <!-- Alert Messages -->
            <div id="alertContainer"></div>

            <!-- Dashboard Cards -->
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-header">Total Modal Masuk</div>
                    <div class="card-value">Rp <?php echo number_format($total_capital, 0, ',', '.'); ?></div>
                    <div class="card-subtext">USD <?php echo number_format($total_capital / 15500, 2, ',', '.'); ?></div>
                </div>

                <div class="card">
                    <div class="card-header">Total Pengeluaran</div>
                    <div class="card-value">Rp <?php echo number_format($total_expenses, 0, ',', '.'); ?></div>
                    <div class="card-subtext">Project expenses terdakumulasi</div>
                </div>

                <div class="card">
                    <div class="card-header">Saldo Tersedia</div>
                    <div class="card-value" style="color: #22c55e;">Rp <?php echo number_format($total_balance, 0, ',', '.'); ?></div>
                    <div class="card-subtext">Saldo untuk project baru</div>
                </div>

                <div class="card">
                    <div class="card-header">Jumlah Investor</div>
                    <div class="card-value"><?php echo count($investors); ?></div>
                    <div class="card-subtext">Investor aktif</div>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="chart-container">
                <div class="chart-box">
                    <h3>💰 Akumulasi Modal Per Investor</h3>
                    <div class="chart-wrapper">
                        <canvas id="capitalChart"></canvas>
                    </div>
                </div>
                <div class="chart-box">
                    <h3>📊 Pengeluaran Per Projek</h3>
                    <div class="chart-wrapper">
                        <canvas id="projectExpenseChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Investors Table -->
            <div style="margin-bottom: 2rem;">
                <h2 style="margin-bottom: 1rem;">Daftar Investor</h2>
                <?php if (count($investors) > 0): ?>
                    <table class="investor-table">
                        <thead>
                            <tr>
                                <th>Nama Investor</th>
                                <th>Kontak</th>
                                <th>Modal Masuk</th>
                                <th>Pengeluaran</th>
                                <th>Saldo</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($investors as $inv): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($inv['name']); ?></strong><br>
                                        <small style="color: var(--text-muted);"><?php echo htmlspecialchars($inv['notes'] ?? '-'); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($inv['contact'] ?? '-'); ?><br>
                                        <small style="color: var(--text-muted);"><?php echo htmlspecialchars($inv['email'] ?? '-'); ?></small>
                                    </td>
                                    <td>
                                        <strong>Rp <?php echo number_format($inv['total_capital_idr'] ?? 0, 0, ',', '.'); ?></strong>
                                    </td>
                                    <td>
                                        <strong>Rp <?php echo number_format($inv['total_expenses_idr'] ?? 0, 0, ',', '.'); ?></strong>
                                    </td>
                                    <td>
                                        <strong style="color: #22c55e;">Rp <?php echo number_format($inv['remaining_balance_idr'] ?? 0, 0, ',', '.'); ?></strong>
                                    </td>
                                    <td>
                                        <span class="status-badge status-active">
                                            Active
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-secondary btn-small" onclick="viewInvestor(<?php echo $inv['id']; ?>)" title="Lihat Detail">
                                                <i data-feather="eye" style="width: 14px; height: 14px;"></i>
                                            </button>
                                            <button class="btn btn-success btn-small" onclick="addCapitalTransaction(<?php echo $inv['id']; ?>)" title="Tambah Modal" style="background: #10b981; border-color: #10b981;">
                                                <i data-feather="plus-circle" style="width: 14px; height: 14px; margin-right: 4px;"></i>
                                                Saldo
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="card" style="text-align: center; padding: 3rem;">
                        <p style="color: var(--text-muted); margin: 0;">Belum ada investor. Klik tombol "Tambah Investor" untuk memulai.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Add Investor Modal -->
    <div id="addInvestorModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Tambah Investor Baru</h2>
                <button class="close-btn" onclick="closeAddInvestorModal()">✕</button>
            </div>
            <form id="addInvestorForm" onsubmit="submitAddInvestor(event)">
                <div class="form-group">
                    <label for="investor_name">Nama Investor *</label>
                    <input type="text" id="investor_name" name="investor_name" required>
                </div>

                <div class="form-group">
                    <label for="investor_address">Alamat Lengkap *</label>
                    <textarea id="investor_address" name="investor_address" required></textarea>
                </div>

                <div class="form-group">
                    <label for="contact_phone">Nomor Telepon</label>
                    <input type="tel" id="contact_phone" name="contact_phone">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email">
                </div>

                <div class="form-group">
                    <label for="notes">Catatan</label>
                    <textarea id="notes" name="notes" style="min-height: 60px;"></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeAddInvestorModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Investor</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Capital Transaction Modal -->
    <div id="capitalTransactionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Tambah Transaksi Modal</h2>
                <button class="close-btn" onclick="closeCapitalTransactionModal()">✕</button>
            </div>
            <form id="capitalTransactionForm" onsubmit="submitCapitalTransaction(event)">
                <input type="hidden" id="investor_id_hidden" name="investor_id">

                <div class="form-group">
                    <label for="amount_usd">Jumlah USD *</label>
                    <input type="number" id="amount_usd" name="amount_usd" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="exchange_rate_display">Kurs USD → IDR</label>
                    <input type="text" id="exchange_rate_display" readonly style="background: var(--bg-tertiary); cursor: not-allowed;">
                </div>

                <div class="form-group">
                    <label for="amount_idr_display">Total IDR</label>
                    <input type="text" id="amount_idr_display" readonly style="background: var(--bg-tertiary); cursor: not-allowed;">
                </div>

                <div class="form-group">
                    <label for="transaction_date">Tanggal Transaksi *</label>
                    <input type="date" id="transaction_date" name="transaction_date" required>
                </div>

                <div class="form-group">
                    <label for="payment_method">Metode Pembayaran</label>
                    <select id="payment_method" name="payment_method">
                        <option value="bank_transfer">Transfer Bank</option>
                        <option value="cash">Tunai</option>
                        <option value="check">Cek</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="reference_no">No. Referensi</label>
                    <input type="text" id="reference_no" name="reference_no">
                </div>

                <div class="form-group">
                    <label for="transaction_description">Deskripsi</label>
                    <textarea id="transaction_description" name="description" style="min-height: 60px;"></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeCapitalTransactionModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Initialize Feather Icons
        feather.replace();
        
        // Set today's date as default
        document.getElementById('transaction_date').valueAsDate = new Date();

        // Fungsi Modal
        function openAddInvestorModal() {
            document.getElementById('addInvestorModal').classList.add('active');
        }

        function closeAddInvestorModal() {
            document.getElementById('addInvestorModal').classList.remove('active');
            document.getElementById('addInvestorForm').reset();
        }

        function addCapitalTransaction(investorId) {
            document.getElementById('investor_id_hidden').value = investorId;
            document.getElementById('capitalTransactionModal').classList.add('active');
            loadExchangeRate();
        }

        function closeCapitalTransactionModal() {
            document.getElementById('capitalTransactionModal').classList.remove('active');
            document.getElementById('capitalTransactionForm').reset();
        }

        // Fixed exchange rate (Rp per USD) - Update manually jika perlu
        const FIXED_USD_RATE = 15500; // Rp 15,500 per USD (25 Jan 2026)

        // Load exchange rate (fixed value)
        function loadExchangeRate() {
            document.getElementById('exchange_rate_display').value = 'Rp ' + FIXED_USD_RATE.toLocaleString('id-ID');
        }

        // Calculate IDR amount when USD changes
        document.getElementById('amount_usd').addEventListener('change', function() {
            const amountUsd = parseFloat(this.value) || 0;
            if (amountUsd > 0) {
                const amountIdr = amountUsd * FIXED_USD_RATE;
                document.getElementById('exchange_rate_display').value = 'Rp ' + FIXED_USD_RATE.toLocaleString('id-ID');
                document.getElementById('amount_idr_display').value = 'Rp ' + amountIdr.toLocaleString('id-ID', {maximumFractionDigits: 0});
            }
        });

        // Submit add investor
        async function submitAddInvestor(event) {
            event.preventDefault();
            const form = document.getElementById('addInvestorForm');
            const btn = event.target.querySelector('button[type="submit"]');
            btn.classList.add('loading');

            try {
                const formData = new FormData(form);
                const response = await fetch('<?php echo BASE_URL; ?>/api/investor-create.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                showAlert(data.message, data.success ? 'success' : 'error');

                if (data.success) {
                    closeAddInvestorModal();
                    setTimeout(() => location.reload(), 1000);
                }
            } catch (error) {
                showAlert('Error: ' + error.message, 'error');
            } finally {
                btn.classList.remove('loading');
            }
        }

        // Submit capital transaction
        async function submitCapitalTransaction(event) {
            event.preventDefault();
            const form = document.getElementById('capitalTransactionForm');
            const btn = event.target.querySelector('button[type="submit"]');
            const investorId = document.getElementById('investor_id_hidden').value;
            btn.classList.add('loading');

            try {
                const formData = new FormData(form);
                const response = await fetch('<?php echo BASE_URL; ?>/api/investor-add-capital.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                showAlert(data.message, data.success ? 'success' : 'error');

                if (data.success) {
                    closeCapitalTransactionModal();
                    setTimeout(() => location.reload(), 1000);
                }
            } catch (error) {
                showAlert('Error: ' + error.message, 'error');
            } finally {
                btn.classList.remove('loading');
            }
        }

        // View investor details
        function viewInvestor(investorId) {
            window.location.href = '<?php echo BASE_URL; ?>/modules/investor/investor-detail.php?id=' + investorId;
        }

        // Show alert
        function showAlert(message, type) {
            const container = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = 'alert alert-' + type;
            alert.textContent = message;
            container.innerHTML = '';
            container.appendChild(alert);

            setTimeout(() => alert.remove(), 5000);
        }

        // Initialize Chart.js - Modern Trading Line Chart 2028 Vibe
        const ctx = document.getElementById('capitalChart').getContext('2d');
        const chartData = <?php
            $labels = [];
            $data = [];
            foreach ($investors as $inv) {
                $labels[] = $inv['name'];
                $data[] = $inv['total_capital_idr'] ?? 0;
            }
            echo json_encode([
                'labels' => $labels,
                'data' => $data
            ]);
        ?>;

        // Create gradient for line chart
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(99, 102, 241, 0.4)');
        gradient.addColorStop(0.5, 'rgba(99, 102, 241, 0.15)');
        gradient.addColorStop(1, 'rgba(99, 102, 241, 0.02)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Total Modal (IDR)',
                    data: chartData.data,
                    borderColor: '#6366f1',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: '#6366f1',
                    pointBorderColor: '#1e293b',
                    pointBorderWidth: 3,
                    pointHoverRadius: 8,
                    pointHoverBackgroundColor: '#818cf8',
                    pointHoverBorderColor: '#6366f1',
                    pointHoverBorderWidth: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        titleColor: '#f1f5f9',
                        bodyColor: '#cbd5e1',
                        borderColor: '#6366f1',
                        borderWidth: 2,
                        padding: 16,
                        displayColors: false,
                        titleFont: {
                            size: 15,
                            weight: '600',
                            family: "'Inter', 'Segoe UI', sans-serif"
                        },
                        bodyFont: {
                            size: 14,
                            weight: '500'
                        },
                        callbacks: {
                            title: function(context) {
                                return '💼 ' + context[0].label;
                            },
                            label: function(context) {
                                const value = context.parsed.y;
                                const formatted = new Intl.NumberFormat('id-ID', {
                                    style: 'currency',
                                    currency: 'IDR',
                                    minimumFractionDigits: 0
                                }).format(value);
                                return 'Modal: ' + formatted;
                            },
                            afterLabel: function(context) {
                                const total = chartData.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed.y / total) * 100).toFixed(1);
                                return 'Kontribusi: ' + percentage + '%';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: true,
                            color: '#334155'
                        },
                        ticks: {
                            color: '#cbd5e1',
                            font: {
                                size: 13,
                                weight: '600',
                                family: "'Inter', 'Segoe UI', sans-serif"
                            },
                            padding: 12
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(148, 163, 184, 0.08)',
                            drawBorder: false
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            color: '#94a3b8',
                            font: {
                                size: 12,
                                weight: '500'
                            },
                            padding: 12,
                            callback: function(value) {
                                if (value >= 1000000000) {
                                    return 'Rp ' + (value / 1000000000).toFixed(1) + 'M';
                                } else if (value >= 1000000) {
                                    return 'Rp ' + (value / 1000000).toFixed(1) + 'Jt';
                                }
                                return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                            }
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });

        // Initialize feather icons
        feather.replace();
    </script>
</body>
</html>
?>
