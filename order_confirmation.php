<?php
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
checkAuth();

$order_id = $_GET['id'];
$user_id = $_SESSION['user']['id'];

// Get order details
$order = $pdo->prepare("SELECT o.*, 
                       COUNT(oi.id) AS items,
                       SUM(oi.price * oi.quantity) AS total
                       FROM orders o
                       JOIN order_items oi ON o.id = oi.order_id
                       WHERE o.id = ? AND o.user_id = ?
                       GROUP BY o.id");
$order->execute([$order_id, $user_id]);
$order = $order->fetch();

if(!$order) die("Order not found");
?>
<!DOCTYPE html>
<html>
<body>
    <?php include 'header.php'; ?>
    
    <div class="confirmation">
        <h2>Order #<?= $order_id ?> Confirmed! 🎉</h2>
        <p>Thank you for your purchase!</p>
        
        <div class="order-details">
            <p><strong>Total Items:</strong> <?= $order['items'] ?></p>
            <p><strong>Total Paid:</strong> R <?= number_format($order['total'], 2) ?></p>
            <p><strong>Order Date:</strong> <?= date('d M Y H:i', strtotime($order['created_at'])) ?></p>
        </div>
        
        <a href="profile.php" class="btn">View Order History</a>
    </div>
</body>
</html>