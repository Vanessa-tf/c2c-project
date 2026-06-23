<?php
// Safe session start - only start if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/db.php';

/**
 * Redirects non-admin users to the Access Denied page.
 */
function checkAdmin() {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        header('Location: /c2c-ecommerce/admin/access_denied.php');
        exit;
    }
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user']);
}

/**
 * Get current user data
 */
function getCurrentUser() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

/**
 * Destroy session and logout user.
 */
function logoutUser() {
    session_unset();
    session_destroy();
    header('Location: /c2c-ecommerce/login.php');
    exit;
}

/**
 * Fetches a user record by ID.
 */
function getUserById($id) {
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch(Exception $e) {
        error_log("Error fetching user: " . $e->getMessage());
        return false;
    }
}

/**
 * Validate user login
 */
function validateLogin($email, $password) {
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Don't store password in session
            unset($user['password']);
            $_SESSION['user'] = $user;
            return true;
        }
        return false;
    } catch(Exception $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

/**
 * Register new user
 */
function registerUser($name, $email, $password, $role = 'user') {
    try {
        $pdo = getPDO();
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return false; // Email already exists
        }
        
        // Insert new user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$name, $email, $hashedPassword, $role]);
    } catch(Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        return false;
    }
}

/**
 * Redirects unauthenticated users to the login page.
 */
function checkAuth() {
    if (!isset($_SESSION['user'])) {
        header('Location: /c2c-ecommerce/login.php');
        exit;
    }
}
?>
