<?php
// client/index.php
include_once "../db/db_connect.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SmallStreet Markets - Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Google Font for stylized S -->
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

  <header>
    <h1>
      <span class="logo-s">S</span>mall
      <span class="logo-s">S</span>treet Markets 🇿🇦
    </h1>
    <nav>
      <a href="#">Home</a>
      <a href="../sell.php">Sell Something</a>
      <a href="login.html">Login</a>
      <a href="register.html">Register</a>
    </nav>
  </header>

  <section class="products">
    <h2>All Listings</h2>
    <div class="gallery">
      <?php
        $sql = "SELECT * FROM products";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $title = htmlspecialchars($row['title']);
                $price = htmlspecialchars($row['price']);
                $img   = htmlspecialchars($row['image']);
                echo "
                <div class=\"product-card\">
                  <img src=\"/c2c-ecommerce/images/{$img}\" alt=\"{$title}\">
                  <h3>{$title}</h3>
                  <p>R{$price}</p>
                  <a href=\"view_product.php?id={$row['id']}\"><button>Order Now</button></a>
                </div>
                ";
            }
        } else {
            echo "<p>No products found. Be the first to <a href=\"../sell.php\">sell something</a>!</p>";
        }
      ?>
    </div>
  </section>

  <footer>
    <p>&copy; 2025 SmallStreet Markets - South Africa's C2C Platform</p>
  </footer>

</body>
</html>
