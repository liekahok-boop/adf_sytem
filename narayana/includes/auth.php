<?php
/**
 * NARAYANA HOTEL MANAGEMENT SYSTEM
 * Authentication Functions
 */

defined('APP_ACCESS') or define('APP_ACCESS', true);
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Start Session
     */
    public function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
        }
    }
    
    /**
     * Login User
     */
    public function login($username, $password) {
        $sql = "SELECT * FROM users WHERE username = :username AND is_active = 1 LIMIT 1";
        $user = $this->db->fetchOne($sql, ['username' => $username]);
        
        if ($user && password_verify($password, $user['password'])) {
            $this->startSession();
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            
            // Load user preferences (theme & language)
            $preferences = $this->db->fetchOne(
                "SELECT theme, language FROM user_preferences WHERE user_id = ?",
                [$user['id']]
            );
            
            if ($preferences) {
                $_SESSION['user_theme'] = $preferences['theme'];
                $_SESSION['user_language'] = $preferences['language'];
            } else {
                // Default preferences
                $_SESSION['user_theme'] = 'dark';
                $_SESSION['user_language'] = 'id';
            }
            
            // Update last login
            $updateSql = "UPDATE users SET updated_at = NOW() WHERE id = :id";
            $this->db->query($updateSql, ['id' => $user['id']]);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Logout User
     */
    public function logout() {
        $this->startSession();
        session_unset();
        session_destroy();
        return true;
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        $this->startSession();
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Get Current User
     */
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
    
    /**
     * Check User Role
     */
    public function hasRole($role) {
        $this->startSession();
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }
    
    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole($roles) {
        $this->startSession();
        if (!isset($_SESSION['role'])) {
            return false;
        }
        return in_array($_SESSION['role'], $roles);
    }
    
    /**
     * Check if user has permission to access a menu
     * Admin always has full access
     */
    public function hasPermission($permissionKey) {
        $this->startSession();
        
        // Admin has all permissions
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            return true;
        }
        
        // Check if user has specific permission
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        $db = Database::getInstance();
        $result = $db->fetchOne(
            "SELECT id FROM user_permissions WHERE user_id = ? AND permission_key = ?",
            [$_SESSION['user_id'], $permissionKey]
        );
        
        return $result !== false;
    }
    
    /**
     * Get all permissions for current user
     */
    public function getUserPermissions() {
        $this->startSession();
        
        // Admin has all permissions
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            return ['dashboard', 'cashbook', 'divisions', 'frontdesk', 'sales_invoice', 'procurement', 'reports', 'users', 'settings'];
        }
        
        // Owner has read-only permissions to dashboard and reports
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'owner') {
            return ['dashboard', 'reports', 'owner_dashboard'];
        }
        
        if (!isset($_SESSION['user_id'])) {
            return [];
        }
        
        $db = Database::getInstance();
        $permissions = $db->fetchAll(
            "SELECT permission_key FROM user_permissions WHERE user_id = ?",
            [$_SESSION['user_id']]
        );
        
        return array_column($permissions, 'permission_key');
    }
    
    /**
     * Check if user is owner
     */
    public function isOwner() {
        return $this->hasRole('owner');
    }
    
    /**
     * Get branches accessible by owner
     */
    public function getOwnerBranches() {
        $this->startSession();
        
        if (!isset($_SESSION['user_id'])) {
            return [];
        }
        
        // Admin can access all branches
        if ($this->hasRole('admin')) {
            $db = Database::getInstance();
            return $db->fetchAll("SELECT * FROM branches WHERE is_active = 1 ORDER BY branch_name");
        }
        
        // Owner can only access assigned branches
        if ($this->hasRole('owner')) {
            $db = Database::getInstance();
            return $db->fetchAll(
                "SELECT b.* FROM branches b
                 INNER JOIN owner_branch_access oba ON b.id = oba.branch_id
                 WHERE oba.user_id = ? AND b.is_active = 1
                 ORDER BY b.branch_name",
                [$_SESSION['user_id']]
            );
        }
        
        return [];
    }
    
    /**
     * Require Login (redirect if not logged in)
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }
        
        // Load user preferences if not already loaded in session
        if (!isset($_SESSION['user_theme']) || !isset($_SESSION['user_language'])) {
            $preferences = $this->db->fetchOne(
                "SELECT theme, language FROM user_preferences WHERE user_id = ?",
                [$_SESSION['user_id']]
            );
            
            if ($preferences) {
                $_SESSION['user_theme'] = $preferences['theme'];
                $_SESSION['user_language'] = $preferences['language'];
            } else {
                // Default preferences
                $_SESSION['user_theme'] = 'dark';
                $_SESSION['user_language'] = 'id';
            }
        }
    }
    
    /**
     * Require Role (redirect if user doesn't have required role)
     */
    public function requireRole($role) {
        $this->requireLogin();
        if (!$this->hasRole($role)) {
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }
    }
    
    /**
     * Register New User (Admin only)
     */
    public function register($data) {
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $userData = [
            'username' => $data['username'],
            'password' => $hashedPassword,
            'full_name' => $data['full_name'],
            'email' => $data['email'] ?? null,
            'role' => $data['role'] ?? 'staff',
            'phone' => $data['phone'] ?? null
        ];
        
        return $this->db->insert('users', $userData);
    }
    
    /**
     * Change Password
     */
    public function changePassword($userId, $oldPassword, $newPassword) {
        $sql = "SELECT password FROM users WHERE id = :id LIMIT 1";
        $user = $this->db->fetchOne($sql, ['id' => $userId]);
        
        if ($user && password_verify($oldPassword, $user['password'])) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            return $this->db->update('users', 
                ['password' => $hashedPassword], 
                'id = :id', 
                ['id' => $userId]
            );
        }
        
        return false;
    }
}

?>
