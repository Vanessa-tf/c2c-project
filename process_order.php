<?php
session_start();
require_once 'includes/auth.php';
checkAuth();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: checkout.php");
    exit();
}

$pdo = getPDO();

// Get and validate form input
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$postal_code = trim($_POST['postal_code'] ?? '');
$country = trim($_POST['country'] ?? '');
$shipping_method_id = filter_input(INPUT_POST, 'shipping_method_id', FILTER_VALIDATE_INT);
$payment_method = $_POST['payment_method'] ?? '';

$card_number = $_POST['card_number'] ?? '';
$expiry = $_POST['expiry'] ?? '';
$cvv = $_POST['cvv'] ?? '';

// Basic validation
if (
    !$address || 
    !$city || 
    !$postal_code || 
    !$country || 
    !$shipping_method_id || 
    !in_array($payment_method, ['cash', 'card'])
) {
    die('Please fill in all required fields and select a valid payment method.');
}

// If card payment, validate card fields
if ($payment_method === 'card') {
    if (!$card_number || !$expiry || !$cvv) {
        die('Please fill in all card details for card payment.');
    }

    // Validate format
    if (!preg_match('/^\d{16}$/', $card_number)) {
        die('Invalid card number. Must be exactly 16 digits.');
    }

    if (!preg_match('/^\d{2}\/\d{2}$/', $expiry)) {
        die('Invalid expiry date. Format must be MM/YY.');
    }

    if (!preg_match('/^\d{3,4}$/', $cvv)) {
        die('Invalid CVV. Must be 3 or 4 digits.');
    }
}

// Validate cart
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    die('Invalid cart item data.');
}

$cart = $_SESSION['cart'];
$total = 0;

$pdo->beginTransaction();

try {
    // Get shipping cost
    $stmt = $pdo->prepare("SELECT cost FROM shipping_methods WHERE id = ?");
    $stmt->execute([$shipping_method_id]);
    $shipping = $stmt->fetch();

    if (!$shipping) {
        throw new Exception("Invalid shipping method.");
    }

    $shipping_cost = $shipping['cost'];

    // Calculate total and check stock
    foreach ($cart as $product_id => $item) {
        $quantity = $item['quantity'];
        $stmt = $pdo->prepare("SELECT price, stock FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if (!$product || $quantity > $product['stock']) {
            throw new Exception("Invalid quantity for product ID $product_id.");
        }

        $total += $product['price'] * $quantity;
    }

    $total += $shipping_cost;

    // Insert order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, address, city, postal_code, country, total, shipping_method_id, payment_method, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $_SESSION['user']['id'],
        $address,
        $city,
        $postal_code,
        $country,
        $total,
        $shipping_method_id,
        $payment_method
    ]);

    $order_id = $pdo->lastInsertId();

    // Insert order items + update stock
    $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt_stock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

    foreach ($cart as $product_id => $item) {
        $stmt_item->execute([$order_id, $product_id, $item['quantity'], $item['price']]);
        $stmt_stock->execute([$item['quantity'], $product_id]);
    }

    $pdo->commit();
    unset($_SESSION['cart']);
    header("Location: thank_you.php?order_id=$order_id");
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    die("Order failed: " . $e->getMessage());
}
