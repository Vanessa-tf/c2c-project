<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $buyer_id = $_SESSION['user']['id'];
        $product_id = $_POST['product_id'];
        $seller_id = $_POST['seller_id'];
        $message = $_POST['message'];

        if (empty($message)) {
            throw new Exception("Message cannot be empty");
        }

        $stmt = $pdo->prepare("INSERT INTO messages 
            (product_id, buyer_id, seller_id, message)
            VALUES (?, ?, ?, ?)");
        $stmt->execute([$product_id, $buyer_id, $seller_id, $message]);

        header("Location: index.php?message_sent=1");
    } catch (Exception $e) {
        header("Location: index.php?error=" . urlencode($e->getMessage()));
    }
}
?>