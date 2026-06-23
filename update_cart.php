<?php
session_start();
include 'includes/auth.php';
checkAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $action = $_POST['action'];
    
    if (isset($_SESSION['cart'][$product_id])) {
        switch ($action) {
            case 'increase':
                $_SESSION['cart'][$product_id]['quantity']++;
                break;
            case 'decrease':
                if ($_SESSION['cart'][$product_id]['quantity'] > 1) {
                    $_SESSION['cart'][$product_id]['quantity']--;
                }
                break;
        }
    }
    
    header("Location: cart.php");
    exit();
}