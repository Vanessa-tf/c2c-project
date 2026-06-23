<?php
include 'includes/db.php';

$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Password strength validation
    if (strlen($password) < 8) {
        header("Location: register.php?error=" . urlencode("Password must be at least 8 characters long"));
        exit();
    }
    if (!preg_match('/[A-Z]/', $password)) {
        header("Location: register.php?error=" . urlencode("Password must include at least one uppercase letter"));
        exit();
    }
    if (!preg_match('/[a-z]/', $password)) {
        header("Location: register.php?error=" . urlencode("Password must include at least one lowercase letter"));
        exit();
    }
    if (!preg_match('/\d/', $password)) {
        header("Location: register.php?error=" . urlencode("Password must include at least one number"));
        exit();
    }
    if (!preg_match('/[\W]/', $password)) {
        header("Location: register.php?error=" . urlencode("Password must include at least one special character"));
        exit();
    }

    try {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            header("Location: register.php?error=" . urlencode("Email already exists"));
            exit();
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $hashed_password]);

        header("Location: login.php?success=" . urlencode("Registration successful!"));
        exit();

    } catch (PDOException $e) {
        header("Location: register.php?error=" . urlencode("Database error"));
        exit();
    }
}
?>
