<?php
session_start();
unset($_SESSION['admin']); // Remove admin session data
session_destroy();          // Destroy the session
header("Location: login.php");
exit();
?>