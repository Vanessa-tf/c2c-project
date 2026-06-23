<?php
include_once "../db/db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = intval($_POST['product_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $address = $conn->real_escape_string($_POST['address']);

    $sql = "INSERT INTO orders (product_id, name, email, address)
            VALUES ('$product_id', '$name', '$email', '$address')";

    if ($conn->query($sql) === TRUE) {
        echo "<h2>Order placed successfully!</h2>";
        echo "<p>We will contact you shortly via email.</p>";
        echo "<a href='index.php'>Back to Home</a>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
