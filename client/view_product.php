<?php
include_once "../db/db_connect.php";

if (!isset($_GET['id'])) {
    die("Product ID not provided.");
}

$product_id = intval($_GET['id']);
$sql = "SELECT * FROM products WHERE id = $product_id";
$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
    die("Product not found.");
}

$product = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order - <?= htmlspecialchars($product['title']) ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<h2>Order: <?= htmlspecialchars($product['title']) ?></h2>
<img src="/c2c-ecommerce/images/<?= htmlspecialchars($product['image']) ?>" width="200">

<p>Price: R<?= htmlspecialchars($product['price']) ?></p>

<form action="submit_order.php" method="POST">
    <input type="hidden" name="product_id" value="<?= $product_id ?>">

    <label>Name:</label><br>
    <input type="text" name="name" required><br>

    <label>Email:</label><br>
    <input type="email" name="email" required><br>

    <label>Address:</label><br>
    <textarea name="address" required></textarea><br><br>

    <button type="submit">Place Order</button>
</form>

</body>
</html>
