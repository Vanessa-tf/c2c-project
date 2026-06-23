<?php
include_once "../db/db_connect.php";
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$reportType = $_POST['report_type'] ?? '';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $reportType . '_report.csv"');

$output = fopen('php://output', 'w');

if ($reportType === 'users') {
    fputcsv($output, ['ID', 'Username', 'Email', 'Role']);
    $result = $conn->query("SELECT id, username, email, role FROM users");
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
} elseif ($reportType === 'products') {
    fputcsv($output, ['ID', 'Title', 'Seller ID', 'Category', 'Price']);
    $result = $conn->query("SELECT id, title, seller_id, category, price FROM products");
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
} elseif ($reportType === 'categories') {
    fputcsv($output, ['Category', 'Total Listings']);
    $result = $conn->query("SELECT category, COUNT(*) as total FROM products GROUP BY category");
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
} else {
    fputcsv($output, ['Invalid report type']);
}

fclose($output);
exit;
