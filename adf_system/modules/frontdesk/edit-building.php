<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance();
$currentUser = $auth->getCurrentUser();
$pageTitle = 'Edit Building';

$buildingId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get building data
$building = $db->fetchOne("SELECT * FROM buildings WHERE id = ?", [$buildingId]);

if (!$building) {
    header('Location: master-map.php');
    exit;
}

// Get room count
$roomCount = $db->fetchOne("SELECT COUNT(*) as total FROM rooms WHERE building_id = ?", [$buildingId])['total'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $buildingCode = strtoupper(trim($_POST['building_code']));
    $buildingName = trim($_POST['building_name']);
    $floors = intval($_POST['total_floors']);
    $colorTheme = $_POST['color_theme'];
    $positionX = floatval($_POST['position_x']);
    $positionY = floatval($_POST['position_y']);
    $rotationAngle = floatval($_POST['rotation_angle']);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    try {
        $db->update('buildings', [
            'building_code' => $buildingCode,
            'building_name' => $buildingName,
            'total_floors' => $floors,
            'color_theme' => $colorTheme,
            'position_x' => $positionX,
            'position_y' => $positionY,
            'rotation_angle' => $rotationAngle,
            'is_active' => $isActive
        ], 'id = :id', ['id' => $buildingId]);
        
        setFlash('success', 'Building berhasil diupdate!');
        header('Location: master-map.php');
        exit;
    } catch (Exception $e) {
        setFlash('error', 'Error: ' . $e->getMessage());
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
}

.color-option:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.color-option.selected {
    border-color: white;
    box-shadow: 0 0 0 2px var(--primary-color);
}

.preview-building {
    background: var(--bg-secondary);
    border: 2px solid var(--bg-tertiary);
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
}
</style>

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div>
        <h2 style="font-size: 1.75rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.5rem;">
            🏢 Edit Building
        </h2>
        <p style="color: var(--text-muted); font-size: 0.875rem;">
            <?php echo $building['building_code']; ?> - <?php echo $roomCount; ?> rooms
        </p>
    </div>
    <a href="master-map.php" class="btn btn-secondary">
        <i data-feather="arrow-left" style="width: 16px; height: 16px;"></i>
        Back to Map
    </a>
</div>

<div style="display: grid; grid-template-columns: 1fr 400px; gap: 1.5rem;">
    
    <!-- Edit Form -->
    <div>
        <div class="card">
            <div style="padding: 1rem; border-bottom: 1px solid var(--bg-tertiary);">
                <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                    Building Information
                </h3>
            </div>
            
            <form method="POST" style="padding: 1.5rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Building Code *</label>
                        <input type="text" name="building_code" class="form-control" 
                               value="<?php echo htmlspecialchars($building['building_code']); ?>" 
                               required maxlength="10"
                               placeholder="BLD-A">
                        <small style="color: var(--text-muted); font-size: 0.75rem;">Uppercase, max 10 chars</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Total Floors *</label>
                        <input type="number" name="total_floors" class="form-control" 
                               value="<?php echo $building['total_floors']; ?>" 
                               required min="1" max="20">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Building Name *</label>
                    <input type="text" name="building_name" class="form-control" 
                           value="<?php echo htmlspecialchars($building['building_name']); ?>" 
                           required maxlength="100"
                           placeholder="Building A (North)">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Color Theme *</label>
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
                            '#3b82f6' => 'Blue'
                        ];
                        foreach ($colors as $color => $name):
                        ?>
                            <div class="color-option <?php echo $building['color_theme'] == $color ? 'selected' : ''; ?>" 
                                 style="background: <?php echo $color; ?>;"
                                 onclick="selectColor('<?php echo $color; ?>')"
                                 title="<?php echo $name; ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="color_theme" id="color_theme" value="<?php echo $building['color_theme']; ?>">
                </div>
                
                <div style="padding: 1rem; background: var(--bg-secondary); border-radius: 8px; margin: 1.5rem 0;">
                    <h4 style="font-size: 0.938rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1rem;">
                        <i data-feather="map-pin" style="width: 16px; height: 16px;"></i>
                        Position & Rotation
                    </h4>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Position X</label>
                            <input type="number" name="position_x" class="form-control" 
                                   value="<?php echo $building['position_x']; ?>" 
                                   step="10" min="-500" max="500">
                            <small style="color: var(--text-muted); font-size: 0.7rem;">Horizontal</small>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Position Y</label>
                            <input type="number" name="position_y" class="form-control" 
                                   value="<?php echo $building['position_y']; ?>" 
                                   step="10" min="-300" max="300">
                            <small style="color: var(--text-muted); font-size: 0.7rem;">Vertical</small>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Rotation</label>
                            <input type="number" name="rotation_angle" class="form-control" 
                                   value="<?php echo $building['rotation_angle']; ?>" 
                                   step="15" min="0" max="360">
                            <small style="color: var(--text-muted); font-size: 0.7rem;">Degrees</small>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" 
                               <?php echo $building['is_active'] ? 'checked' : ''; ?>>
                        <span style="font-weight: 600; color: var(--text-primary);">Building is Active</span>
                    </label>
                    <small style="color: var(--text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                        Inactive buildings will be hidden from the map
                    </small>
                </div>
                
                <div style="display: flex; gap: 0.75rem; padding-top: 1rem; border-top: 1px solid var(--bg-tertiary);">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i data-feather="save" style="width: 16px; height: 16px;"></i>
                        Save Changes
                    </button>
                    <a href="master-map.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Preview & Info -->
    <div>
        <!-- Preview -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div style="padding: 1rem; border-bottom: 1px solid var(--bg-tertiary);">
                <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                    Preview
                </h3>
            </div>
            <div class="preview-building">
                <div style="width: 80px; height: 80px; margin: 0 auto; border-radius: 16px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(0,0,0,0.15); transition: all 0.3s ease;" id="previewIcon">
                    <i data-feather="home" style="width: 40px; height: 40px; color: white;"></i>
                </div>
                <div style="margin-top: 1rem;">
                    <div style="font-size: 1.25rem; font-weight: 800; color: var(--text-primary);" id="previewCode">
                        <?php echo $building['building_code']; ?>
                    </div>
                    <div style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem;" id="previewName">
                        <?php echo $building['building_name']; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="card">
            <div style="padding: 1rem;">
                <h4 style="font-size: 0.938rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.75rem;">
                    <i data-feather="bar-chart-2" style="width: 16px; height: 16px;"></i>
                    Building Stats
                </h4>
                <div style="display: grid; gap: 0.75rem;">
                    <div style="display: flex; justify-content: space-between; padding: 0.75rem; background: var(--bg-secondary); border-radius: 8px;">
                        <span style="color: var(--text-muted); font-size: 0.813rem;">Total Rooms</span>
                        <span style="font-weight: 700; color: var(--text-primary);"><?php echo $roomCount; ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 0.75rem; background: var(--bg-secondary); border-radius: 8px;">
                        <span style="color: var(--text-muted); font-size: 0.813rem;">Floors</span>
                        <span style="font-weight: 700; color: var(--text-primary);"><?php echo $building['total_floors']; ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 0.75rem; background: var(--bg-secondary); border-radius: 8px;">
                        <span style="color: var(--text-muted); font-size: 0.813rem;">Status</span>
                        <span style="font-weight: 700; color: <?php echo $building['is_active'] ? '#10b981' : '#ef4444'; ?>;">
                            <?php echo $building['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Info -->
        <div class="card" style="margin-top: 1rem;">
            <div style="padding: 1rem;">
                <h4 style="font-size: 0.938rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.75rem;">
                    <i data-feather="info" style="width: 16px; height: 16px;"></i>
                    Tips
                </h4>
                <ul style="font-size: 0.813rem; color: var(--text-secondary); line-height: 1.6; margin: 0; padding-left: 1.25rem;">
                    <li>Building code harus unik (BLD-A, BLD-B, dll)</li>
                    <li>Adjust position X/Y untuk mengatur posisi di map</li>
                    <li>Rotation: 0° = normal, 90° = ke kanan, 180° = terbalik</li>
                    <li>Non-aktifkan building untuk hide dari map</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
feather.replace();

// Color picker
function selectColor(color) {
    document.querySelectorAll('.color-option').forEach(el => el.classList.remove('selected'));
    event.target.classList.add('selected');
    document.getElementById('color_theme').value = color;
    document.getElementById('previewIcon').style.background = color;
}

// Live preview
document.querySelector('input[name="building_code"]').addEventListener('input', function() {
    document.getElementById('previewCode').textContent = this.value.toUpperCase() || 'BLD-?';
});

document.querySelector('input[name="building_name"]').addEventListener('input', function() {
    document.getElementById('previewName').textContent = this.value || 'Building Name';
});

// Initial preview color
document.getElementById('previewIcon').style.background = '<?php echo $building['color_theme']; ?>';
</script>

<?php include '../../includes/footer.php'; ?>
