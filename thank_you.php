<?php
session_start();
require_once 'includes/db.php';

$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    header('Location: index.php');
    exit;
}

$pdo = getPDO();

// Fetch order details including payment and shipping method
$stmt = $pdo->prepare("
    SELECT o.*, 
           s.name AS shipping_name, 
           s.cost AS shipping_cost
    FROM orders o
    JOIN shipping_methods s ON o.shipping_method_id = s.id
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    die('Order not found.');
}

// Format payment method
$payment_method_display = match ($order['payment_method']) {
    'cash' => 'Cash on Delivery',
    'card' => 'Card Payment',
    default => 'Unknown',
};

// Dynamic payment message
$payment_message = '';
if ($order['payment_method'] === 'cash') {
    $payment_message = "Please have R" . number_format($order['total'], 2) . " ready in cash for the courier.";
} elseif ($order['payment_method'] === 'card') {
    $payment_message = "Your card payment has been received successfully.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Thank You - SmallStreet</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="thank-you">
    <h2>🎉 Thank You for Your Order!</h2>
    <p>Your order #<?= htmlspecialchars($order['id']) ?> is being processed.</p>
    <p>Shipping Method: <?= htmlspecialchars($order['shipping_name']) ?> (R<?= number_format($order['shipping_cost'], 2) ?>)</p>
    <p>Shipping Address: <?= htmlspecialchars($order['address']) ?>, <?= htmlspecialchars($order['city']) ?>, <?= htmlspecialchars($order['postal_code']) ?>, <?= htmlspecialchars($order['country']) ?></p>
    <p>Payment Method: <strong><?= htmlspecialchars($payment_method_display) ?></strong></p>
    <p><?= htmlspecialchars($payment_message) ?></p>
    <p>Total Paid (including shipping): R<?= number_format($order['total'], 2) ?></p>

    <?php if (!empty($_SESSION['user']['email'])): ?>
        <p>We'll contact you at <?= htmlspecialchars($_SESSION['user']['email']) ?> with updates.</p>
    <?php endif; ?>

    <a href="index.php" class="btn-primary">Continue Shopping</a>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
