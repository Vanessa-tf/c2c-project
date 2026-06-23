<?php
session_start();
require_once __DIR__ . '/includes/db.php';

$pdo = getPDO();  // Get PDO connection

// Get category name from URL
$categoryName = $_GET['name'] ?? '';
$categoryName = urldecode($categoryName);

try {
    // Get category details
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE name = ?");
    $stmt->execute([$categoryName]);
    $category = $stmt->fetch();

    if (!$category) {
        header("Location: index.php");
        exit();
    }

    // Get products in this category
    $productsStmt = $pdo->prepare("SELECT * FROM products WHERE category = ?");
    $productsStmt->execute([$categoryName]);
    $products = $productsStmt->fetchAll();

} catch(PDOException $e) {
    $error = "Error loading category: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($categoryName) ?> - SmallStreet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="category-page">
        <!-- Category Header -->
        <section class="category-header">
            <h1><?= htmlspecialchars($categoryName) ?></h1>
            <p class="product-count"><?= count($products) ?> listings available</p>
        </section>

        <!-- Product Grid (Matches Homepage Style) -->
        <div class="product-grid">
            <?php if(!empty($products)): ?>
                <?php foreach($products as $product): 
                    $image_path = "images/" . ($product['image_path'] ?? 'placeholder.jpg');
                ?>
                <div class="product-card">
                    <a href="product.php?id=<?= $product['id'] ?>">
                        <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($product['title']) ?>">
                        <h3><?= htmlspecialchars($product['title']) ?></h3>
                        <p class="price">R <?= number_format($product['price'], 2) ?></p>
                        <p class="seller">By <?= htmlspecialchars($product['seller_name'] ?? 'Local Artisan') ?></p>
                    </a>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>No products found in this category.</p>
                    <a href="index.php" class="btn-primary">Browse All Categories</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
