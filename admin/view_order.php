<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
checkAuth();

$pdo = getPDO();

if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = $_GET['id'];

// Get order with user info and shipping_method_id
$stmt = $pdo->prepare("SELECT o.id, o.created_at, o.shipping_method_id, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: orders.php");
    exit();
}

// Get order items
$stmt = $pdo->prepare("SELECT p.title, p.price, oi.quantity FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

// Fetch shipping method details
$stmt = $pdo->prepare("SELECT name, cost FROM shipping_methods WHERE id = ?");
$stmt->execute([$order['shipping_method_id']]);
$shipping = $stmt->fetch();

// Define expected shipping days per method
$shipping_days = [
    'Standard Shipping' => 3,
    'Express Shipping' => 1,
    'Economy Shipping' => 7,
];

// Default days if method unknown
$days_to_ship = $shipping_days[$shipping['name']] ?? 5;

// Calculate ship-by date
$order_date = new DateTime($order['created_at']);
$shipped_date = clone $order_date;
$shipped_date->modify("+$days_to_ship days");

$shipped_status = "Will be shipped by " . $shipped_date->format('Y-m-d') . " (via " . htmlspecialchars($shipping['name']) . ")";
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Order #<?= $order['id'] ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include 'admin_header.php'; ?>
    
    <div class="admin-form">
        <h2>View Order: #<?= $order['id'] ?></h2>
        
        <div class="form-group">
            <label>User Email:</label>
            <p><?= htmlspecialchars($order['email']) ?></p>
        </div>
        
        <div class="form-group">
            <label>Order Date:</label>
            <p><?= htmlspecialchars($order['created_at']) ?></p>
        </div>

        <div class="form-group">
            <label>Shipping Method:</label>
            <p><?= htmlspecialchars($shipping['name']) ?> (R<?= number_format($shipping['cost'], 2) ?>)</p>
        </div>

        <div class="form-group">
            <label>Shipping Status:</label>
            <p><?= htmlspecialchars($shipped_status) ?></p>
        </div>

        <div class="form-group">
            <label>Order Items:</label>
            <table>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Total</th>
                </tr>
                <?php 
                $grandTotal = 0;
                foreach ($items as $item): 
                    $total = $item['price'] * $item['quantity'];
                    $grandTotal += $total;
                ?>
                <tr>
                    <td><?= htmlspecialchars($item['title']) ?></td>
                    <td>R<?= number_format($item['price'], 2) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>R<?= number_format($total, 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="grand-total">
                    <td colspan="3"><strong>Grand Total:</strong></td>
                    <td><strong>R<?= number_format($grandTotal, 2) ?></strong></td>
                </tr>
            </table>
        </div>
        
        <div class="form-actions">
             <a class="btn-primary" href="generate_invoice.php?id=<?= $order_id ?>&type=pdf">Download PDF Invoice</a>
    <a class="btn-primary" href="generate_invoice.php?id=<?= $order_id ?>&type=csv">Download CSV Invoice</a>
    <a href="orders.php" class="btn-primary">Back to Orders</a>
                
            </div>
           
        </div>
    </div>
</body>
</html>
