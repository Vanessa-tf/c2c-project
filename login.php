<!-- login.php -->
<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login - SmallStreet</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="login-form" style="max-width: 400px; margin: 20px auto; padding: 20px;">
        <h2>Welcome Back! 🇿🇦</h2>
        
       <?php if (isset($_GET['error'])): ?>
    <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
    <p style="color: green;"><?= htmlspecialchars($_GET['success']) ?></p>
<?php endif; ?>


        <form action="process_login.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>

        <p>New here? <a href="register.php">Create an account</a></p>
    </div>
</body>
</html>