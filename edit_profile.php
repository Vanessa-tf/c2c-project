<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Get current user data
$user_id = $_SESSION['user']['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile - SmallStreet</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="edit-profile-container">
        <h2>Edit Your Profile</h2>

        <?php if (isset($_GET['error'])): ?>
            <p class="error"><?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <p class="success"><?= htmlspecialchars($_GET['success']) ?></p>
        <?php endif; ?>

        <form action="process_edit_profile.php" method="POST">
            <div class="form-group">
                <label>Full Name:</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>

            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="form-group">
                <label>Current Password (to confirm changes):</label>
                <input type="password" name="current_password" required>
            </div>

            <div class="form-group">
                <label>New Password (leave blank to keep current):</label>
                <input type="password" name="new_password">
            </div>

            <div class="form-group">
                <label>Confirm New Password:</label>
                <input type="password" name="confirm_password">
            </div>

            <button type="submit" class="update-btn">Update Profile</button>
        </form>
    </div>
</body>
</html>