<?php
/**
 * Migration: Add branch_id to cash_book and frontdesk_rooms
 * This fixes the health analysis to work for all businesses
 */
require_once 'config/config.php';
require_once 'config/database.php';

header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Add Branch ID Migration</title>
    <style>
        body { 
            font-family: monospace; 
            background: #1a1a2e; 
            color: #eee; 
            padding: 20px; 
            max-width: 900px;
            margin: 0 auto;
        }
        .success { 
            color: #10b981; 
            background: rgba(16, 185, 129, 0.1); 
            padding: 15px; 
            border-radius: 8px; 
            margin: 10px 0; 
            border-left: 4px solid #10b981;
        }
        .error { 
            color: #ef4444; 
            background: rgba(239, 68, 68, 0.1); 
            padding: 15px; 
            border-radius: 8px; 
            margin: 10px 0; 
            border-left: 4px solid #ef4444;
        }
        .warning { 
            color: #f59e0b; 
            background: rgba(245, 158, 11, 0.1); 
            padding: 15px; 
            border-radius: 8px; 
            margin: 10px 0; 
            border-left: 4px solid #f59e0b;
        }
        .info { 
            color: #3b82f6; 
            background: rgba(59, 130, 246, 0.1); 
            padding: 15px; 
            border-radius: 8px; 
            margin: 10px 0; 
            border-left: 4px solid #3b82f6;
        }
        h1 { color: #6366f1; }
        h2 { color: #8b5cf6; margin-top: 30px; }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #6366f1;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px 10px 0;
            font-weight: bold;
        }
        .btn:hover { background: #4f46e5; }
        pre { 
            background: #0f172a; 
            padding: 15px; 
            border-radius: 8px; 
            overflow: auto;
            border: 1px solid #334155;
        }
    </style>
</head>
<body>
    <h1>🔧 Migration: Add branch_id to Tables</h1>
    <p>This migration adds branch_id column to cash_book and frontdesk_rooms tables to enable per-branch health analysis.</p>
    <hr>";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<h2>Step 1: Check Current Tables</h2>";
    
    // Check if cash_book exists
    $tables = $conn->query("SHOW TABLES LIKE 'cash_book'")->fetchAll();
    if (count($tables) > 0) {
        echo "<div class='info'>✓ Table 'cash_book' exists</div>";
        
        // Check if branch_id already exists
        $columns = $conn->query("SHOW COLUMNS FROM cash_book LIKE 'branch_id'")->fetchAll();
        if (count($columns) > 0) {
            echo "<div class='warning'>⚠️ Column 'branch_id' already exists in cash_book</div>";
        } else {
            echo "<div class='info'>→ Column 'branch_id' needs to be added to cash_book</div>";
        }
    } else {
        echo "<div class='warning'>⚠️ Table 'cash_book' does not exist</div>";
    }
    
    // Check if frontdesk_rooms exists
    $tables = $conn->query("SHOW TABLES LIKE 'frontdesk_rooms'")->fetchAll();
    if (count($tables) > 0) {
        echo "<div class='info'>✓ Table 'frontdesk_rooms' exists</div>";
        
        // Check if branch_id already exists
        $columns = $conn->query("SHOW COLUMNS FROM frontdesk_rooms LIKE 'branch_id'")->fetchAll();
        if (count($columns) > 0) {
            echo "<div class='warning'>⚠️ Column 'branch_id' already exists in frontdesk_rooms</div>";
        } else {
            echo "<div class='info'>→ Column 'branch_id' needs to be added to frontdesk_rooms</div>";
        }
    } else {
        echo "<div class='warning'>⚠️ Table 'frontdesk_rooms' does not exist</div>";
    }
    
    echo "<h2>Step 2: Add branch_id to cash_book</h2>";
    
    // Check if cash_book has branch_id
    $columns = $conn->query("SHOW COLUMNS FROM cash_book LIKE 'branch_id'")->fetchAll();
    if (count($columns) == 0) {
        try {
            // Add branch_id column
            $conn->exec("ALTER TABLE cash_book ADD COLUMN branch_id INT DEFAULT 1 AFTER id");
            echo "<div class='success'>✅ Added 'branch_id' column to cash_book</div>";
            
            // Add index for better performance
            $conn->exec("ALTER TABLE cash_book ADD INDEX idx_branch_id (branch_id)");
            echo "<div class='success'>✅ Added index on branch_id</div>";
            
            // Try to add foreign key if branches table exists
            try {
                $conn->exec("ALTER TABLE cash_book ADD CONSTRAINT fk_cash_book_branch 
                            FOREIGN KEY (branch_id) REFERENCES branches(id)");
                echo "<div class='success'>✅ Added foreign key constraint to branches table</div>";
            } catch (Exception $e) {
                echo "<div class='warning'>⚠️ Could not add foreign key (branches table might not exist): " . $e->getMessage() . "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error adding branch_id to cash_book: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='info'>ℹ️ Column 'branch_id' already exists in cash_book</div>";
    }
    
    echo "<h2>Step 3: Add branch_id to frontdesk_rooms</h2>";
    
    // Check if frontdesk_rooms exists first
    $tables = $conn->query("SHOW TABLES LIKE 'frontdesk_rooms'")->fetchAll();
    if (count($tables) > 0) {
        $columns = $conn->query("SHOW COLUMNS FROM frontdesk_rooms LIKE 'branch_id'")->fetchAll();
        if (count($columns) == 0) {
            try {
                // Add branch_id column
                $conn->exec("ALTER TABLE frontdesk_rooms ADD COLUMN branch_id INT DEFAULT 1 AFTER id");
                echo "<div class='success'>✅ Added 'branch_id' column to frontdesk_rooms</div>";
                
                // Add index
                $conn->exec("ALTER TABLE frontdesk_rooms ADD INDEX idx_branch_id (branch_id)");
                echo "<div class='success'>✅ Added index on branch_id</div>";
                
                // Try to add foreign key
                try {
                    $conn->exec("ALTER TABLE frontdesk_rooms ADD CONSTRAINT fk_frontdesk_rooms_branch 
                                FOREIGN KEY (branch_id) REFERENCES branches(id)");
                    echo "<div class='success'>✅ Added foreign key constraint to branches table</div>";
                } catch (Exception $e) {
                    echo "<div class='warning'>⚠️ Could not add foreign key: " . $e->getMessage() . "</div>";
                }
                
            } catch (Exception $e) {
                echo "<div class='error'>❌ Error adding branch_id to frontdesk_rooms: " . $e->getMessage() . "</div>";
            }
        } else {
            echo "<div class='info'>ℹ️ Column 'branch_id' already exists in frontdesk_rooms</div>";
        }
    } else {
        echo "<div class='info'>ℹ️ Table 'frontdesk_rooms' does not exist (skipping)</div>";
    }
    
    echo "<h2>Step 4: Update Existing Data</h2>";
    echo "<div class='info'>All existing records will have branch_id = 1 (default branch)</div>";
    
    // Check data distribution
    $cashBookCount = $conn->query("SELECT COUNT(*) as cnt FROM cash_book")->fetch()['cnt'];
    echo "<div class='info'>📊 Total records in cash_book: " . number_format($cashBookCount) . "</div>";
    
    if ($tables = $conn->query("SHOW TABLES LIKE 'frontdesk_rooms'")->fetchAll()) {
        $roomsCount = $conn->query("SELECT COUNT(*) as cnt FROM frontdesk_rooms")->fetch()['cnt'];
        echo "<div class='info'>📊 Total records in frontdesk_rooms: " . number_format($roomsCount) . "</div>";
    }
    
    echo "<h2>✅ Migration Complete!</h2>";
    echo "<div class='success'><strong>Health Analysis is now ready for all businesses!</strong><br>
    The system can now track financial health separately for each branch.</div>";
    
    echo "<p><a href='modules/owner/health-report.php' class='btn'>Go to Health Report</a>";
    echo "<a href='modules/owner/dashboard.php' class='btn'>Go to Owner Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Fatal Error: " . $e->getMessage() . "</div>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</body></html>";
?>
