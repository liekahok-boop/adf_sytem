<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();
$pageTitle = 'Add Room Type';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $typeName = trim($_POST['type_name']);
    $basePrice = floatval($_POST['base_price']);
    $colorCode = $_POST['color_code'];
    $description = trim($_POST['description']);
    
    // Check if type name already exists
    $existing = $db->fetchOne("SELECT id FROM room_types WHERE type_name = ?", [$typeName]);
    
    if ($existing) {
        setFlash('error', 'Room type dengan nama ini sudah ada!');
    } else {
        try {
            $db->insert('room_types', [
                'type_name' => $typeName,
                'base_price' => $basePrice,
                'color_code' => $colorCode,
                'description' => $description
            ]);
            
            setFlash('success', 'Room type berhasil ditambahkan!');
            header('Location: manage-room-types.php');
            exit;
        } catch (Exception $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
    }
}

include '../../includes/header.php';
?>

<style>
.color-picker-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(50px, 1fr));
    gap: 0.5rem;
}

.color-option {
    width: 100%;
    height: 50px;
    border-radius: 8px;
    border: 3px solid transparent;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
}

.color-option:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.color-option.selected {
    border-color: white;
    box-shadow: 0 0 0 2px var(--primary-color);
}

.color-option.selected::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 1.5rem;
    font-weight: 800;
}

.preview-card {
    background: var(--bg-secondary);
    border: 2px solid var(--bg-tertiary);
    border-radius: 12px;
    padding: 1.5rem;
}

.preview-type-icon {
    width: 80px;
    height: 80px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}
</style>

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.25rem;">
            ➕ Add Room Type
        </h2>
        <p style="color: var(--text-muted); font-size: 0.813rem;">Buat tipe kamar baru</p>
    </div>
    <a href="manage-room-types.php" class="btn btn-secondary">
        <i data-feather="arrow-left" style="width: 16px; height: 16px;"></i>
        Back to List
    </a>
</div>

<div style="display: grid; grid-template-columns: 1fr 400px; gap: 1.5rem;">
    
    <!-- Form -->
    <div class="card">
        <div style="padding: 1rem; border-bottom: 1px solid var(--bg-tertiary);">
            <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                Type Information
            </h3>
        </div>
        
        <form method="POST" style="padding: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Type Name *</label>
                <input type="text" name="type_name" class="form-control" 
                       required maxlength="50" id="typeName"
                       placeholder="Standard, Deluxe, Suite, dll">
            </div>
            
            <div class="form-group">
                <label class="form-label">Base Price (Rp) *</label>
                <input type="number" name="base_price" class="form-control" 
                       required min="0" step="1000" id="basePrice"
                       placeholder="500000">
                <small style="color: var(--text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                    Harga dasar per malam
                </small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Color Code *</label>
                <div class="color-picker-grid">
                    <?php
                    $colors = [
                        '#6366f1' => 'Indigo',
                        '#8b5cf6' => 'Purple',
                        '#ec4899' => 'Pink',
                        '#ef4444' => 'Red',
                        '#f59e0b' => 'Orange',
                        '#10b981' => 'Green',
                        '#06b6d4' => 'Cyan',
                        '#3b82f6' => 'Blue',
                        '#14b8a6' => 'Teal',
                        '#f97316' => 'Deep Orange'
                    ];
                    foreach ($colors as $color => $name):
                    ?>
                        <div class="color-option <?php echo $color == '#6366f1' ? 'selected' : ''; ?>" 
                             style="background: <?php echo $color; ?>;"
                             onclick="selectColor('<?php echo $color; ?>')"
                             title="<?php echo $name; ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="color_code" id="color_code" value="#6366f1">
            </div>
            
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4" id="description"
                          placeholder="Deskripsi tipe kamar, fasilitas yang tersedia, dll..."></textarea>
            </div>
            
            <div style="display: flex; gap: 0.75rem; padding-top: 1rem; border-top: 1px solid var(--bg-tertiary);">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i data-feather="save" style="width: 16px; height: 16px;"></i>
                    Save Room Type
                </button>
                <a href="manage-room-types.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    
    <!-- Preview -->
    <div>
        <div class="card" style="margin-bottom: 1.5rem;">
            <div style="padding: 1rem; border-bottom: 1px solid var(--bg-tertiary);">
                <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                    Preview
                </h3>
            </div>
            <div class="preview-card">
                <div class="preview-type-icon" id="previewIcon" style="background: #6366f120;">
                    <i data-feather="layers" style="width: 40px; height: 40px; color: #6366f1;" id="previewIconFeather"></i>
                </div>
                
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <h4 style="font-size: 1.25rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.5rem;" id="previewName">
                        Room Type
                    </h4>
                    <div style="font-size: 1.5rem; font-weight: 700; color: #10b981;" id="previewPrice">
                        Rp 0
                    </div>
                </div>
                
                <div style="background: var(--bg-tertiary); border-radius: 8px; padding: 1rem;">
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.5rem;">Description</div>
                    <div style="font-size: 0.813rem; color: var(--text-secondary); line-height: 1.5;" id="previewDesc">
                        Belum ada deskripsi
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Info -->
        <div class="card">
            <div style="padding: 1rem;">
                <h4 style="font-size: 0.938rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.75rem;">
                    <i data-feather="info" style="width: 16px; height: 16px;"></i>
                    Tips
                </h4>
                <ul style="font-size: 0.813rem; color: var(--text-secondary); line-height: 1.6; margin: 0; padding-left: 1.25rem;">
                    <li>Type name harus unik (Standard, Deluxe, Suite)</li>
                    <li>Base price adalah harga dasar per malam</li>
                    <li>Color code untuk membedakan di denah & report</li>
                    <li>Deskripsi bisa diisi dengan fasilitas room</li>
                    <li>Type yang sudah digunakan tidak bisa dihapus</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
feather.replace();

let currentColor = '#6366f1';

// Color picker
function selectColor(color) {
    currentColor = color;
    document.querySelectorAll('.color-option').forEach(el => el.classList.remove('selected'));
    event.target.classList.add('selected');
    document.getElementById('color_code').value = color;
    
    // Update preview
    document.getElementById('previewIcon').style.background = color + '20';
    document.getElementById('previewIconFeather').style.color = color;
}

// Live preview
document.getElementById('typeName').addEventListener('input', function() {
    document.getElementById('previewName').textContent = this.value || 'Room Type';
});

document.getElementById('basePrice').addEventListener('input', function() {
    const price = parseInt(this.value) || 0;
    document.getElementById('previewPrice').textContent = 'Rp ' + price.toLocaleString('id-ID');
});

document.getElementById('description').addEventListener('input', function() {
    document.getElementById('previewDesc').textContent = this.value || 'Belum ada deskripsi';
});
</script>

<?php include '../../includes/footer.php'; ?>
