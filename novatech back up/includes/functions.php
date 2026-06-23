<?php
// includes/functions.php

// Function to check if user is logged in
function check_session() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

// Function to safely escape output to prevent XSS
function safe_output($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Function to check if user has an active subscription (placeholder)
function check_subscription($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND status = 'active' AND expiry_date > NOW()");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}
?>