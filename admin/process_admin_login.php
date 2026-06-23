<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin'] = $admin;
            header("Location: dashboard.php");
            exit();
        } else {
            header("Location: login.php?error=Invalid credentials");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: login.php?error=Database error");
        exit();
    }
}
?>