<?php
session_start();
require_once '../config/database.php';

class Auth {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getConnection();
    }
    
    // Login function
    public function login($email, $password) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT u.user_id, u.email, u.password, u.full_name, u.role_id, ur.role_name, u.is_active
                FROM users u 
                JOIN user_roles ur ON u.role_id = ur.role_id 
                WHERE u.email = ? AND u.is_active = 1
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['role_name'] = $user['role_name'];
                $_SESSION['logged_in'] = true;
                
                // Get additional info based on role
                if ($user['role_name'] == 'Student') {
                    $stmt = $this->pdo->prepare("SELECT student_id, college_id FROM students WHERE user_id = ?");
                    $stmt->execute([$user['user_id']]);
                    $student_info = $stmt->fetch();
                    if ($student_info) {
                        $_SESSION['student_id'] = $student_info['student_id'];
                        $_SESSION['college_id'] = $student_info['college_id'];
                    }
                }
                
                return [
                    'success' => true,
                    'message' => 'Login successful',
                    'redirect' => $this->getRedirectUrl($user['role_name'])
                ];
            } else {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
        }
    }
    
    // Get redirect URL based on role
    private function getRedirectUrl($role) {
        switch ($role) {
            case 'Developer':
                return 'developer/dashboard.php';
            case 'SuperAdmin':
                return 'superadmin/dashboard.php';
            case 'Admin':
                return 'admin/dashboard.php';
            case 'Student':
                return 'student/dashboard.php';
            case 'Company':
                return 'company/dashboard.php';
            default:
                return 'index.php';
        }
    }
    
    // Check if user is logged in
    public static function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    // Check user role
    public static function hasRole($role) {
        return isset($_SESSION['role_name']) && $_SESSION['role_name'] === $role;
    }
    
    // Get current user info
    public static function getCurrentUser() {
        if (self::isLoggedIn()) {
            return [
                'user_id' => $_SESSION['user_id'],
                'email' => $_SESSION['email'],
                'full_name' => $_SESSION['full_name'],
                'role_name' => $_SESSION['role_name'],
                'student_id' => $_SESSION['student_id'] ?? null,
                'college_id' => $_SESSION['college_id'] ?? null
            ];
        }
        return null;
    }
    
    // Logout function
    public static function logout() {
        session_destroy();
        header('Location: ../index.php');
        exit;
    }
    
    // Require login
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: ../index.php');
            exit;
        }
    }
    
    // Require specific role
    public static function requireRole($role) {
        self::requireLogin();
        if (!self::hasRole($role)) {
            header('Location: ../unauthorized.php');
            exit;
        }
    }
    
    // Generate password hash
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
?>