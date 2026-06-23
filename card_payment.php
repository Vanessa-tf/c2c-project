<?php
session_start();
require_once 'includes/db.php';

$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    header('Location: index.php');
    exit;
}

$pdo = getPDO();

// Fetch order to verify it exists
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    die('Order not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Here you would integrate real payment logic.

    // For demo: mark order as paid (add a 'paid' column or status)
    $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ?");
    $stmt->execute([$order_id]);

    // Redirect to thank you page
    header("Location: thank_you.php?order_id=$order_id");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Card Payment - SmallStreet</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="card-payment">
        <h2>Card Payment for Order #<?= htmlspecialchars($order_id) ?></h2>
        <p>This is a demo page. Implement your payment gateway here.</p>

        <form method="POST" action="">
            <label>Card Number:</label><br>
            <input type="text" name="card_number" required><br>

            <label>Expiry Date:</label><br>
            <input type="text" name="expiry_date" required><br>

            <label>CVV:</label><br>
            <input type="text" name="cvv" required><br><br>

            <button type="submit">Pay Now</button>
        </form>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
