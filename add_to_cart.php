<?php 
session_start();
require 'includes/auth.php';
checkAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate product_id
        $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1]
        ]);

        if (!$product_id || !$quantity) {
            throw new Exception('Invalid product or quantity');
        }

        require_once 'includes/db.php';
        $pdo = getPDO();

        // Fetch product details
        $stmt = $pdo->prepare("SELECT id, title, price, image_path, stock FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if (!$product) {
            throw new Exception('Product not found');
        }

        if ($product['stock'] == 0) {
            throw new Exception('Sorry, this product is out of stock');
        }

        if ($quantity > $product['stock']) {
            throw new Exception('Only ' . $product['stock'] . ' items left in stock');
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$product_id])) {
            $newQuantity = $_SESSION['cart'][$product_id]['quantity'] + $quantity;

            if ($newQuantity > $product['stock']) {
                throw new Exception('Cannot add more than ' . $product['stock'] . ' items');
            }

            $_SESSION['cart'][$product_id]['quantity'] = $newQuantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'product_id' => $product['id'], // ✅ FIXED
                'name' => $product['title'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'image' => $product['image_path'],
                'max_stock' => $product['stock']
            ];
        }

        $_SESSION['success'] = 'Item added to cart!';
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
exit();
