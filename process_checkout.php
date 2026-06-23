<?php
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
checkAuth();

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

try {
    // Initialize database connection
    $pdo = getPDO(); // THIS WAS MISSING
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Calculate total from cart instead of trusting POST
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    // Insert order
    $stmt = $pdo->prepare("INSERT INTO orders 
        (user_id, total, shipping_address, payment_method) 
        VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user']['id'],
        $total, // Use calculated total instead of $_POST
        htmlspecialchars($_POST['address']),
        htmlspecialchars($_POST['payment_method'])
    ]);
    
    $orderId = $pdo->lastInsertId();
    
    // Insert order items with product verification
    $stmt = $pdo->prepare("INSERT INTO order_items 
        (order_id, product_id, quantity, price) 
        VALUES (?, ?, ?, ?)");

    foreach ($_SESSION['cart'] as $productId => $item) {
        // Verify product exists
        $productCheck = $pdo->prepare("SELECT price FROM products WHERE id = ?");
        $productCheck->execute([$productId]);
        $product = $productCheck->fetch();
        
        if (!$product) {
            throw new Exception("Product {$productId} no longer exists");
        }

        $stmt->execute([
            $orderId,
            $productId,
            $item['quantity'],
            $product['price'] // Use actual price from DB
        ]);
    }
    
    $pdo->commit();
    
    // Clear cart and redirect
    unset($_SESSION['cart']);
    header("Location: thank_you.php?order_id=" . urlencode($orderId));
    exit();

} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    $_SESSION['error'] = "Checkout failed: " . $e->getMessage();
    header("Location: checkout.php");
    exit();
}