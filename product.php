<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// Get product ID from URL
$product_id = $_GET['id'] ?? 0;

try {
    $pdo = getPDO();

    // Get product details including seller name
    $stmt = $pdo->prepare("SELECT p.*, u.name as seller_name 
                          FROM products p 
                          JOIN users u ON p.user_id = u.id 
                          WHERE p.id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        throw new Exception("Product not found");
    }

    // Get related products in the same category except current
    $stmt = $pdo->prepare("SELECT * FROM products 
                          WHERE category = ? AND id != ? 
                          LIMIT 4");
    $stmt->execute([$product['category'], $product_id]);
    $related_products = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
} catch (Exception $e) {
    die($e->getMessage());
}
?>

<?php include __DIR__ . '/header.php'; ?>

<div class="product-container">
    <div class="product-images">
        <!-- Main product image -->
        <img src="/c2c-ecommerce/images/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
    </div>

    <div class="product-details">
        <h1><?= htmlspecialchars($product['title']) ?></h1>

        <div class="product-meta">
            <span class="price">R<?= number_format($product['price'], 2) ?></span>
            <span class="stock <?= $product['stock'] > 0 ? 'in-stock' : 'out-of-stock' ?>">
                <?= $product['stock'] > 0 ? 'In Stock' : 'Out of Stock' ?>
            </span>
            <span class="seller">Sold by: <?= htmlspecialchars($product['seller_name']) ?></span>
        </div>

        <div class="product-description">
            <?= nl2br(htmlspecialchars($product['description'])) ?>
        </div>

        <?php if ($product['stock'] > 0): ?>
            <form action="/c2c-ecommerce/add_to_cart.php" method="post" class="add-to-cart-form">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <div class="quantity-selector">
                    <label for="quantity">Quantity:</label>
                    <select name="quantity" id="quantity">
                        <?php 
                        $max_quantity = min($product['stock'], 10);
                        for ($i = 1; $i <= $max_quantity; $i++): 
                        ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <button type="submit" class="add-to-cart-btn">
                    Add to Cart
                </button>
            </form>
        <?php else: ?>
            <div class="out-of-stock-notice">
                <p>This product is currently out of stock</p>
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['id'] != $product['user_id']): ?>
                    <button class="notify-me-btn">Notify Me When Available</button>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Contact Seller Form -->
        <?php if (isset($_SESSION['user']) && $_SESSION['user']['id'] != $product['user_id']): ?>
            <div class="contact-seller">
                <h3>Ask the Seller</h3>
                <form action="/c2c-ecommerce/start_conversation.php" method="post">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="seller_id" value="<?= $product['user_id'] ?>">
                    <textarea name="message" placeholder="Your message..." required></textarea>
                    <button type="submit">Send Message</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Related Products -->
<?php if (!empty($related_products)): ?>
    <div class="related-products">
        <h2>You May Also Like</h2>
        <div class="products-grid">
            <?php foreach ($related_products as $related): ?>
                <div class="product-card">
                    <a href="/c2c-ecommerce/product.php?id=<?= $related['id'] ?>">
                        <img src="/c2c-ecommerce/images/<?= htmlspecialchars($related['image']) ?>" alt="<?= htmlspecialchars($related['title']) ?>">
                        <h3><?= htmlspecialchars($related['title']) ?></h3>
                        <p class="price">R<?= number_format($related['price'], 2) ?></p>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
