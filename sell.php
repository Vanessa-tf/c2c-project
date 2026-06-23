<?php
session_start();
include 'includes/auth.php';
checkAuth();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Use YOUR database name
    $conn = new mysqli("localhost", "root", "", "smallstreet");
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $title = $conn->real_escape_string($_POST["title"]);
    $description = $conn->real_escape_string($_POST["description"]);
    $price = $conn->real_escape_string($_POST["price"]);
    $category = $conn->real_escape_string($_POST["category"]);

    // Handle image upload
    $target_dir = "images/";
    $image_name = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // Insert into database
        $sql = "INSERT INTO products (title, description, category, price, image_path, user_id)
                VALUES ('$title', '$description', '$category', '$price', '$image_name', {$_SESSION['user']['id']})";

        if ($conn->query($sql)) {
            $success = "Product added successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    } else {
        $error = "Image upload failed.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sell Product</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="sell-form">
        <h2>Add a Product</h2>
        
        <?php if(isset($success)): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form action="sell.php" method="post" enctype="multipart/form-data">
            <label>Title:</label><br>
            <input type="text" name="title" required><br><br>

            <label>Description:</label><br>
            <textarea name="description" rows="4" required></textarea><br><br>

            <label>Price (R):</label><br>
            <input type="number" step="0.01" name="price" required><br><br>

            <label>Category:</label><br>
            <select name="category" required>
                <option value="">Select Category</option>
                <option value="Art">Art</option>
                <option value="Traditonal">Traditonal</option>
                <option value="Electronics">Electronics</option>
                <option value="Plants">Plants</option>
                <option value="Handmade">Handmade</option>
                <option value="Home">Home</option>
                <option value="Garden">Garden</option>
                <option value="Services">Services</option>
                <option value="Fashion">Fashion</option>
            </select><br><br>

            <label>Image:</label><br>
            <input type="file" name="image" accept="image/*" required><br><br>

            <input type="submit" value="Add Product">
        </form>
    </div>
</body>
</html>