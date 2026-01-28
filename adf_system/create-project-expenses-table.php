<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$businesses = [
    'adf_narayana_hotel',
    'adf_benscafe',
    'adf_eat_meet',
    'adf_furniture',
    'adf_karimunjawa',
    'adf_pabrik_kapal'
];

$create_projects_table = "
CREATE TABLE IF NOT EXISTS `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','completed','on_hold') DEFAULT 'active',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

$create_project_expenses_table = "
CREATE TABLE IF NOT EXISTS `project_expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL COMMENT 'Kategori pengeluaran: Semen, Cat, Besi, dll',
  `description` text DEFAULT NULL,
  `amount_usd` decimal(15,2) NOT NULL DEFAULT 0.00,
  `amount_idr` decimal(15,2) NOT NULL DEFAULT 0.00,
  `exchange_rate` decimal(10,2) DEFAULT 15500.00,
  `expense_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `category` (`category`),
  KEY `expense_date` (`expense_date`),
  CONSTRAINT `project_expenses_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

$create_project_balances_table = "
CREATE TABLE IF NOT EXISTS `project_balances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `total_expenses_usd` decimal(15,2) DEFAULT 0.00,
  `total_expenses_idr` decimal(15,2) DEFAULT 0.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_id` (`project_id`),
  CONSTRAINT `project_balances_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

echo "<h2>Creating Project Expense Tables</h2>";

foreach ($businesses as $db_name) {
    echo "<h3>Processing: $db_name</h3>";
    
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=$db_name;charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Create projects table
        echo "Creating projects table... ";
        $pdo->exec($create_projects_table);
        echo "✓ Done<br>";
        
        // Create project_expenses table
        echo "Creating project_expenses table... ";
        $pdo->exec($create_project_expenses_table);
        echo "✓ Done<br>";
        
        // Create project_balances table
        echo "Creating project_balances table... ";
        $pdo->exec($create_project_balances_table);
        echo "✓ Done<br>";
        
        echo "<span style='color: green;'>✓ Success for $db_name</span><br><br>";
        
    } catch (Exception $e) {
        echo "<span style='color: red;'>✗ Error for $db_name: " . $e->getMessage() . "</span><br><br>";
    }
}

echo "<h2>Done! All tables created.</h2>";
echo "<p><a href='modules/investor/'>Go to Investor Module</a></p>";
?>
