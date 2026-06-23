<?php
session_start();
include 'includes/auth.php';
checkAuth();

if (isset($_GET['product_id'])) {
    $product_id = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);
    
    if ($product_id && isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        $_SESSION['success'] = "Item removed from cart";
    } else {
        $_SESSION['error'] = "Invalid product";
    }
}

header("Location: cart.php");
exit();