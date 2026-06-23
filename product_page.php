<?php
session_start();
require 'includes/auth.php';
checkAuth();
require 'includes/db.php';
$pdo = getPDO();

// Fetch product info
$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    echo "Product not found";
    exit;
}
?>

<h1><?= htmlspecialchars($product['title']) ?></h1>
<img src="<?= htmlspecialchars($product['image_path']) ?>" alt="Product Image">
<p><?= htmlspecialchars($product['description']) ?></p>
<p>Price: R<?= htmlspecialchars($product['price']) ?></p>
<p>Stock: <?= $product['stock'] ?></p>

<!-- Add to Cart Form -->
<?php if ($product['stock'] > 0): ?>
    <form action="product.php" method="POST" class="add-to-cart-form">
        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

        <label for="quantity">Quantity:</label>
        <input
            type="number"
            name="quantity"
            id="quantity"
            value="1"
            min="1"
            max="<?= $product['stock'] ?>"
            required
        >

        <button type="submit">Add to Cart</button>
    </form>
<?php else: ?>
    <p style="color:red;">Out of Stock</p>
<?php endif; ?>

</form>
