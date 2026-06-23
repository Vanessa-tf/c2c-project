<?php
include_once "../db/db_connect.php";
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Generate Reports</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header>
    <h1>SmallStreet Admin - Reports</h1>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="users.php">Manage Users</a>
        <a href="products.php">Manage Products</a>
        <a href="report.php">Reports</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<section>
    <h2>Generate Reports</h2>

    <form method="post" action="report_download.php">
        <label>Select Report Type:</label>
        <select name="report_type" required>
            <option value="users">Users Report</option>
            <option value="products">Products Report</option>
            <option value="categories">Listings by Category</option>
        </select>
        <br><br>
        <button type="submit">Download CSV</button>
    </form>
</section>

<footer>
    <p>&copy; 2025 SmallStreet Markets Admin</p>
</footer>
</body>
</html>
