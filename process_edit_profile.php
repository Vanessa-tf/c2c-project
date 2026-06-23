<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user_id = $_SESSION['user']['id'];
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Get current user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            throw new Exception("Current password is incorrect");
        }

        // Prepare updates
        $updates = [];
        $params = [];

        // Update name
        if (!empty($_POST['name'])) {
            $updates[] = "name = ?";
            $params[] = $_POST['name'];
        }

        // Update email
        if (!empty($_POST['email'])) {
            // Check email availability
            $email_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $email_stmt->execute([$_POST['email'], $user_id]);
            if ($email_stmt->rowCount() > 0) {
                throw new Exception("Email already taken");
            }
            
            $updates[] = "email = ?";
            $params[] = $_POST['email'];
        }

        // Update password
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                throw new Exception("New passwords don't match");
            }
            if (strlen($new_password) < 8) {
                throw new Exception("Password must be at least 8 characters");
            }
            
            $updates[] = "password = ?";
            $params[] = password_hash($new_password, PASSWORD_DEFAULT);
        }

        // No updates
        if (empty($updates)) {
            throw new Exception("No changes made");
        }

        // Build SQL query
        $params[] = $user_id;
        $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
        
        $update_stmt = $pdo->prepare($sql);
        $update_stmt->execute($params);

        // Update session data
        if (!empty($_POST['name'])) $_SESSION['user']['name'] = $_POST['name'];
        if (!empty($_POST['email'])) $_SESSION['user']['email'] = $_POST['email'];

        header("Location: edit_profile.php?success=Profile updated");
        exit();

    } catch (Exception $e) {
        header("Location: edit_profile.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}