<?php
// Line 2-3 should be:
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
checkAdmin();

$messages = $pdo->query("
    SELECT m.*, u.name AS sender, c.product_id
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    JOIN conversations c ON m.conversation_id = c.id
    ORDER BY m.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html>
<body>
    <?php include 'admin_header.php'; ?>
    
    <div class="admin-content">
        <h2>All Messages</h2>
        <table>
            <tr>
                <th>Product</th>
                <th>Sender</th>
                <th>Message</th>
                <th>Date</th>
            </tr>
            <?php foreach($messages as $msg): ?>
            <tr>
                <td><a href="../product.php?id=<?= $msg['product_id'] ?>">View Product</a></td>
                <td><?= htmlspecialchars($msg['sender']) ?></td>
                <td><?= htmlspecialchars($msg['message']) ?></td>
                <td><?= $msg['created_at'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>