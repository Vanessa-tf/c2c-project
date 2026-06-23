<?php
// edit_product.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
checkAdmin();

$pdo = getPDO();

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: products.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include 'admin_header.php'; ?>
    
    <div class="admin-form">
        <h2>Edit Product: <?= htmlspecialchars($product['title']) ?></h2>

        <?php if (isset($_GET['error'])): ?>
            <p class="error"><?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>

        <form action="process_product.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

            <div class="form-group">
                <label>Title:</label>
                <input type="text" name="title" value="<?= htmlspecialchars($product['title']) ?>" required>
            </div>

            <div class="form-group">
                <label>Description:</label>
                <textarea name="description" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label>Price (ZAR):</label>
                <input type="number" name="price" step="0.01" min="0" value="<?= $product['price'] ?>" required>
            </div>

            <div class="form-group">
                <label>Category:</label>
                <select name="category" required>
                    <?php
                    $categories = ['Traditional', 'Handmade', 'Services', 'Fashion', 'Home & Garden', 'Electronics', 'Art'];
                    foreach ($categories as $cat): ?>
                        <option value="<?= $cat ?>" <?= $product['category'] == $cat ? 'selected' : '' ?>>
                            <?= $cat ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Current Image:</label><br>
                <img src="../images/<?= $product['image_path'] ?>" width="100" alt="Current Product Image"><br><br>
                <input type="file" name="image">
            </div>

            <button type="submit" class="btn-primary">Update Product</button>
        </form>
    </div>
</body>
</html>
