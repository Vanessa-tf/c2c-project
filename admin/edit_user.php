<?php
// edit_user.php
include '../includes/db.php'; // Add this line first
include '../includes/auth.php';
checkAdmin();

$pdo = getPDO();

if(!isset($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$user_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if(!$user) {
    header("Location: users.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include 'admin_header.php'; ?>
    
    <div class="admin-form">
        <h2>Edit User: <?= htmlspecialchars($user['name']) ?></h2>
        <?php if(isset($_GET['error'])): ?>
            <p class="error"><?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>
        
        <form action="process_user.php" method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
            
            <div class="form-group">
                <label>Full Name:</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>New Password (leave blank to keep current):</label>
                <input type="password" name="password">
            </div>
            
            <div class="form-group">
                <label>Role:</label>
                <select name="role" required>
                    <option value="user" <?= ($user['role'] ?? 'user') === 'user' ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= ($user['role'] ?? 'user') === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            
            <button type="submit" class="btn-primary">Update User</button>
        </form>
    </div>
</body>
</html>