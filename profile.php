<?php
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
checkAuth();

try {
    // Initialize database connection
    $pdo = getPDO(); // THIS IS THE MISSING LINE
    
    $user_id = $_SESSION['user']['id'];
    $products = $pdo->prepare("SELECT * FROM products WHERE user_id = ?");
    $products->execute([$user_id]);
    
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Your Profile</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="profile">
        <h2>Hi <?= htmlspecialchars($_SESSION['user']['name']) ?>!</h2>
        <h3>Your Listings:</h3>
        
        <div class="user-products">
            <?php if($products->rowCount() > 0): ?>
                <?php while($product = $products->fetch()): ?>
                    <div class="product">
                        <img src="images/<?= htmlspecialchars($product['image_path']) ?>" 
                             alt="<?= htmlspecialchars($product['title']) ?>"
                             onerror="this.src='images/placeholder.jpg'">
                        <p><?= htmlspecialchars($product['title']) ?></p>
                        <p>R<?= number_format($product['price'], 2) ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-listings">You haven't listed any products yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>