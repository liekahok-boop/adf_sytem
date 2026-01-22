<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$invoice_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$print_mode = isset($_GET['print']);

if ($invoice_id <= 0) {
    $_SESSION['error'] = 'Invoice tidak ditemukan';
    redirect(BASE_URL . '/modules/sales/index.php');
}

// Get invoice data
$invoice = $db->fetchOne("
    SELECT 
        si.*,
        d.division_name,
        d.division_code,
        u.full_name as created_by_name
    FROM sales_invoices_header si
    LEFT JOIN divisions d ON si.division_id = d.id
    LEFT JOIN users u ON si.created_by = u.id
    WHERE si.id = ?
", [$invoice_id]);

if (!$invoice) {
    $_SESSION['error'] = 'Invoice tidak ditemukan';
    redirect(BASE_URL . '/modules/sales/index.php');
}

// Get invoice items
$items = $db->fetchAll("
    SELECT * FROM sales_invoices_detail
    WHERE invoice_header_id = ?
    ORDER BY id
", [$invoice_id]);

// Get company/hotel info
$companySettings = [
    'name' => $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = 'company_name'")['setting_value'] ?? 'Narayana Hotel',
    'address' => $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = 'company_address'")['setting_value'] ?? '',
    'phone' => $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = 'company_phone'")['setting_value'] ?? '',
    'email' => $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = 'company_email'")['setting_value'] ?? '',
    'logo' => $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = 'company_logo'")['setting_value'] ?? null,
    'invoice_logo' => $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = 'invoice_logo'")['setting_value'] ?? null
];

// Use invoice logo if available, otherwise use company logo
$displayLogo = $companySettings['invoice_logo'] ?? $companySettings['logo'];

if ($print_mode) {
    // Print layout
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Invoice <?php echo $invoice['invoice_number']; ?></title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 14px; color: #333; }
            .invoice-container { max-width: 210mm; margin: 0 auto; padding: 20mm; background: white; }
            .header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #6366f1; }
            .company-info { flex: 1; }
            .company-logo { width: 80px; height: 80px; object-fit: contain; margin-bottom: 10px; }
            .company-name { font-size: 24px; font-weight: 700; color: #6366f1; margin-bottom: 5px; }
            .invoice-title { text-align: right; }
            .invoice-number { font-size: 28px; font-weight: 700; color: #6366f1; }
            .invoice-date { color: #666; margin-top: 5px; }
            .section { margin-bottom: 30px; }
            .section-title { font-size: 12px; font-weight: 700; text-transform: uppercase; color: #666; margin-bottom: 10px; letter-spacing: 1px; }
            .customer-info, .billing-info { background: #f9fafb; padding: 15px; border-radius: 8px; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th { background: #6366f1; color: white; padding: 12px; text-align: left; font-weight: 600; font-size: 13px; }
            td { padding: 12px; border-bottom: 1px solid #e5e7eb; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            .summary { margin-top: 30px; display: flex; justify-content: flex-end; }
            .summary-table { width: 300px; }
            .summary-table tr td { padding: 8px 12px; }
            .summary-total { font-size: 20px; font-weight: 700; color: #6366f1; background: #f0f9ff; }
            .footer { margin-top: 50px; padding-top: 20px; border-top: 2px solid #e5e7eb; text-align: center; color: #666; font-size: 12px; }
            .status-badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-weight: 600; font-size: 12px; }
            .status-paid { background: #d1fae5; color: #065f46; }
            .status-unpaid { background: #fee2e2; color: #991b1b; }
            .status-partial { background: #fef3c7; color: #92400e; }
            @media print {
                body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
                .no-print { display: none; }
                .invoice-container { padding: 0; }
            }
        </style>
    </head>
    <body>
        <div class="invoice-container">
            <!-- Header -->
            <div class="header">
                <div class="company-info">
                    <?php if ($displayLogo && file_exists(BASE_PATH . '/uploads/logos/' . $displayLogo)): ?>
                        <img src="<?php echo BASE_URL . '/uploads/logos/' . $displayLogo; ?>" alt="Logo" class="company-logo">
                    <?php endif; ?>
                    <div class="company-name"><?php echo $companySettings['name']; ?></div>
                    <?php if ($companySettings['address']): ?>
                        <div style="font-size: 12px; color: #666; margin-top: 5px;"><?php echo nl2br($companySettings['address']); ?></div>
                    <?php endif; ?>
                    <?php if ($companySettings['phone']): ?>
                        <div style="font-size: 12px; color: #666;">Tel: <?php echo $companySettings['phone']; ?></div>
                    <?php endif; ?>
                    <?php if ($companySettings['email']): ?>
                        <div style="font-size: 12px; color: #666;">Email: <?php echo $companySettings['email']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="invoice-title">
                    <div class="invoice-number"><?php echo $invoice['invoice_number']; ?></div>
                    <div class="invoice-date"><?php echo date('d F Y', strtotime($invoice['invoice_date'])); ?></div>
                    <?php
                    $status_class = [
                        'paid' => 'status-paid',
                        'unpaid' => 'status-unpaid',
                        'partial' => 'status-partial'
                    ];
                    $status_label = [
                        'paid' => 'LUNAS',
                        'unpaid' => 'BELUM BAYAR',
                        'partial' => 'SEBAGIAN'
                    ];
                    ?>
                    <div style="margin-top: 10px;">
                        <span class="status-badge <?php echo $status_class[$invoice['payment_status']]; ?>">
                            <?php echo $status_label[$invoice['payment_status']]; ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Customer Info -->
            <div class="section">
                <div class="section-title">Kepada:</div>
                <div class="customer-info">
                    <div style="font-size: 18px; font-weight: 700; margin-bottom: 8px;"><?php echo $invoice['customer_name']; ?></div>
                    <?php if ($invoice['customer_address']): ?>
                        <div style="font-size: 13px; color: #666; margin-bottom: 5px;"><?php echo nl2br($invoice['customer_address']); ?></div>
                    <?php endif; ?>
                    <?php if ($invoice['customer_phone']): ?>
                        <div style="font-size: 13px; color: #666;">Tel: <?php echo $invoice['customer_phone']; ?></div>
                    <?php endif; ?>
                    <?php if ($invoice['customer_email']): ?>
                        <div style="font-size: 13px; color: #666;">Email: <?php echo $invoice['customer_email']; ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Items Table -->
            <div class="section">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 40px;" class="text-center">#</th>
                            <th>Item / Layanan</th>
                            <th style="width: 100px;" class="text-center">Qty</th>
                            <th style="width: 150px;" class="text-right">Harga Satuan</th>
                            <th style="width: 150px;" class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $index => $item): ?>
                            <tr>
                                <td class="text-center"><?php echo $index + 1; ?></td>
                                <td>
                                    <div style="font-weight: 600;"><?php echo $item['item_name']; ?></div>
                                    <?php if ($item['item_description']): ?>
                                        <div style="font-size: 12px; color: #666; margin-top: 3px;"><?php echo $item['item_description']; ?></div>
                                    <?php endif; ?>
                                    <?php if ($item['category']): ?>
                                        <div style="font-size: 11px; color: #666; margin-top: 3px;">
                                            <span style="background: #e5e7eb; padding: 2px 8px; border-radius: 4px;"><?php echo ucfirst(str_replace('_', ' ', $item['category'])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?php echo number_format($item['quantity'], 0, ',', '.'); ?></td>
                                <td class="text-right">Rp <?php echo number_format($item['unit_price'], 0, ',', '.'); ?></td>
                                <td class="text-right" style="font-weight: 600;">Rp <?php echo number_format($item['total_price'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Summary -->
            <div class="summary">
                <table class="summary-table">
                    <tr>
                        <td>Subtotal</td>
                        <td class="text-right">Rp <?php echo number_format($invoice['subtotal'], 0, ',', '.'); ?></td>
                    </tr>
                    <?php if ($invoice['discount_amount'] > 0): ?>
                        <tr>
                            <td>Diskon</td>
                            <td class="text-right" style="color: #ef4444;">- Rp <?php echo number_format($invoice['discount_amount'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if ($invoice['tax_amount'] > 0): ?>
                        <tr>
                            <td>Pajak</td>
                            <td class="text-right">Rp <?php echo number_format($invoice['tax_amount'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr class="summary-total">
                        <td style="font-size: 16px;">TOTAL</td>
                        <td class="text-right">Rp <?php echo number_format($invoice['total_amount'], 0, ',', '.'); ?></td>
                    </tr>
                </table>
            </div>
            
            <!-- Notes -->
            <?php if ($invoice['notes']): ?>
                <div class="section" style="margin-top: 30px;">
                    <div class="section-title">Catatan:</div>
                    <div style="background: #f9fafb; padding: 15px; border-radius: 8px; font-size: 13px; color: #666;">
                        <?php echo nl2br($invoice['notes']); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Footer -->
            <div class="footer">
                <div style="margin-bottom: 10px;">Terima kasih atas kepercayaan Anda!</div>
                <div>Dibuat oleh: <?php echo $invoice['created_by_name']; ?> | Tanggal: <?php echo date('d/m/Y H:i', strtotime($invoice['created_at'])); ?></div>
                <div style="margin-top: 20px;">
                    <strong>Metode Pembayaran:</strong> <?php echo ucfirst($invoice['payment_method']); ?>
                </div>
            </div>
            
            <div class="no-print" style="text-align: center; margin-top: 30px;">
                <button onclick="window.print()" style="background: #6366f1; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                    🖨️ Print Invoice
                </button>
                <button onclick="window.close()" style="background: #6b7280; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; margin-left: 10px;">
                    ✖ Tutup
                </button>
            </div>
        </div>
        
        <script>
            // Auto print on load if needed
            // window.onload = function() { window.print(); };
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Normal view mode
$pageTitle = 'Invoice ' . $invoice['invoice_number'];
include '../../includes/header.php';
?>

<div style="max-width: 900px; margin: 0 auto;">
    <!-- Actions -->
    <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
        <a href="index.php" class="btn btn-secondary">
            <i data-feather="arrow-left" style="width: 16px; height: 16px;"></i> Kembali
        </a>
        <div style="display: flex; gap: 0.75rem;">
            <a href="view-invoice.php?id=<?php echo $invoice_id; ?>&print=1" target="_blank" class="btn btn-primary">
                <i data-feather="printer" style="width: 16px; height: 16px;"></i> Print Invoice
            </a>
        </div>
    </div>
    
    <!-- Invoice Preview Card -->
    <div class="card" style="padding: 2rem;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 3px solid var(--primary-color);">
            <div>
                <?php if ($displayLogo && file_exists(BASE_PATH . '/uploads/logos/' . $displayLogo)): ?>
                    <img src="<?php echo BASE_URL . '/uploads/logos/' . $displayLogo; ?>" alt="Logo" style="width: 80px; height: 80px; object-fit: contain; margin-bottom: 10px;">
                <?php endif; ?>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color);"><?php echo $companySettings['name']; ?></div>
                <div style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.5rem; line-height: 1.5;">
                    <?php if ($companySettings['address']): ?><?php echo nl2br($companySettings['address']); ?><br><?php endif; ?>
                    <?php if ($companySettings['phone']): ?>Tel: <?php echo $companySettings['phone']; ?><br><?php endif; ?>
                    <?php if ($companySettings['email']): ?>Email: <?php echo $companySettings['email']; ?><?php endif; ?>
                </div>
            </div>
            
            <div style="text-align: right;">
                <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color);"><?php echo $invoice['invoice_number']; ?></div>
                <div style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.5rem;"><?php echo date('d F Y', strtotime($invoice['invoice_date'])); ?></div>
                <?php
                $status_colors = ['paid' => 'success', 'unpaid' => 'danger', 'partial' => 'warning'];
                $status_labels = ['paid' => '✓ LUNAS', 'unpaid' => '⏳ BELUM BAYAR', 'partial' => '⏱ SEBAGIAN'];
                ?>
                <span class="badge badge-<?php echo $status_colors[$invoice['payment_status']]; ?>" style="margin-top: 0.75rem; font-size: 0.875rem; padding: 0.5rem 1rem;">
                    <?php echo $status_labels[$invoice['payment_status']]; ?>
                </span>
            </div>
        </div>
        
        <!-- Customer Info -->
        <div style="background: var(--bg-secondary); padding: 1.25rem; border-radius: 0.75rem; margin-bottom: 2rem;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.75rem;">Kepada:</div>
            <div style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;"><?php echo $invoice['customer_name']; ?></div>
            <?php if ($invoice['customer_address']): ?>
                <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.25rem;"><?php echo nl2br($invoice['customer_address']); ?></div>
            <?php endif; ?>
            <?php if ($invoice['customer_phone']): ?>
                <div style="color: var(--text-muted); font-size: 0.875rem;">Tel: <?php echo $invoice['customer_phone']; ?></div>
            <?php endif; ?>
            <?php if ($invoice['customer_email']): ?>
                <div style="color: var(--text-muted); font-size: 0.875rem;">Email: <?php echo $invoice['customer_email']; ?></div>
            <?php endif; ?>
        </div>
        
        <!-- Items Table -->
        <div class="table-responsive" style="margin-bottom: 2rem;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 40px;" class="text-center">#</th>
                        <th>Item / Layanan</th>
                        <th style="width: 100px;" class="text-center">Qty</th>
                        <th style="width: 150px;" class="text-right">Harga Satuan</th>
                        <th style="width: 150px;" class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $index => $item): ?>
                        <tr>
                            <td class="text-center"><?php echo $index + 1; ?></td>
                            <td>
                                <div style="font-weight: 600;"><?php echo $item['item_name']; ?></div>
                                <?php if ($item['item_description']): ?>
                                    <div style="font-size: 0.813rem; color: var(--text-muted); margin-top: 0.25rem;"><?php echo $item['item_description']; ?></div>
                                <?php endif; ?>
                                <?php if ($item['category']): ?>
                                    <span class="badge badge-secondary" style="margin-top: 0.25rem; font-size: 0.75rem;">
                                        <?php echo ucfirst(str_replace('_', ' ', $item['category'])); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?php echo number_format($item['quantity'], 0, ',', '.'); ?></td>
                            <td class="text-right">Rp <?php echo number_format($item['unit_price'], 0, ',', '.'); ?></td>
                            <td class="text-right" style="font-weight: 700;">Rp <?php echo number_format($item['total_price'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Summary -->
        <div style="display: flex; justify-content: flex-end;">
            <div style="width: 350px; background: var(--bg-secondary); padding: 1.5rem; border-radius: 0.75rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                    <span>Subtotal</span>
                    <span>Rp <?php echo number_format($invoice['subtotal'], 0, ',', '.'); ?></span>
                </div>
                <?php if ($invoice['discount_amount'] > 0): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; color: #ef4444;">
                        <span>Diskon</span>
                        <span>- Rp <?php echo number_format($invoice['discount_amount'], 0, ',', '.'); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($invoice['tax_amount'] > 0): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                        <span>Pajak</span>
                        <span>Rp <?php echo number_format($invoice['tax_amount'], 0, ',', '.'); ?></span>
                    </div>
                <?php endif; ?>
                <div style="border-top: 2px solid var(--bg-tertiary); padding-top: 0.75rem; margin-top: 0.75rem; display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: 700; color: var(--primary-color);">
                    <span>TOTAL</span>
                    <span>Rp <?php echo number_format($invoice['total_amount'], 0, ',', '.'); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Notes -->
        <?php if ($invoice['notes']): ?>
            <div style="margin-top: 2rem; background: var(--bg-secondary); padding: 1.25rem; border-radius: 0.75rem;">
                <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.75rem;">Catatan:</div>
                <div style="color: var(--text-muted); font-size: 0.875rem;"><?php echo nl2br($invoice['notes']); ?></div>
            </div>
        <?php endif; ?>
        
        <!-- Footer Info -->
        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid var(--bg-tertiary); text-align: center; color: var(--text-muted); font-size: 0.875rem;">
            <div>Dibuat oleh: <?php echo $invoice['created_by_name']; ?> | Tanggal: <?php echo date('d/m/Y H:i', strtotime($invoice['created_at'])); ?></div>
            <div style="margin-top: 0.5rem;"><strong>Metode Pembayaran:</strong> <?php echo ucfirst($invoice['payment_method']); ?></div>
        </div>
    </div>
</div>

<script>
feather.replace();
</script>

<?php include '../../includes/footer.php'; ?>
