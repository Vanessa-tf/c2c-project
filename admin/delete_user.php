<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user_id = $_POST['user_id'];
        
        // Prevent admin from deleting themselves
        if ($_SESSION['admin']['id'] == $user_id) {
            header("Location: users.php?error=Cannot delete current admin");
            exit();
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        
        header("Location: users.php?success=User deleted");
    } catch (PDOException $e) {
        header("Location: users.php?error=" . urlencode($e->getMessage()));
    }
}
?>