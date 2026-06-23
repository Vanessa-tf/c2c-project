<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

checkAuth();

$pdo = getPDO();

$product_id = $_POST['product_id'] ?? 0;
$buyer_id = $_POST['buyer_id'] ?? 0;
$seller_id = $_POST['seller_id'] ?? 0;
$message = trim($_POST['message'] ?? '');
$user_id = $_SESSION['user']['id'];

if (empty($message)) {
    $_SESSION['error'] = "Message cannot be empty";
    header("Location: conversation.php?product_id=$product_id&buyer_id=$buyer_id&seller_id=$seller_id");
    exit();
}

// Only buyer or seller can send message
if ($user_id != $buyer_id && $user_id != $seller_id) {
    die("Access denied.");
}

// Verify product exists
$stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
$stmt->execute([$product_id]);
if (!$stmt->fetch()) {
    die("Invalid product.");
}

// Insert message
$stmt = $pdo->prepare("INSERT INTO messages (product_id, buyer_id, seller_id, message, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->execute([$product_id, $buyer_id, $seller_id, $message]);

header("Location: conversation.php?product_id=$product_id&buyer_id=$buyer_id&seller_id=$seller_id");
exit();
