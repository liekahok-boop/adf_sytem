<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

try {
    require_once '../config/database.php';
    require_once '../includes/auth.php';

    $auth = new Auth();
    if (!$auth->isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    // Check if user is owner or admin
    if (!$auth->hasRole('owner') && !$auth->hasRole('admin')) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }

    $db = Database::getInstance();
    $userId = $auth->getCurrentUser()['id'];
    $branchId = isset($_GET['branch_id']) ? intval($_GET['branch_id']) : null;
    $period = isset($_GET['period']) ? $_GET['period'] : '7days'; // 7days, 30days, 12months

    // Validate period
    $validPeriods = ['7days', '30days', '12months'];
    if (!in_array($period, $validPeriods)) {
        $period = '7days';
    }

    $data = [];
    
    if ($period === '7days') {
        // 7 days data
        $query = "SELECT 
                    DATE(transaction_date) as date,
                    SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END) as income,
                    SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) as expense
                  FROM cash_book
                  WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)";
        
        $params = [];
        if ($branchId) {
            $query .= " AND branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $query .= " GROUP BY DATE(transaction_date)
                   ORDER BY DATE(transaction_date) ASC";
        
        $results = $db->fetchAll($query, $params);
        
        // Fill in missing days
        $labels = [];
        $incomeData = [];
        $expenseData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('D, M j', strtotime($date));
            
            $found = false;
            foreach ($results as $row) {
                if ($row['date'] === $date) {
                    $incomeData[] = floatval($row['income']);
                    $expenseData[] = floatval($row['expense']);
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $incomeData[] = 0;
                $expenseData[] = 0;
            }
        }
        
        $data = [
            'labels' => $labels,
            'income' => $incomeData,
            'expense' => $expenseData
        ];
        
    } elseif ($period === '30days') {
        // Current month data (from 1st to last day of current month)
        $firstDay = date('Y-m-01'); // First day of current month
        $lastDay = date('Y-m-t');   // Last day of current month
        $daysInMonth = date('t');   // Number of days in current month
        
        $query = "SELECT 
                    DATE(transaction_date) as date,
                    SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END) as income,
                    SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) as expense
                  FROM cash_book
                  WHERE transaction_date >= :first_day AND transaction_date <= :last_day";
        
        $params = [
            'first_day' => $firstDay,
            'last_day' => $lastDay
        ];
        if ($branchId) {
            $query .= " AND branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $query .= " GROUP BY DATE(transaction_date)
                   ORDER BY DATE(transaction_date) ASC";
        
        $results = $db->fetchAll($query, $params);
        
        // Fill in missing days from 1 to end of month
        $labels = [];
        $incomeData = [];
        $expenseData = [];
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = date('Y-m-') . sprintf('%02d', $day);
            $labels[] = date('M j', strtotime($date));
            
            $found = false;
            foreach ($results as $row) {
                if ($row['date'] === $date) {
                    $incomeData[] = floatval($row['income']);
                    $expenseData[] = floatval($row['expense']);
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $incomeData[] = 0;
                $expenseData[] = 0;
            }
        }
        
        $data = [
            'labels' => $labels,
            'income' => $incomeData,
            'expense' => $expenseData
        ];
        
    } elseif ($period === '12months') {
        // Current year data (from January to December)
        $currentYear = date('Y');
        $firstDay = $currentYear . '-01-01';
        $lastDay = $currentYear . '-12-31';
        
        $query = "SELECT 
                    DATE_FORMAT(transaction_date, '%Y-%m') as month,
                    SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END) as income,
                    SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) as expense
                  FROM cash_book
                  WHERE transaction_date >= :first_day AND transaction_date <= :last_day";
        
        $params = [
            'first_day' => $firstDay,
            'last_day' => $lastDay
        ];
        if ($branchId) {
            $query .= " AND branch_id = :branch_id";
            $params['branch_id'] = $branchId;
        }
        
        $query .= " GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
                   ORDER BY DATE_FORMAT(transaction_date, '%Y-%m') ASC";
        
        $results = $db->fetchAll($query, $params);
        
        // Fill in all 12 months from January to December
        $labels = [];
        $incomeData = [];
        $expenseData = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthStr = $currentYear . '-' . sprintf('%02d', $month);
            $labels[] = date('M Y', strtotime($monthStr . '-01'));
            
            $found = false;
            foreach ($results as $row) {
                if ($row['month'] === $monthStr) {
                    $incomeData[] = floatval($row['income']);
                    $expenseData[] = floatval($row['expense']);
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $incomeData[] = 0;
                $expenseData[] = 0;
            }
        }
        
        $data = [
            'labels' => $labels,
            'income' => $incomeData,
            'expense' => $expenseData
        ];
    }
    
    echo json_encode([
        'success' => true,
        'period' => $period,
        'data' => $data
    ]);
    
} catch (Exception $e) {
    // Return error with details for debugging
    echo json_encode([
        'success' => false,
        'message' => 'Database error',
        'error' => $e->getMessage(),
        'period' => $period ?? '7days'
    ]);
    exit;
}
