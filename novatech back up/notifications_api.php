<?php
session_start();
include(__DIR__ . "/includes/db.php");
include(__DIR__ . "/includes/functions.php");

// Check if user is logged in and is a student
check_session();
if ($_SESSION['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch notifications (unread first, then read, limited to 10)
$stmt = $pdo->prepare("
    SELECT id, message, type, is_read, created_at 
    FROM notifications 
    WHERE user_id = :user_id 
    ORDER BY is_read ASC, created_at DESC 
    LIMIT 10
");
$stmt->execute(['user_id' => $user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($notifications);
?>