<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/models.php';

/**
 * Authentication Class
 */
class Auth {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Register a new user
     */
    public function register(array $userData) {
        $errors = [];
        
        // Validate required fields
        $requiredFields = ['username', 'email', 'password', 'first_name', 'last_name', 'role'];
        foreach ($requiredFields as $field) {
            if (empty($userData[$field])) {
                $errors[] = ucfirst($field) . " is required";
            }
        }
        
        // Validate email
        if (!empty($userData['email']) && !validate_email($userData['email'])) {
            $errors[] = "Invalid email format";
        }
        
        // Check if email already exists
        if (!empty($userData['email']) && $this->userModel->findByEmail($userData['email'])) {
            $errors[] = "Email already exists";
        }
        
        // Check if username already exists
        if (!empty($userData['username']) && $this->userModel->findByUsername($userData['username'])) {
            $errors[] = "Username already exists";
        }
        
        // Validate password
        if (!empty($userData['password'])) {
            $passwordErrors = validate_password($userData['password']);
            $errors = array_merge($errors, $passwordErrors);
        }
        
        // Validate role
        if (!empty($userData['role']) && !in_array($userData['role'], ['customer', 'vendor', 'admin'])) {
            $errors[] = "Invalid role";
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $userId = $this->userModel->createUser($userData);
            
            // If vendor registration, create vendor record
            if ($userData['role'] === 'vendor' && !empty($userData['vendor_data'])) {
                $vendorModel = new Vendor();
                $userData['vendor_data']['user_id'] = $userId;
                $userData['vendor_data']['status'] = 'pending';
                $vendorModel->create($userData['vendor_data']);
            }
            
            return ['success' => true, 'user_id' => $userId];
        } catch (Exception $e) {
            return ['success' => false, 'errors' => ['Registration failed: ' . $e->getMessage()]];
        }
    }
    
    /**
     * Login user
     */
    public function login(string $email, string $password, bool $remember = false) {
        $user = $this->userModel->verifyLogin($email, $password);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => 'Account is not active'];
        }
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        
        // Set vendor ID if user is a vendor
        if ($user['role'] === 'vendor') {
            $vendorModel = new Vendor();
            $vendor = $vendorModel->findByUserId($user['id']);
            if ($vendor) {
                $_SESSION['vendor_id'] = $vendor['id'];
                $_SESSION['vendor_status'] = $vendor['status'];
            }
        }
        
        // Set admin flag
        if ($user['role'] === 'admin') {
            $_SESSION['is_admin'] = true;
        }
        
        // Update last login
        $this->userModel->updateLastLogin($user['id']);
        
        // Set remember me cookie if requested
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/'); // 30 days
            
            // Store token in database (you might want to create a remember_tokens table)
            // For now, we'll store it in session
            $_SESSION['remember_token'] = $token;
        }
        
        return ['success' => true, 'user' => $user];
    }
    
    /**
     * Logout user
     */
    public function logout() {
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        // Clear session
        session_destroy();
        
        return ['success' => true];
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return !empty($_SESSION['user_id']);
    }
    
    /**
     * Get current user data
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return $this->userModel->find($_SESSION['user_id']);
    }
    
    /**
     * Check if user has specific role
     */
    public function hasRole(string $role) {
        return $_SESSION['user_role'] === $role;
    }
    
    /**
     * Require specific role
     */
    public function requireRole(string $role) {
        if (!$this->isLoggedIn() || !$this->hasRole($role)) {
            $redirectUrl = match($role) {
                'admin' => build_path('/admin/login.php'),
                'vendor' => build_path('/vendor/login.php'),
                default => build_path('/customer/login.php')
            };
            
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
    
    /**
     * Change password
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword) {
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }
        
        if (!verify_password($currentPassword, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }
        
        $passwordErrors = validate_password($newPassword);
        if (!empty($passwordErrors)) {
            return ['success' => false, 'errors' => $passwordErrors];
        }
        
        $newPasswordHash = hash_password($newPassword);
        $success = $this->userModel->update($userId, ['password_hash' => $newPasswordHash]);
        
        if ($success) {
            return ['success' => true, 'message' => 'Password changed successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to change password'];
        }
    }
    
    /**
     * Reset password request
     */
    public function requestPasswordReset(string $email) {
        $user = $this->userModel->findByEmail($email);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Email not found'];
        }
        
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour
        
        // Store token in session (in production, store in database)
        $_SESSION['reset_token'] = $token;
        $_SESSION['reset_user_id'] = $user['id'];
        $_SESSION['reset_expires'] = $expiresAt;
        
        // In production, send email with reset link
        // For now, we'll just return the token
        return [
            'success' => true, 
            'message' => 'Password reset link sent to your email',
            'token' => $token // Remove this in production
        ];
    }
    
    /**
     * Reset password with token
     */
    public function resetPassword(string $token, string $newPassword) {
        if (!isset($_SESSION['reset_token']) || 
            !isset($_SESSION['reset_user_id']) || 
            !isset($_SESSION['reset_expires'])) {
            return ['success' => false, 'message' => 'Invalid or expired reset token'];
        }
        
        if ($_SESSION['reset_token'] !== $token) {
            return ['success' => false, 'message' => 'Invalid reset token'];
        }
        
        if (strtotime($_SESSION['reset_expires']) < time()) {
            return ['success' => false, 'message' => 'Reset token has expired'];
        }
        
        $passwordErrors = validate_password($newPassword);
        if (!empty($passwordErrors)) {
            return ['success' => false, 'errors' => $passwordErrors];
        }
        
        $newPasswordHash = hash_password($newPassword);
        $success = $this->userModel->update($_SESSION['reset_user_id'], ['password_hash' => $newPasswordHash]);
        
        if ($success) {
            // Clear reset session data
            unset($_SESSION['reset_token']);
            unset($_SESSION['reset_user_id']);
            unset($_SESSION['reset_expires']);
            
            return ['success' => true, 'message' => 'Password reset successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to reset password'];
        }
    }
    
    /**
     * Update user profile
     */
    public function updateProfile(int $userId, array $profileData) {
        $allowedFields = ['first_name', 'last_name', 'phone'];
        $updateData = [];
        
        foreach ($allowedFields as $field) {
            if (isset($profileData[$field])) {
                $updateData[$field] = sanitize_input($profileData[$field]);
            }
        }
        
        if (empty($updateData)) {
            return ['success' => false, 'message' => 'No valid fields to update'];
        }
        
        $success = $this->userModel->update($userId, $updateData);
        
        if ($success) {
            // Update session data
            foreach ($updateData as $field => $value) {
                $_SESSION[$field] = $value;
            }
            
            return ['success' => true, 'message' => 'Profile updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update profile'];
        }
    }
}

/**
 * Session Management
 */
class SessionManager {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function regenerate() {
        session_regenerate_id(true);
    }
    
    public static function destroy() {
        session_destroy();
    }
    
    public static function set(string $key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public static function get(string $key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    public static function has(string $key) {
        return isset($_SESSION[$key]);
    }
    
    public static function remove(string $key) {
        unset($_SESSION[$key]);
    }
    
    public static function flash(string $type, string $message) {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        if (!isset($_SESSION['flash'][$type])) {
            $_SESSION['flash'][$type] = [];
        }
        $_SESSION['flash'][$type][] = $message;
    }
    
    public static function getFlash(string $type = null) {
        if ($type) {
            $messages = $_SESSION['flash'][$type] ?? [];
            unset($_SESSION['flash'][$type]);
            return $messages;
        }
        
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $messages;
    }
}

?>
