<?php include 'includes/db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - SmallStreet</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="register-form" style="max-width: 400px; margin: 20px auto; padding: 20px;">
        <h2>Join SmallStreet 🇿🇦</h2>
        
        <?php if (isset($_GET['error'])): ?>
            <p style="color: red;"><?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>

        <form action="process_register.php" method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
        </form>
        
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>