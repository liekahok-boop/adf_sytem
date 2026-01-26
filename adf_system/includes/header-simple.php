<?php
// Module Header - Simplified version
if (!defined('APP_ACCESS')) die('Access denied');

// Ensure session started
if (session_status() === PHP_SESSION_NONE) {
    if (defined('SESSION_NAME')) session_name(SESSION_NAME);
    session_start();
}

$auth = $auth ?? new Auth();
$currentUser = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
</head>
<body data-theme="dark">
    <aside class="sidebar">
        <div style="padding:1.5rem;border-bottom:1px solid #2d3748;">
            <h2 style="color:#64b5f6;margin:0;">Narayana Hotel</h2>
            <p style="color:#718096;font-size:0.875rem;margin:0.25rem 0 0;">Hotel System</p>
        </div>
        
        <nav style="padding:1rem 0;">
            <ul style="list-style:none;padding:0;margin:0;">
                <li><a href="<?php echo BASE_URL; ?>/index.php" style="display:flex;align-items:center;padding:0.75rem 1.5rem;color:#e2e8f0;text-decoration:none;">
                    <i data-feather="home" style="width:20px;height:20px;margin-right:0.75rem;"></i>Dashboard
                </a></li>
                
                <?php if ($auth->hasPermission('investor')): ?>
                <li><a href="<?php echo BASE_URL; ?>/modules/investor/" style="display:flex;align-items:center;padding:0.75rem 1.5rem;color:#e2e8f0;text-decoration:none;background:rgba(100,181,246,0.1);">
                    <i data-feather="briefcase" style="width:20px;height:20px;margin-right:0.75rem;"></i>Investor
                </a></li>
                <?php endif; ?>
                
                <?php if ($auth->hasPermission('project')): ?>
                <li><a href="<?php echo BASE_URL; ?>/modules/project/" style="display:flex;align-items:center;padding:0.75rem 1.5rem;color:#e2e8f0;text-decoration:none;">
                    <i data-feather="layers" style="width:20px;height:20px;margin-right:0.75rem;"></i>Project
                </a></li>
                <?php endif; ?>
                
                <?php if ($auth->hasPermission('settings')): ?>
                <li><a href="<?php echo BASE_URL; ?>/modules/settings/" style="display:flex;align-items:center;padding:0.75rem 1.5rem;color:#e2e8f0;text-decoration:none;">
                    <i data-feather="settings" style="width:20px;height:20px;margin-right:0.75rem;"></i>Settings
                </a></li>
                <?php endif; ?>
                
                <li><a href="<?php echo BASE_URL; ?>/logout.php" style="display:flex;align-items:center;padding:0.75rem 1.5rem;color:#ef4444;text-decoration:none;">
                    <i data-feather="log-out" style="width:20px;height:20px;margin-right:0.75rem;"></i>Logout
                </a></li>
            </ul>
        </nav>
    </aside>
    <script>feather.replace();</script>
