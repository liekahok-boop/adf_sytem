<?php
define('APP_ACCESS', true);
$base_path = dirname(__FILE__);
require_once $base_path . '/config/config.php';
require_once $base_path . '/config/database.php';
require_once $base_path . '/includes/ProjectManager.php';

$db = Database::getInstance()->getConnection();
$project = new ProjectManager($db);

echo "<h2>Debug: Projects and Expenses</h2>";

// Get all projects
$projects = $project->getAllProjects();

echo "<h3>Total Projects: " . count($projects) . "</h3>";
echo "<pre>";
print_r($projects);
echo "</pre>";

// Check if any project has expenses
$has_expenses = false;
$total_expenses = 0;
foreach ($projects as $proj) {
    if ($proj['total_expenses_idr'] > 0) {
        $has_expenses = true;
        $total_expenses += $proj['total_expenses_idr'];
    }
}

echo "<h3>Has Expenses: " . ($has_expenses ? 'YES' : 'NO') . "</h3>";
echo "<h3>Total All Expenses: Rp " . number_format($total_expenses, 0, ',', '.') . "</h3>";

// Get recent expenses
try {
    $stmt = $db->prepare("
        SELECT pe.*, p.name as project_name 
        FROM project_expenses pe
        JOIN projects p ON pe.project_id = p.id
        ORDER BY pe.expense_date DESC, pe.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recent_expenses = $stmt->fetchAll();
    
    echo "<h3>Recent Expenses: " . count($recent_expenses) . "</h3>";
    echo "<pre>";
    print_r($recent_expenses);
    echo "</pre>";
} catch (Exception $e) {
    echo "<h3>Error getting expenses: " . $e->getMessage() . "</h3>";
}

// Check project data for pie chart
echo "<h3>Project Data for Pie Chart:</h3>";
$project_labels = [];
$project_expenses = [];

foreach ($projects as $proj) {
    $project_labels[] = $proj['name'];
    $project_expenses[] = $proj['total_expenses_idr'] ?? 0;
    echo "Project: {$proj['name']} = Rp " . number_format($proj['total_expenses_idr'] ?? 0, 0, ',', '.') . "<br>";
}

echo "<h3>Sum Check: " . array_sum($project_expenses) . "</h3>";
echo "<h3>Empty Check: " . (empty($projects) ? 'EMPTY' : 'NOT EMPTY') . "</h3>";
echo "<h3>Sum > 0: " . (array_sum(array_column($projects, 'total_expenses_idr')) > 0 ? 'YES' : 'NO') . "</h3>";
?>
