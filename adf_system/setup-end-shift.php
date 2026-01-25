<?php
/**
 * End Shift Feature Setup Installer
 * Run this to setup database tables and configure settings
 */

define('APP_ACCESS', true);
require_once 'config/config.php';
require_once 'config/database.php';

// No authentication required for setup
$db = Database::getInstance();

// Track setup steps
$setupSteps = [];
$errors = [];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>End Shift Feature Setup</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            max-width: 700px;
            width: 100%;
            padding: 2rem;
        }
        h1 {
            color: #333;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .step {
            margin-bottom: 1.5rem;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #ddd;
        }
        .step.success {
            background: #e8f5e9;
            border-color: #4caf50;
        }
        .step.error {
            background: #ffebee;
            border-color: #f44336;
        }
        .step.pending {
            background: #f5f5f5;
            border-color: #999;
        }
        .step-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .step.success .step-title { color: #2e7d32; }
        .step.error .step-title { color: #c62828; }
        .step.pending .step-title { color: #666; }
        
        .step-message {
            font-size: 0.875rem;
            margin: 0.5rem 0;
            font-family: monospace;
        }
        .success .step-message { color: #1b5e20; }
        .error .step-message { color: #b71c1c; }
        .pending .step-message { color: #666; }
        
        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        button {
            flex: 1;
            padding: 0.75rem;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
            border: 1px solid #ddd;
        }
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        .info-box {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            color: #0d47a1;
        }
        code {
            background: #f5f5f5;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌅 End Shift Feature Setup</h1>
        
        <div class="info-box">
            ℹ️ This setup wizard will create necessary database tables and configure End Shift feature for your system.
        </div>

        <?php
        // Step 1: Create shift_logs table
        try {
            $db->execute("
                CREATE TABLE IF NOT EXISTS shift_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    action VARCHAR(50) NOT NULL,
                    data JSON,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    INDEX idx_user_id (user_id),
                    INDEX idx_created_at (created_at),
                    INDEX idx_action (action)
                )
            ");
            $setupSteps[] = [
                'status' => 'success',
                'title' => 'Create shift_logs table',
                'message' => 'Table created successfully for tracking shift changes'
            ];
        } catch (Exception $e) {
            $setupSteps[] = [
                'status' => 'error',
                'title' => 'Create shift_logs table',
                'message' => $e->getMessage()
            ];
            $errors[] = 'shift_logs table';
        }

        // Step 2: Add whatsapp_number to business_settings
        try {
            $db->execute("
                ALTER TABLE business_settings ADD COLUMN IF NOT EXISTS whatsapp_number VARCHAR(20)
            ");
            $setupSteps[] = [
                'status' => 'success',
                'title' => 'Add WhatsApp column to business_settings',
                'message' => 'Column whatsapp_number added successfully'
            ];
        } catch (Exception $e) {
            $setupSteps[] = [
                'status' => 'error',
                'title' => 'Add WhatsApp column to business_settings',
                'message' => $e->getMessage()
            ];
            $errors[] = 'business_settings column';
        }

        // Step 3: Add phone to users table
        try {
            $db->execute("
                ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20)
            ");
            $setupSteps[] = [
                'status' => 'success',
                'title' => 'Add phone column to users table',
                'message' => 'Column phone added successfully for user contact info'
            ];
        } catch (Exception $e) {
            $setupSteps[] = [
                'status' => 'error',
                'title' => 'Add phone column to users table',
                'message' => $e->getMessage()
            ];
            $errors[] = 'users phone column';
        }

        // Step 4: Create po_images table
        try {
            $db->execute("
                CREATE TABLE IF NOT EXISTS po_images (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    po_id INT NOT NULL,
                    image_path VARCHAR(255) NOT NULL,
                    is_primary BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (po_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
                    INDEX idx_po_id (po_id)
                )
            ");
            $setupSteps[] = [
                'status' => 'success',
                'title' => 'Create po_images table',
                'message' => 'Table created for storing PO image references'
            ];
        } catch (Exception $e) {
            $setupSteps[] = [
                'status' => 'error',
                'title' => 'Create po_images table',
                'message' => $e->getMessage()
            ];
            $errors[] = 'po_images table';
        }

        // Display setup steps
        foreach ($setupSteps as $step) {
            $icon = $step['status'] === 'success' ? '✓' : '✗';
            echo "
                <div class=\"step {$step['status']}\">
                    <div class=\"step-title\">
                        <span>{$icon}</span>
                        <span>{$step['title']}</span>
                    </div>
                    <div class=\"step-message\">{$step['message']}</div>
                </div>
            ";
        }

        // Overall status
        if (empty($errors)) {
            echo "
                <div class=\"step success\" style=\"margin-top: 2rem;\">
                    <div class=\"step-title\">✓ Setup Completed Successfully!</div>
                    <div class=\"step-message\">
                        All database tables created. You can now:
                        <br><br>
                        1. Login as Admin<br>
                        2. Go to Settings → End Shift Configuration<br>
                        3. Configure WhatsApp number and admin contact<br>
                        4. Staff can now use End Shift feature
                    </div>
                </div>
            ";
        } else {
            echo "
                <div class=\"step error\" style=\"margin-top: 2rem;\">
                    <div class=\"step-title\">⚠ Setup Incomplete</div>
                    <div class=\"step-message\">
                        Some steps failed. Please check:<br>
                        " . implode(', ', $errors) . "
                    </div>
                </div>
            ";
        }
        ?>

        <div class="button-group">
            <a href="<?php echo BASE_URL; ?>/index.php" style="flex: 1; text-decoration: none;">
                <button class="btn-primary">
                    <?php echo empty($errors) ? '✓ Go to Dashboard' : 'Go Back'; ?>
                </button>
            </a>
            <a href="<?php echo BASE_URL; ?>/modules/settings/end-shift.php" style="flex: 1; text-decoration: none;">
                <button class="btn-secondary">
                    ⚙️ Configure Settings
                </button>
            </a>
        </div>
    </div>
</body>
</html>
