<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();
$pageTitle = 'Buat Invoice Baru';

// Get divisions
$divisions = $db->fetchAll("SELECT * FROM divisions WHERE is_active = 1 ORDER BY division_name");

// Handle form submission
if (isPost()) {
    try {
        // Debug: Log form data
        error_log("Form submitted with POST data: " . print_r($_POST, true));
        
        $db->getConnection()->beginTransaction();
        
        // Get form data
        $invoice_date = sanitize(getPost('invoice_date'));
        $customer_name = sanitize(getPost('customer_name'));
        $customer_phone = sanitize(getPost('customer_phone'));
        $customer_email = sanitize(getPost('customer_email'));
        $customer_address = sanitize(getPost('customer_address'));
        $division_id = (int)getPost('division_id');
        $payment_method = sanitize(getPost('payment_method'));
        $payment_status = sanitize(getPost('payment_status'));
        $notes = sanitize(getPost('notes'));
        
        // Get items
        $item_names = getPost('item_name') ?? [];
        $item_descriptions = getPost('item_description') ?? [];
        $categories = getPost('category') ?? [];
        $quantities = getPost('quantity') ?? [];
        $unit_prices = getPost('unit_price') ?? [];
        
        if (empty($item_names) || count($item_names) === 0) {
            throw new Exception('Minimal harus ada 1 item');
        }
        
        // Calculate totals
        $subtotal = 0;
        $items = [];
        foreach ($item_names as $index => $item_name) {
            if (empty($item_name)) continue;
            
            $qty = (float)str_replace(['.', ','], ['', '.'], $quantities[$index] ?? 1);
            $price = (float)str_replace(['.', ','], ['', '.'], $unit_prices[$index] ?? 0);
            $total = $qty * $price;
            
            $items[] = [
                'item_name' => trim($item_name),
                'item_description' => trim($item_descriptions[$index] ?? ''),
                'category' => trim($categories[$index] ?? ''),
                'quantity' => $qty,
                'unit_price' => $price,
                'total_price' => $total
            ];
            
            $subtotal += $total;
        }
        
        $discount_amount = (float)str_replace(['.', ','], ['', '.'], getPost('discount_amount') ?? 0);
        $tax_amount = (float)str_replace(['.', ','], ['', '.'], getPost('tax_amount') ?? 0);
        $total_amount = $subtotal - $discount_amount + $tax_amount;
        
        // Generate invoice number: INV-YYYYMM-XXXX
        $prefix = 'INV-' . date('Ym') . '-';
        $lastInvoice = $db->fetchOne("
            SELECT invoice_number 
            FROM sales_invoices_header 
            WHERE invoice_number LIKE ? 
            ORDER BY invoice_number DESC 
            LIMIT 1
        ", [$prefix . '%']);
        
        if ($lastInvoice) {
            $lastNumber = (int)substr($lastInvoice['invoice_number'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        $invoice_number = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        
        // Validate required data
        if (empty($customer_name)) {
            throw new Exception('Nama customer wajib diisi');
        }
        if (empty($division_id) || $division_id <= 0) {
            throw new Exception('Divisi wajib dipilih');
        }
        if (empty($currentUser['id'])) {
            throw new Exception('User ID tidak valid');
        }
        
        // Insert invoice header
        error_log("Attempting to insert invoice header");
        error_log("Customer: $customer_name, Division ID: $division_id, Total: $total_amount");
        
        $invoice_id = $db->insert('sales_invoices_header', [
            'invoice_number' => $invoice_number,
            'invoice_date' => $invoice_date,
            'customer_name' => $customer_name,
            'customer_phone' => $customer_phone,
            'customer_email' => $customer_email,
            'customer_address' => $customer_address,
            'division_id' => $division_id,
            'payment_method' => $payment_method,
            'payment_status' => $payment_status,
            'subtotal' => $subtotal,
            'discount_amount' => $discount_amount,
            'tax_amount' => $tax_amount,
            'total_amount' => $total_amount,
            'paid_amount' => $payment_status === 'paid' ? $total_amount : 0,
            'notes' => $notes,
            'created_by' => $currentUser['id']
        ]);
        
        error_log("Invoice header inserted with ID: $invoice_id");
        
        // Insert items
        error_log("Inserting " . count($items) . " items");
        foreach ($items as $item) {
            $item['invoice_header_id'] = $invoice_id;
            $result = $db->insert('sales_invoices_detail', $item);
            if (!$result) {
                throw new Exception('Gagal insert invoice detail');
            }
        }
        error_log("All items inserted successfully");
        
        // If paid, create cash book entry
        if ($payment_status === 'paid') {
            $category = $db->fetchOne("SELECT id FROM categories WHERE category_name = 'Penjualan' AND transaction_type = 'income'");
            
            if (!$category) {
                $category_id = $db->insert('categories', [
                    'category_name' => 'Penjualan',
                    'transaction_type' => 'income',
                    'is_active' => 1
                ]);
            } else {
                $category_id = $category['id'];
            }
            
            $cash_book_id = $db->insert('cash_book', [
                'transaction_date' => $invoice_date,
                'transaction_time' => date('H:i:s'),
                'division_id' => $division_id,
                'category_id' => $category_id,
                'transaction_type' => 'income',
                'amount' => $total_amount,
                'description' => "Invoice $invoice_number - $customer_name",
                'payment_method' => $payment_method,
                'created_by' => $currentUser['id'],
                'source_type' => 'invoice',
                'source_id' => $invoice_id,
                'is_editable' => 0
            ]);
            
            // Update invoice with cash_book reference
            $db->update('sales_invoices_header', 
                ['cash_book_id' => $cash_book_id],
                'id = :id',
                ['id' => $invoice_id]
            );
        }
        
        $db->getConnection()->commit();
        
        error_log("Transaction committed successfully: $invoice_number (ID: $invoice_id)");
        
        $_SESSION['success'] = "Invoice $invoice_number berhasil dibuat!";
        
        $redirect_url = BASE_URL . '/modules/sales/index.php';
        error_log("Redirecting to: $redirect_url");
        
        redirect($redirect_url);
        exit;
        
    } catch (Exception $e) {
        $db->getConnection()->rollBack();
        error_log("Error creating invoice: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
}

include '../../includes/header.php';
?>

<?php if (isset($_SESSION['error'])): ?>
    <div style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-left: 4px solid #ef4444; padding: 1.25rem 1.5rem; border-radius: 0.75rem; margin-bottom: 1.5rem; box-shadow: 0 4px 12px rgba(239,68,68,0.15);">
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
.item-row {
    background: var(--bg-secondary);
    padding: 1rem;
    border-radius: 0.75rem;
    margin-bottom: 1rem;
    border: 1px solid var(--bg-tertiary);
}

.remove-item-btn {
    background: #ef4444;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    cursor: pointer;
    font-size: 0.875rem;
}

.remove-item-btn:hover {
    background: #dc2626;
}

#totalSummary {
    position: sticky;
    bottom: 1rem;
    background: linear-gradient(135deg, var(--primary-color)15, var(--bg-secondary));
    border: 2px solid var(--primary-color);
    padding: 1.5rem;
    border-radius: 0.75rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
</style>

<div class="card" style="max-width: 1000px; margin: 0 auto;">
    <form method="POST" id="invoiceForm">
        <div style="padding: 1.25rem; border-bottom: 1px solid var(--bg-tertiary); background: linear-gradient(135deg, var(--primary-color)15, var(--bg-secondary));">
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem; margin: 0;">
                <i data-feather="file-plus" style="width: 22px; height: 22px;"></i> Buat Invoice Baru
            </h3>
        </div>
        
        <div style="padding: 1.5rem;">
            <!-- Customer Info -->
            <div style="background: var(--bg-secondary); padding: 1.25rem; border-radius: 0.75rem; margin-bottom: 1.5rem;">
                <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-primary);">Informasi Customer</h4>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Nama Customer <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="customer_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">No. Telepon</label>
                        <input type="text" name="customer_phone" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="customer_email" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Alamat</label>
                        <textarea name="customer_address" class="form-control" rows="2" style="resize: none;"></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Invoice Info -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">Tanggal Invoice <span style="color: var(--danger);">*</span></label>
                    <input type="date" name="invoice_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Divisi <span style="color: var(--danger);">*</span></label>
                    <select name="division_id" class="form-control" required>
                        <option value="">-- Pilih Divisi --</option>
                        <?php foreach ($divisions as $div): ?>
                            <option value="<?php echo $div['id']; ?>"><?php echo $div['division_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Metode Pembayaran <span style="color: var(--danger);">*</span></label>
                    <select name="payment_method" class="form-control" required>
                        <option value="cash">Cash</option>
                        <option value="debit">Debit Card</option>
                        <option value="transfer">Transfer</option>
                        <option value="qr">QR Code</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
            </div>
            
            <!-- Items Section -->
            <div style="background: var(--bg-secondary); padding: 1.25rem; border-radius: 0.75rem; margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h4 style="font-size: 1rem; font-weight: 700; margin: 0; color: var(--text-primary);">Item / Layanan</h4>
                    <button type="button" class="btn btn-primary btn-sm" onclick="addItem()">
                        <i data-feather="plus" style="width: 14px; height: 14px;"></i> Tambah Item
                    </button>
                </div>
                
                <div id="itemsContainer">
                    <!-- Items will be added here -->
                </div>
            </div>
            
            <!-- Summary -->
            <div id="totalSummary">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">Diskon (Rp)</label>
                        <input type="text" name="discount_amount" id="discount_amount" class="form-control" value="0" onkeyup="calculateTotal()">
                    </div>
                    
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">Pajak (Rp)</label>
                        <input type="text" name="tax_amount" id="tax_amount" class="form-control" value="0" onkeyup="calculateTotal()">
                    </div>
                    
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">Status Pembayaran <span style="color: var(--danger);">*</span></label>
                        <select name="payment_status" class="form-control" required>
                            <option value="paid">Lunas</option>
                            <option value="partial">Sebagian</option>
                            <option value="unpaid">Belum Bayar</option>
                        </select>
                    </div>
                </div>
                
                <div style="border-top: 2px solid var(--bg-tertiary); padding-top: 1rem; display: grid; grid-template-columns: 1fr auto; gap: 2rem;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2" style="resize: none;" placeholder="Catatan tambahan (opsional)"></textarea>
                    </div>
                    
                    <div style="text-align: right;">
                        <div style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.25rem;">Subtotal</div>
                        <div style="font-size: 1.125rem; color: var(--text-primary); margin-bottom: 0.75rem;" id="subtotal_display">Rp 0</div>
                        
                        <div style="font-size: 1.25rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.25rem;">TOTAL</div>
                        <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color);" id="total_display">Rp 0</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Form Actions -->
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--bg-tertiary); background: var(--bg-secondary); display: flex; justify-content: flex-end; gap: 0.75rem;">
            <a href="index.php" class="btn btn-secondary">
                <i data-feather="x" style="width: 16px; height: 16px;"></i> Batal
            </a>
            <button type="submit" class="btn btn-primary">
                <i data-feather="save" style="width: 16px; height: 16px;"></i> Simpan Invoice
            </button>
        </div>
    </form>
</div>

<script>
let itemCount = 0;

function addItem() {
    itemCount++;
    const container = document.getElementById('itemsContainer');
    const itemRow = document.createElement('div');
    itemRow.className = 'item-row';
    itemRow.id = `item-${itemCount}`;
    
    itemRow.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
            <h5 style="font-size: 0.875rem; font-weight: 600; margin: 0; color: var(--text-primary);">Item #${itemCount}</h5>
            <button type="button" class="remove-item-btn" onclick="removeItem(${itemCount})">
                <i data-feather="x" style="width: 14px; height: 14px;"></i> Hapus
            </button>
        </div>
        
        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr; gap: 0.75rem;">
            <div>
                <label style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.25rem; display: block;">Nama Item *</label>
                <input type="text" name="item_name[]" class="form-control" required>
            </div>
            
            <div>
                <label style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.25rem; display: block;">Kategori</label>
                <select name="category[]" class="form-control">
                    <option value="">-- Pilih --</option>
                    <option value="rental_motor">Rental Motor</option>
                    <option value="rental_mobil">Rental Mobil</option>
                    <option value="laundry">Laundry</option>
                    <option value="tour">Tour/Trip</option>
                    <option value="other">Lainnya</option>
                </select>
            </div>
            
            <div>
                <label style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.25rem; display: block;">Qty *</label>
                <input type="number" name="quantity[]" class="form-control" value="1" min="0.01" step="0.01" required onkeyup="calculateTotal()">
            </div>
            
            <div>
                <label style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.25rem; display: block;">Harga Satuan *</label>
                <input type="text" name="unit_price[]" class="form-control" value="0" required onkeyup="calculateTotal()">
            </div>
            
            <div>
                <label style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.25rem; display: block;">Total</label>
                <input type="text" class="form-control item-total" readonly style="background: var(--bg-tertiary); font-weight: 700;">
            </div>
        </div>
        
        <div style="margin-top: 0.75rem;">
            <label style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.25rem; display: block;">Deskripsi</label>
            <textarea name="item_description[]" class="form-control" rows="1" style="resize: none; font-size: 0.813rem;"></textarea>
        </div>
    `;
    
    container.appendChild(itemRow);
    feather.replace();
    calculateTotal();
}

function removeItem(id) {
    const item = document.getElementById(`item-${id}`);
    if (item) {
        item.remove();
        calculateTotal();
    }
}

function calculateTotal() {
    const quantities = document.getElementsByName('quantity[]');
    const unitPrices = document.getElementsByName('unit_price[]');
    const itemTotals = document.querySelectorAll('.item-total');
    
    let subtotal = 0;
    
    for (let i = 0; i < quantities.length; i++) {
        const qty = parseFloat(quantities[i].value) || 0;
        const price = parseFloat(unitPrices[i].value.replace(/[^0-9.-]+/g, '')) || 0;
        const total = qty * price;
        
        itemTotals[i].value = 'Rp ' + total.toLocaleString('id-ID');
        subtotal += total;
    }
    
    const discount = parseFloat(document.getElementById('discount_amount').value.replace(/[^0-9.-]+/g, '')) || 0;
    const tax = parseFloat(document.getElementById('tax_amount').value.replace(/[^0-9.-]+/g, '')) || 0;
    const grandTotal = subtotal - discount + tax;
    
    document.getElementById('subtotal_display').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
    document.getElementById('total_display').textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
}

// Add first item on load
document.addEventListener('DOMContentLoaded', function() {
    addItem();
    
    // Form validation
    const form = document.getElementById('invoiceForm');
    form.addEventListener('submit', function(e) {
        console.log('Form is being submitted');
        
        // Check if at least one item exists
        const itemsContainer = document.getElementById('itemsContainer');
        if (itemsContainer.children.length === 0) {
            e.preventDefault();
            alert('Harap tambahkan minimal 1 item!');
            return false;
        }
        
        // Validate all items have name
        const itemNames = document.getElementsByName('item_name[]');
        for (let i = 0; i < itemNames.length; i++) {
            if (!itemNames[i].value.trim()) {
                e.preventDefault();
                alert('Semua item harus memiliki nama!');
                return false;
            }
        }
        
        console.log('Form validation passed, submitting...');
        return true;
    });
});

feather.replace();
</script>

<?php include '../../includes/footer.php'; ?>
