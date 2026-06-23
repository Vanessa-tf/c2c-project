<?php
include 'includes/auth.php';
checkAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>About Us - SmallStreet</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="about-page">
        <section class="about-intro">
            <h1>About SmallStreet</h1>
            <p>Welcome to SmallStreet, your local marketplace for unique artisan products made with love. We connect buyers with passionate sellers who craft beautiful, one-of-a-kind items.</p>
        </section>

        <section class="our-mission">
            <h2>Our Mission</h2>
            <p>Our mission is to empower local artisans and small businesses by providing a platform that celebrates creativity, craftsmanship, and community.</p>
        </section>

        <section class="contact-info">
            <h2>Contact Us</h2>
            <p>If you have any questions or want to learn more about SmallStreet, feel free to <a href="contact.php">get in touch</a>.</p>
        </section>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
