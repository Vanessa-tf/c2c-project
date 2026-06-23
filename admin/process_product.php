<?php
require_once __DIR__ . '/../includes/auth.php';
checkAdmin();
$pdo = getPDO();


if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    try {
        switch($action) {
            case 'create':
                // Validate new product
                $title = htmlspecialchars($_POST['title']);
                $description = htmlspecialchars($_POST['description']);
                $price = (float)$_POST['price'];
                $category = $_POST['category'];
                
                // Handle file upload
                $image_path = handleFileUpload();
                
                // Create product
                $stmt = $pdo->prepare("INSERT INTO products 
                    (title, description, price, category, image_path)
                    VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $price, $category, $image_path]);
                
                header("Location: products.php?success=Product created");
                break;
                
            case 'update':
                // Validate existing product
                $product_id = (int)$_POST['product_id'];
                $title = htmlspecialchars($_POST['title']);
                $description = htmlspecialchars($_POST['description']);
                $price = (float)$_POST['price'];
                $category = $_POST['category'];
                
                // Get existing image
                $stmt = $pdo->prepare("SELECT image_path FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $current_image = $stmt->fetchColumn();
                
                // Handle new image upload
                $image_path = !empty($_FILES['image']['name']) 
                    ? handleFileUpload() 
                    : $current_image;
                
                // Update product
                $stmt = $pdo->prepare("UPDATE products SET
                    title = ?, description = ?, price = ?, 
                    category = ?, image_path = ?
                    WHERE id = ?");
                $stmt->execute([$title, $description, $price, $category, $image_path, $product_id]);
                
                header("Location: products.php?success=Product updated");
                break;
                
            case 'delete':
                $product_id = (int)$_POST['product_id'];
                
                // Delete product
                $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                
                header("Location: products.php?success=Product deleted");
                break;
        }
        
    } catch(Exception $e) {
        $location = ($action === 'create') ? 'add_product.php' : "edit_product.php?id=$product_id";
        header("Location: $location?error=" . urlencode($e->getMessage()));
        exit();
    }
}

function handleFileUpload() {
    $upload_dir = '../images/';
    if(!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $allowed_types = ['jpg', 'jpeg', 'png'];
    $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    
    if(!in_array($file_ext, $allowed_types)) {
        throw new Exception("Only JPG/PNG images allowed");
    }
    
    $file_name = uniqid() . '.' . $file_ext;
    $target_path = $upload_dir . $file_name;
    
    if(!move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
        throw new Exception("Failed to upload image");
    }
    
    return $file_name;
}