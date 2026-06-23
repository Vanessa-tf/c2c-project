<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<?php if(isset($_SESSION['success'])): ?>
    <div class="alert success">
        <?= $_SESSION['success'] ?>
        <?php unset($_SESSION['success']) ?>
    </div>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
    <div class="alert error">
        <?= $_SESSION['error'] ?>
        <?php unset($_SESSION['error']) ?>
    </div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SmallStreet Markets</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header>
    <h1>
      <span class="logo-s">S</span>mall
      <span class="logo-s">S</span>treet Markets 🇿🇦
    </h1>

    <!-- Navigation -->
    <nav>
      <?php if(isset($_SESSION['user'])): ?>
        <a href="cart.php">Cart (<?= count($_SESSION['cart'] ?? []) ?>)</a>
        <a href="profile.php">Profile</a>
        <a href="sell.php">Sell</a>
        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="index.php">Home</a>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
      <?php endif; ?>
    </nav>

    <!-- Single search bar -->
    <div class="search-bar">
      <form action="search.php" method="GET">
        <input type="text" name="query" placeholder="Search handmade goods...">
        <button type="submit">🔍</button>
      </form>
    </div>
  </header> <!-- Closing header tag here -->