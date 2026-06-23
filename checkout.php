<?php
session_start();
require_once 'includes/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Fetch active shipping methods
$pdo = getPDO();
$stmt = $pdo->query("SELECT * FROM shipping_methods WHERE active = 1");
$shipping_methods = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Checkout - SmallStreet</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="checkout">
    <h2>Checkout</h2>
    <form action="process_order.php" method="POST">
        <label for="address">Address:</label><br>
        <input type="text" name="address" id="address" required><br><br>

        <label for="city">City:</label><br>
        <input type="text" name="city" id="city" required><br><br>

        <label for="postal_code">Postal Code:</label><br>
        <input type="text" name="postal_code" id="postal_code" required><br><br>

        <label for="country">Country:</label><br>
        <input type="text" name="country" id="country" required><br><br>

        <label for="shipping_method">Shipping Method:</label><br>
        <select name="shipping_method_id" id="shipping_method" required>
            <option value="">-- Select Shipping Method --</option>
            <?php foreach ($shipping_methods as $method): ?>
                <option value="<?= htmlspecialchars($method['id']) ?>">
                    <?= htmlspecialchars($method['name']) ?> (R<?= number_format($method['cost'], 2) ?>)
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="payment_method">Payment Method:</label><br>
        <select name="payment_method" id="payment_method" required>
            <option value="">-- Select Payment Method --</option>
            <option value="cash">Cash on Delivery</option>
            <option value="card">Card Payment</option>
        </select><br><br>

        <div id="card-details" style="display: none;">
            <label for="card_number">Card Number:</label><br>
            <input type="text" name="card_number" id="card_number" maxlength="16" pattern="\d{16}" title="Enter a valid 16-digit card number"><br><br>

            <label for="expiry">Expiry Date (MM/YY):</label><br>
            <input type="text" name="expiry" id="expiry" pattern="\d{2}/\d{2}" title="Format should be MM/YY"><br><br>

            <label for="cvv">CVV:</label><br>
            <input type="text" name="cvv" id="cvv" maxlength="4" pattern="\d{3,4}" title="3 or 4 digit CVV"><br><br>
        </div>

        <button type="submit" class="btn-primary">Place Order</button>
    </form>
</div>

<?php include 'footer.php'; ?>

<script>
document.getElementById('payment_method').addEventListener('change', function () {
    const cardFields = document.getElementById('card-details');
    cardFields.style.display = (this.value === 'card') ? 'block' : 'none';
});

document.getElementById('card_number').addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, ''); // allow digits only
});
</script>
</body>
</html>
