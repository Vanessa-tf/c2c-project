<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/includes/db.php';

try {
    $pdo = getPDO();
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SmallStreet Markets - Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <section class="categories">
        <h2>Browse Categories</h2>
        <div class="category-list">
            <?php
            try {
                $categories = $pdo->query("SELECT id, name FROM categories")->fetchAll();
                foreach($categories as $category):
            ?>
                <div class="cat">
                    <a href="#" onclick="goToCategory('<?= urlencode($category['name']) ?>'); return false;">
                        <?= htmlspecialchars($category['name']) ?>
                    </a>
                </div>
            <?php
                endforeach;
            } catch(PDOException $e) {
                echo "<p class='error'>Error loading categories: " . $e->getMessage() . "</p>";
            }
            ?>
        </div>
    </section>

    <section class="products">
        <h2>Popular Listings</h2>
        <div class="product-grid">
            <?php
            try {
                $search = $_GET['search'] ?? '';
                $searchTerm = "%$search%";
                
                $stmt = $pdo->prepare("
                    SELECT 
                        id, 
                        title, 
                        price, 
                        category,
                        COALESCE(image_path, 'placeholder.jpg') AS image 
                    FROM products 
                    WHERE title LIKE ? 
                    OR description LIKE ?
                    ORDER BY created_at DESC
                ");
                
                $stmt->execute([$searchTerm, $searchTerm]);

                if ($stmt->rowCount() === 0) {
                    echo "<p class='notice'>No products found" . (!empty($search) ? " for '$search'" : "") . ".</p>";
                } else {
                    while($product = $stmt->fetch()):
                        $image_path = "images/" . $product['image'];
            ?>
                        <div class="product-card">
                            <a href="product.php?id=<?= $product['id'] ?>">
                                <img src="<?= $image_path ?>" 
                                     alt="<?= htmlspecialchars($product['title']) ?>" 
                                     onerror="this.src='images/placeholder.jpg'">
                                <h3><?= htmlspecialchars($product['title']) ?></h3>
                                <p class="price">R <?= number_format($product['price'], 2) ?></p>
                                <p class="category"><?= htmlspecialchars($product['category']) ?></p>
                            </a>
                        </div>
            <?php
                    endwhile;
                }
            } catch(PDOException $e) {
                echo "<p class='error'>Error loading products: " . $e->getMessage() . "</p>";
            }
            ?>
        </div>
    </section>

    <footer>
        <p>&copy; <?= date('Y') ?> SmallStreet Markets - South Africa's C2C Platform</p>
    </footer>

    <script>
    function goToCategory(name) {
        window.location.href = 'category.php?name=' + name;
    }
    </script>
</body>
</html>
