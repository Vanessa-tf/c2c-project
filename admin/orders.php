<?php
// Top of admin/orders.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Get database connection
$pdo = getPDO();

// Check admin permissions
checkAdmin();

// Handle order deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order'])) {
    $order_id = $_POST['order_id'];
    
    try {
        // First delete order items
        $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt->execute([$order_id]);
        
        // Then delete the order
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        
        $_SESSION['message'] = "Order #$order_id has been deleted";
        header("Location: orders.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting order: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Management</title>
    <link rel="stylesheet" href="/c2c-ecommerce/css/style.css">
</head>
<body>
    <?php include 'admin_header.php'; ?>
    
    <div class="admin-content">
        <h2>Manage Orders</h2>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <table class="data-table">
            <tr>
                <th>Order ID</th>
                <th>User Email</th>
                <th>Total</th>
                <th>Order Date</th>
                <th>Actions</th>
            </tr>
            <?php
            try {
                $stmt = $pdo->query("
                    SELECT o.id, o.total, o.created_at, u.email
                    FROM orders o
                    JOIN users u ON o.user_id = u.id
                    ORDER BY o.created_at DESC
                ");
                
                while($order = $stmt->fetch()):
            ?>
            <tr>
                <td><?= $order['id'] ?></td>
                <td><?= htmlspecialchars($order['email']) ?></td>
                <td>R<?= number_format($order['total'], 2) ?></td>
                <td><?= htmlspecialchars($order['created_at']) ?></td>
                <td class="action-buttons">
                    <a href="view_order.php?id=<?= $order['id'] ?>" class="btn-view">View</a>
                    <form action="orders.php" method="POST" class="delete-form">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <button type="submit" name="delete_order" class="btn-delete"
                            onclick="return confirm('Permanently delete this order?')">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
            <?php
                endwhile;
            } catch (PDOException $e) {
                echo "<tr><td colspan='5'>Error loading orders: " . $e->getMessage() . "</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>