<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_step = $_POST['current_step'];
    $data = $_SESSION['enroll_data'] ?? [];

    // Merge new data
    foreach ($_POST as $key => $value) {
        if ($key !== 'current_step' && $key !== 'action') {
            $data[$key] = $value;
        }
    }

    // Handle file uploads
    if (!empty($_FILES['proof_upload']['name'])) {
        $file = $_FILES['proof_upload'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_name = uniqid() . '.' . $ext;
        $path = 'uploads/' . $new_name;
        if (move_uploaded_file($file['tmp_name'], $path)) {
            $data['proof_upload'] = $path;
        }
    }

    if (!empty($_FILES['sponsor_letter']['name'])) {
        $file = $_FILES['sponsor_letter'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_name = uniqid() . '.' . $ext;
        $path = 'uploads/' . $new_name;
        if (move_uploaded_file($file['tmp_name'], $path)) {
            $data['sponsor_letter_upload'] = $path;
        }
    }

    // Generate password if not exists
    if (empty($data['password'])) {
        $data['password'] = bin2hex(random_bytes(4)); // Simple 8-char password
    }

    // Save to session
    $_SESSION['enroll_data'] = $data;

    // Save to database
    try {
        $email = $data['email'] ?? '';
        $stmt = $pdo->prepare("SELECT id FROM students WHERE email = ?");
        $stmt->execute([$email]);
        $student = $stmt->fetch();

        if (!$student) {
            // Insert new student
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO students (email, phone, password) VALUES (?, ?, ?)");
            $stmt->execute([$email, $data['phone'] ?? '', $password_hash]);
            $student_id = $pdo->lastInsertId();
            $_SESSION['student_id'] = $student_id;
        } else {
            $student_id = $student['id'];
            $_SESSION['student_id'] = $student_id;
        }

        // Save progress
        $json_data = json_encode($data);
        $stmt = $pdo->prepare("INSERT INTO enrollment_progress (student_id, current_step, data) VALUES (?, ?, ?) 
                               ON DUPLICATE KEY UPDATE current_step = ?, data = ?");
        $stmt->execute([$student_id, $current_step, $json_data, $current_step, $json_data]);

        // If reached step 6, generate OTP and go to step 7
        if ($current_step == 6) {
            $otp = '123456'; // In real system: rand(100000, 999999)
            $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            $stmt = $pdo->prepare("UPDATE students SET otp = ?, otp_expiry = ? WHERE id = ?");
            $stmt->execute([$otp, $expiry, $student_id]);

            $_SESSION['enroll_email'] = $email;
            $current_step = 7;
        }

        // Redirect to next step
        header("Location: enroll.php?step=" . $current_step);
        exit;

    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>