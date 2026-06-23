<?php
require_once __DIR__ . '/../includes/auth.php';  // Fixed: __DIR__ instead of DIR
require_once __DIR__ . '/../includes/db.php';    // Fixed: __DIR__ instead of DIR

// Redirect logged-in admins
if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {  // Fixed: use 'role' not 'is_admin'
    header("Location: dashboard.php");
    exit();
}

$error = '';
$email = '';

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid form submission');
        }
        
        // Sanitize and validate input
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        
        // Attempt login - Fixed: use validateLogin() instead of loginUser()
        if (validateLogin($email, $password)) {
            // Check if user is admin - Fixed: use 'role' not 'is_admin'
            if ($_SESSION['user']['role'] === 'admin') {
                header("Location: dashboard.php");
                exit();
            }
            
            // Log out non-admin users
            logoutUser();
            throw new Exception('This account does not have admin privileges');
        }
        
        throw new Exception('Invalid email or password');
    }
} catch (PDOException $e) {
    error_log('Database error during login: ' . $e->getMessage());
    $error = 'System error occurred';
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - SmallStreet</title>
    <link rel="stylesheet" href="../css/style.css">  <!-- Fixed: use your unified CSS -->
</head>
<body>
    <div class="admin-login-container">
        <h1>
            <span class="logo-s">S</span>mall<span class="logo-s">S</span>treet Admin Portal 🇿🇦
        </h1>
        
        <?php if($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            
            <div class="form-group">
                <label for="email">Admin Email:</label>
                <input type="email" id="email" name="email" 
                       value="<?= htmlspecialchars($email) ?>" 
                       required autocomplete="email">
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" 
                       required autocomplete="current-password">
            </div>
            
            <button type="submit" class="login-button">Login as Admin</button>
        </form>
        
        <div class="login-footer">
            <a href="../index.php">← Back to Main Site</a>
        </div>
    </div>
</body>
</html>