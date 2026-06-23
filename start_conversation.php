<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

checkAuth();

if (empty($_POST['product_id']) || empty($_POST['seller_id']) || empty($_POST['message'])) {
    $_SESSION['error'] = "Please fill all fields";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

$pdo = getPDO();
$buyer_id = $_SESSION['user']['id'];
$product_id = (int)$_POST['product_id'];
$seller_id = (int)$_POST['seller_id'];

// Verify product belongs to seller
$stmt = $pdo->prepare("SELECT id FROM products WHERE id = ? AND user_id = ?");
$stmt->execute([$product_id, $seller_id]);
if (!$stmt->fetch()) {
    $_SESSION['error'] = "Invalid product or seller";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

// Insert new message directly into messages table
$stmt = $pdo->prepare("INSERT INTO messages (product_id, buyer_id, seller_id, message, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->execute([$product_id, $buyer_id, $seller_id, trim($_POST['message'])]);

// Redirect to conversation page passing identifying info
header("Location: conversation.php?product_id=$product_id&buyer_id=$buyer_id&seller_id=$seller_id");
exit();
