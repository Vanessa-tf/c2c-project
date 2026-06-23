<?php
include 'includes/auth.php';
checkAuth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Contact Us - SmallStreet</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="contact-page">
        <section class="contact-intro">
            <h1>Contact SmallStreet</h1>
            <p>We’d love to hear from you! Whether you have questions, feedback, or need support, please reach out.</p>
        </section>

        <section class="contact-form-section">
            <form action="send_contact.php" method="post" class="contact-form">
                <label for="name">Your Name</label>
                <input type="text" id="name" name="name" required />

                <label for="email">Your Email</label>
                <input type="email" id="email" name="email" required />

                <label for="message">Message</label>
                <textarea id="message" name="message" rows="6" required></textarea>

                <button type="submit" class="btn-primary">Send Message</button>
            </form>
        </section>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
