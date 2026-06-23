<?php
session_start();
include 'includes/auth.php';
checkAuth();

function calculateTotal() {
    $total = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
    }
    return $total;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Your Cart - SmallStreet</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="cart-container">
    <h2>🛒 Review Your Cart 🇿🇦</h2>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (empty($_SESSION['cart'])): ?>
        <p class="empty-cart">Your cart is empty. Start shopping!</p>
        <a href="index.php" class="btn-primary">Browse Products</a>
    <?php else: ?>
        <div class="cart-items">
            <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                <div class="cart-item">
                    <img src="images/<?= htmlspecialchars($item['image']) ?>" 
                         alt="<?= htmlspecialchars($item['name']) ?>" class="cart-image">
                    <div class="cart-item-info">
                        <h3><?= htmlspecialchars($item['name']) ?></h3>
                        <p>Unit Price: R<?= number_format($item['price'], 2) ?></p>
                        <p>Subtotal: R<?= number_format($item['price'] * $item['quantity'], 2) ?></p>

                        <div class="quantity-controls">
                            <button class="update-qty" data-id="<?= $product_id ?>" data-action="decrease">−</button>
                            <input type="number" value="<?= htmlspecialchars($item['quantity']) ?>" readonly>
                            <button class="update-qty" data-id="<?= $product_id ?>" data-action="increase">+</button>
                        </div>
                    </div>
                    <a href="remove_from_cart.php?product_id=<?= urlencode($product_id) ?>" 
                       class="btn-danger">Remove</a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="cart-total">
            <h3>Total: R<?= number_format(calculateTotal(), 2) ?></h3>
            <a href="checkout.php" class="btn-checkout">Proceed to Checkout ➔</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        const buttons = document.querySelectorAll(".update-qty");

        buttons.forEach(button => {
            button.addEventListener("click", function () {
                const productId = this.getAttribute("data-id");
                const action = this.getAttribute("data-action");

                const formData = new FormData();
                formData.append("product_id", productId);
                formData.append("action", action);

                fetch("update_cart.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => {
                    if (response.ok) {
                        location.reload(); 
                    }
                });
            });
        });
    });
</script>

</body>
</html>
