<?php
// Line 1-4
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
checkAuth();


$user_id = $_SESSION['user']['id'];
$conversations = $pdo->query("
    SELECT c.*, p.title AS product_title, 
           u.name AS other_user, COUNT(m.id) AS unread
    FROM conversations c
    JOIN products p ON c.product_id = p.id
    JOIN users u ON (c.buyer_id = u.id AND u.id != $user_id) 
                 OR (c.seller_id = u.id AND u.id != $user_id)
    LEFT JOIN messages m ON m.conversation_id = c.id 
                         AND m.sender_id != $user_id 
                         AND m.is_read = 0
    WHERE c.buyer_id = $user_id OR c.seller_id = $user_id
    GROUP BY c.id
    ORDER BY c.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html>
<body>
    <div class="inbox">
        <h2>Your Conversations</h2>
        <?php foreach($conversations as $convo): ?>
            <div class="convo-item">
                <a href="conversation.php?id=<?= $convo['id'] ?>">
                    <?= htmlspecialchars($convo['product_title']) ?> 
                    (<?= $convo['other_user'] ?>)
                    <?php if($convo['unread'] > 0): ?>
                        <span class="unread"><?= $convo['unread'] ?></span>
                    <?php endif; ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>