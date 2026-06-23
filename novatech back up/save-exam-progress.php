<?php
session_start();
include(__DIR__ . "/includes/db.php");
include(__DIR__ . "/includes/functions.php");

check_session();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attempt_id'])) {
    $attempt_id = intval($_POST['attempt_id']);
    $answers = $_POST['answers'] ?? [];

    $stmt = $pdo->prepare("UPDATE mock_exam_attempts SET answers = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([json_encode($answers), $attempt_id, $_SESSION['user_id']]);

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>