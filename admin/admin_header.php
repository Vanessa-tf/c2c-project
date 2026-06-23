<?php
// Remove any include/require statements that might cause circular references
// This file should ONLY contain header HTML, no business logic
?>
<header class="admin-header">
    <h1>
        <span class="logo-s">S</span>mall
        <span class="logo-s">S</span>treet Admin 🇿🇦
    </h1>
    
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="users.php">Manage Users</a>
        <a href="products.php">Manage Listings</a>
        <a href="logout.php" class="btn-primary">Logout (<?= htmlspecialchars($_SESSION['user']['name'] ?? 'Admin') ?>)</a>
    </nav>
</header>

<div class="main-container">