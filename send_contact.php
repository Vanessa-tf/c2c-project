<?php
session_start();

// Basic sanitization and validation
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if (empty($name) || empty($email) || empty($message)) {
    $_SESSION['error'] = "Please fill in all fields.";
    header("Location: contact.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Please enter a valid email address.";
    header("Location: contact.php");
    exit();
}

// Here you would typically send an email or save to database
// For example, using mail():
// $to = 'your-email@example.com';
// $subject = "New contact form message from $name";
// $body = "Name: $name\nEmail: $email\n\nMessage:\n$message";
// $headers = "From: $email";

// if (mail($to, $subject, $body, $headers)) {
//     $_SESSION['success'] = "Thank you for contacting us, we will get back to you soon!";
// } else {
//     $_SESSION['error'] = "Failed to send your message, please try again.";
//     header("Location: contact.php");
//     exit();
// }

// Since we don’t have mail set up, just simulate success
$_SESSION['success'] = "Thank you for contacting us, we will get back to you soon!";
header("Location: contact.php");
exit();
?>
