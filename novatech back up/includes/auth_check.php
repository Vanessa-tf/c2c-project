<?php
session_start();

// If user is not logged in, redirect to login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Optional: Check if user is a student (if needed)
//if ($_SESSION['role'] !== 'student') {
//     die("Access denied. Students only.");
// }
?>