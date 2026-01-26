<?php
/**
 * Authentication Class
 */

defined('APP_ACCESS') or define('APP_ACCESS', true);
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
        }
    }
    
    public function login($username, $password) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1 LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $passwordMatch = false;
            if ($user) {
                if (password_verify($password, $user['password'])) {
                    $passwordMatch = true;
                } else if ($user['password'] === md5($password)) {
                    $passwordMatch = true;
                }
            }
            
            if ($user && $passwordMatch) {
                $this->startSession();
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['business_access'] = $user['business_access'] ?? 'all';
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                
                try {
                    $stmt = $pdo->prepare("SELECT theme, language FROM user_preferences WHERE user_id = ?");
                    $stmt->execute([$user['id']]);
                    $preferences = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($preferences) {
                        $_SESSION['user_theme'] = $preferences['theme'];
                        $_SESSION['user_language'] = $preferences['language'];
                    } else {
                        $_SESSION['user_theme'] = 'dark';
                        $_SESSION['user_language'] = 'id';
                    }
                } catch (PDOException $e) {
                    $_SESSION['user_theme'] = 'dark';
                    $_SESSION['user_language'] = 'id';
                }
                
                $stmt = $pdo->prepare("UPDATE users SET updated_at = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Auth login error: " . $e->getMessage());
            return false;
        }
    }
    
    public function logout() {
        $this->startSession();
        session_unset();
        session_destroy();
        return true;
    }
    
    public function isLoggedIn() {
        $this->startSession();
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public function getCurrentUser() {
        $this->startSession();
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'full_name' => $_SESSION['full_name'],
                'role' => $_SESSION['role']
            ];
        }
        return null;
    }
    
    public function hasRole($role) {
        $this->startSession();
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }
        
        if (!isset($_SESSION['user_theme']) || !isset($_SESSION['user_language'])) {
            try {
                $preferences = $this->db->fetchOne(
                    "SELECT theme, language FROM user_preferences WHERE user_id = ?",
                    [$_SESSION['user_id']]
                );
                
                if ($preferences) {
                    $_SESSION['user_theme'] = $preferences['theme'];
                    $_SESSION['user_language'] = $preferences['language'];
                } else {
                    $_SESSION['user_theme'] = 'dark';
                    $_SESSION['user_language'] = 'id';
                }
            } catch (Exception $e) {
                $_SESSION['user_theme'] = 'dark';
                $_SESSION['user_language'] = 'id';
            }
        }
    }
    
    public function requireRole($role) {
        $this->requireLogin();
        if (!$this->hasRole($role)) {
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }
    }
    
    public function hasPermission($module) {
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        // Get user ID from session
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) {
            return false;
        }
        
        try {
            // Use existing database connection from $this->db
            $conn = $this->db->getConnection();
            
            // Query user_permissions table
            $query = "SELECT COUNT(*) as count FROM user_permissions WHERE user_id = ? AND permission = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$user_id, $module]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // If found in database, return true
            if ($result && intval($result['count']) > 0) {
                return true;
            }
        } catch (Exception $e) {
            // Log error for debugging
            error_log("Permission check error: " . $e->getMessage());
        }
        
        // Fallback: role-based permissions (for backward compatibility)
        $rolePermissions = [
            'admin' => ['dashboard', 'cashbook', 'divisions', 'frontdesk', 'sales_invoice', 'procurement', 'users', 'reports', 'settings', 'investor', 'project'],
            'manager' => ['dashboard', 'cashbook', 'divisions', 'frontdesk', 'sales_invoice', 'procurement', 'users', 'reports', 'settings', 'investor', 'project'],
            'accountant' => ['dashboard', 'cashbook', 'reports', 'procurement', 'investor', 'project'],
            'staff' => ['dashboard', 'cashbook', 'investor', 'project']
        ];
        
        $userRole = $_SESSION['role'] ?? 'staff';
        $permissions = $rolePermissions[$userRole] ?? ['dashboard'];
        
        return in_array($module, $permissions);
    }
}

?>
