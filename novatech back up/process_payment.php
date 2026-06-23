<?php
require_once 'includes/auth_check.php';
require_once 'includes/config.php';

if (!isset($_GET['package_id']) || !is_numeric($_GET['package_id'])) {
    die("Invalid package selected.");
}

$package_id = $_GET['package_id'];
$user_id = $_SESSION['user_id'];

// Fetch package details
$stmt = $pdo->prepare("SELECT package_name, price, duration_days FROM subscriptions WHERE package_id = ?");
$stmt->execute([$package_id]);
$package = $stmt->fetch();

if (!$package) {
    die("Package not found.");
}

// For now, simulate payment (in real life, redirect to PayFast)
// Insert into user_subscriptions with status 'pending'
$start_date = date('Y-m-d');
$end_date = date('Y-m-d', strtotime("+$package[duration_days] days"));

$stmt = $pdo->prepare("INSERT INTO user_subscriptions (user_id, package_id, start_date, end_date, payment_status) VALUES (?, ?, ?, ?, 'pending')");
if ($stmt->execute([$user_id, $package_id, $start_date, $end_date])) {
    $subscription_id = $pdo->lastInsertId();
    
    // TODO: Redirect to PayFast with item details
    // For demo, we'll auto-complete payment
    $stmt = $pdo->prepare("UPDATE user_subscriptions SET payment_status = 'completed' WHERE id = ?");
    $stmt->execute([$subscription_id]);
    
    $_SESSION['message'] = "Payment successful! Your subscription is now active.";
    header("Location: student_dashboard.php");
    exit();
} else {
    die("Failed to process subscription.");
}
?>