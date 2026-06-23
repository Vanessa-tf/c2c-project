<?php
require_once __DIR__ . '/../includes/auth.php';
checkAdmin();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add User - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include 'admin_header.php'; ?>
    
    <div class="admin-form">
        <h2>Create New User</h2>
        <?php if(isset($_GET['error'])): ?>
            <p class="error"><?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>
        
        <form action="process_user.php" method="POST">
            <input type="hidden" name="action" value="create">
            
            <div class="form-group">
                <label>Full Name:</label>
                <input type="text" name="name" required>
            </div>
            
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label>Role:</label>
                <select name="role" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            
            <button type="submit">Create User</button>
        </form>
    </div>
</body>
</html>