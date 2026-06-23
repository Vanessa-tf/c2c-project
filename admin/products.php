<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Get database connection
$pdo = getPDO();

// Check admin permissions
checkAdmin();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Product Management</title>
    <link rel="stylesheet" href="/c2c-ecommerce/css/style.css">
</head>
<body>
    <?php include 'admin_header.php'; ?>
    
    <div class="admin-content">
        <h2>Manage Products</h2>
        <a href="add_product.php" class="btn-add">+ Add New Product</a>

        <table class="data-table">
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Title</th>
                <th>Price</th>
                <th>Category</th>
                <th>Actions</th>
            </tr>
            <?php
            try {
                $stmt = $pdo->query("SELECT p.*, u.name AS seller 
                                     FROM products p
                                     JOIN users u ON p.user_id = u.id");
                while ($product = $stmt->fetch()):
            ?>
            <tr>
                <td><?= $product['id'] ?></td>
                <td><img src="../images/<?= htmlspecialchars($product['image_path']) ?>" width="50"></td>
                <td><?= htmlspecialchars($product['title']) ?></td>
                <td>R <?= number_format($product['price'], 2) ?></td>
                <td><?= htmlspecialchars($product['category']) ?></td>
                <td class="action-buttons">
                    <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn-edit">Edit</a>
                    <form action="process_product.php" method="POST" class="delete-form">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <button type="submit" class="btn-delete" 
                            onclick="return confirm('Delete this product permanently?')">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
            <?php 
                endwhile;
            } catch (PDOException $e) {
                echo "<tr><td colspan='6'>Error loading products: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>
