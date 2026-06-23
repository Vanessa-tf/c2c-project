<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

checkAuth();

$pdo = getPDO();

$product_id = $_GET['product_id'] ?? 0;
$buyer_id = $_GET['buyer_id'] ?? 0;
$seller_id = $_GET['seller_id'] ?? 0;
$user_id = $_SESSION['user']['id'];

if ($user_id != $buyer_id && $user_id != $seller_id) {
    die("Access denied.");
}

$stmt = $pdo->prepare("SELECT title FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();
if (!$product) {
    die("Product not found.");
}

$stmt = $pdo->prepare("SELECT * FROM messages 
                       WHERE product_id = ? AND buyer_id = ? AND seller_id = ?
                       ORDER BY created_at ASC");
$stmt->execute([$product_id, $buyer_id, $seller_id]);
$messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Chat about <?= htmlspecialchars($product['title']) ?></title>
    <link rel="stylesheet" href="/c2c-ecommerce/css/style.css">
</head>
<body>
<?php include __DIR__ . '/header.php'; ?>

<div class="conversation-container">
    <h2>Conversation: <?= htmlspecialchars($product['title']) ?></h2>

    <div class="messages">
        <?php foreach ($messages as $msg): ?>
            <div class="message <?= $msg['buyer_id'] == $user_id ? 'sent' : 'received' ?>">
                <p><?= htmlspecialchars($msg['message']) ?></p>
                <small><?= date('M j, Y g:i a', strtotime($msg['created_at'])) ?></small>
            </div>
        <?php endforeach; ?>
    </div>

    <form action="send_message.php" method="POST" class="message-form">
        <input type="hidden" name="product_id" value="<?= $product_id ?>">
        <input type="hidden" name="buyer_id" value="<?= $buyer_id ?>">
        <input type="hidden" name="seller_id" value="<?= $seller_id ?>">
        <textarea name="message" required></textarea>
        <button type="submit">Send</button>
    </form>
</div>
</body>
</html>
