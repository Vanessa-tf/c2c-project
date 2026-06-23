<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
checkAdmin();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Product - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include 'admin_header.php'; ?>
    
    <div class="admin-form">
        <h2>Add New Product</h2>
        <?php if(isset($_GET['error'])): ?>
            <p class="error"><?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>
        
        <form action="process_product.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create">
            
            <div class="form-group">
                <label>Title:</label>
                <input type="text" name="title" required>
            </div>
            
            <div class="form-group">
                <label>Description:</label>
                <textarea name="description" rows="4"></textarea>
            </div>
            
            <div class="form-group">
                <label>Price (ZAR):</label>
                <input type="number" name="price" step="0.01" min="0" required>
            </div>
            
            <div class="form-group">
                <label>Category:</label>
                <select name="category" required>
                    <option value="Traditional">Traditional</option>
                    <option value="Handmade">Handmade</option>
					<option value="Electronics">Electronics</option>
					<option value="Services">Services</option>
					<option value="Fashion">Fashion</option>
					<option value="Home & Garden">Home & Garden</option>
                    <option value="Art">Art</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Product Image:</label>
                <input type="file" name="image" accept="image/*" required>
            </div>
            
            <button type="submit">Add Product</button>
        </form>
    </div>
</body>
</html>