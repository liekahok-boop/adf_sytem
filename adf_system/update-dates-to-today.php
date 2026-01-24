<?php
/**
 * Update some transactions to today's date for demo purposes
 */
require_once 'config/database.php';

header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Update Transaction Dates</title>
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
        .info { 
            color: #3b82f6; 
            background: rgba(59, 130, 246, 0.1); 
            padding: 15px; 
            border-radius: 8px; 
            margin: 10px 0; 
            border-left: 4px solid #3b82f6;
        }
        h1 { color: #6366f1; }
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
    </style>
</head>
<body>
    <h1>🔄 Update Transaction Dates to Today</h1>
    <p>This will update some transactions from each branch to today's date for chart display.</p>
    <hr>";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $today = date('Y-m-d');
    
    echo "<h2>Current Date: $today</h2>";
    
    // Update 2 latest transactions per branch to today
    for ($branchId = 1; $branchId <= 6; $branchId++) {
        // Get latest 2 transactions for this branch
        $transactions = $db->fetchAll(
            "SELECT id, description, transaction_type, amount, transaction_date 
             FROM cash_book 
             WHERE branch_id = ? 
             ORDER BY transaction_date DESC 
             LIMIT 2",
            [$branchId]
        );
        
        if (count($transactions) > 0) {
            echo "<div class='info'><strong>Branch ID: $branchId</strong></div>";
            
            foreach ($transactions as $trans) {
                $stmt = $conn->prepare("UPDATE cash_book SET transaction_date = ? WHERE id = ?");
                $stmt->execute([$today, $trans['id']]);
                
                echo "<div class='success'>
                    ✅ Updated Transaction ID {$trans['id']}<br>
                    - Type: {$trans['transaction_type']}<br>
                    - Amount: Rp " . number_format($trans['amount'], 0, ',', '.') . "<br>
                    - Old Date: {$trans['transaction_date']} → New Date: $today
                </div>";
            }
        }
    }
    
    // Show summary
    $todayStats = $db->fetchAll("
        SELECT branch_id,
               COUNT(*) as count,
               SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END) as income,
               SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) as expense
        FROM cash_book
        WHERE transaction_date = ?
        GROUP BY branch_id
        ORDER BY branch_id
    ", [$today]);
    
    echo "<h2>✅ Summary: Today's Transactions ($today)</h2>";
    echo "<table style='width: 100%; border-collapse: collapse; background: #0f172a; border-radius: 8px; overflow: hidden;'>
        <tr style='background: #1e293b; color: #6366f1;'>
            <th style='padding: 12px; text-align: left; border-bottom: 2px solid #334155;'>Branch ID</th>
            <th style='padding: 12px; text-align: right; border-bottom: 2px solid #334155;'>Count</th>
            <th style='padding: 12px; text-align: right; border-bottom: 2px solid #334155;'>Income</th>
            <th style='padding: 12px; text-align: right; border-bottom: 2px solid #334155;'>Expense</th>
        </tr>";
    
    foreach ($todayStats as $stat) {
        echo "<tr>
            <td style='padding: 12px; border-bottom: 1px solid #334155;'>Branch {$stat['branch_id']}</td>
            <td style='padding: 12px; text-align: right; border-bottom: 1px solid #334155;'>{$stat['count']}</td>
            <td style='padding: 12px; text-align: right; color: #10b981; border-bottom: 1px solid #334155;'>Rp " . number_format($stat['income'], 0, ',', '.') . "</td>
            <td style='padding: 12px; text-align: right; color: #ef4444; border-bottom: 1px solid #334155;'>Rp " . number_format($stat['expense'], 0, ',', '.') . "</td>
        </tr>";
    }
    
    echo "</table>";
    
    echo "<div class='success' style='margin-top: 20px; font-size: 1.1rem;'>
        <strong>🎉 Update Complete!</strong><br>
        All branches now have transactions for today. The comparison chart should display data now.
    </div>";
    
    echo "<p><a href='modules/owner/dashboard.php' class='btn'>Go to Owner Dashboard</a>";
    echo "<a href='check-branch-data.php' class='btn'>Check Data Again</a></p>";
    
} catch (Exception $e) {
    echo "<div style='color: #ef4444; background: rgba(239, 68, 68, 0.1); padding: 15px; border-radius: 8px; border-left: 4px solid #ef4444;'>
        ❌ Error: " . $e->getMessage() . "
    </div>";
}

echo "</body></html>";
?>
