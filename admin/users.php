<?php
// Top of admin/users.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Get database connection
$pdo = getPDO();  // Add this line

// Check admin permissions
checkAdmin();
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>
    <link rel="stylesheet" href="/c2c-ecommerce/css/style.css">
</head>
<body>
    <?php include 'admin_header.php'; ?>
    
    <div class="admin-content">
        <h2>Manage Users</h2>
        <a href="add_user.php" class="btn-add">+ Add New User</a>
        
        <table class="data-table">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
            <?php
            try {
                $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
                while($user = $stmt->fetch()):
            ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= ucfirst($user['role']) ?></td>
                <td class="action-buttons">
                    <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn-edit">Edit</a>
                    <form action="process_user.php" method="POST" class="delete-form">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <button type="submit" class="btn-delete" 
                            onclick="return confirm('Permanently delete this user?')">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
            <?php 
                endwhile;
            } catch (PDOException $e) {
                echo "<tr><td colspan='5'>Error loading users: " . $e->getMessage() . "</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>