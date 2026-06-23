<?php
include 'includes/db.php';
$search_query = $_GET['query'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Search - SmallStreet</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="search-results">
        <h2>Results for "<?= htmlspecialchars($search_query) ?>"</h2>
        <div class="product-list">
            <?php
            try {
                $pdo = getPDO();

                // Case-insensitive search on title or description
                $stmt = $pdo->prepare("SELECT * FROM products 
                    WHERE LOWER(title) LIKE LOWER(?)
                    OR LOWER(description) LIKE LOWER(?)
                    ORDER BY created_at DESC");

                $search_term = "%" . trim($search_query) . "%";
                $stmt->execute([$search_term, $search_term]);

                $resultsFound = false;

                while ($product = $stmt->fetch()):
                    $resultsFound = true;
            ?>
                <div class="product-card">
                    <a href="product.php?id=<?= $product['id'] ?>">
                        <img src="images/<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
                        <h3><?= htmlspecialchars($product['title']) ?></h3>
                        <p><?= htmlspecialchars($product['description']) ?></p>
                        <p>R<?= number_format($product['price'], 2) ?></p>
                    </a>
                </div>
            <?php
                endwhile;

                if (!$resultsFound) {
                    echo "<p>No products found matching your search.</p>";
                }

            } catch (PDOException $e) {
                error_log("Search error: " . $e->getMessage());
                echo "<p>Sorry, we couldn't complete your search. Please try again.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>
