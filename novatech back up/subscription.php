<?php
require_once 'includes/auth_check.php';
require_once 'includes/config.php';

// Fetch all available packages
$stmt = $pdo->prepare("SELECT package_id, package_name, description, price, duration_days FROM subscriptions ORDER BY price ASC");
$stmt->execute();
$packages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose a Package - NovaTech LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="student_dashboard.php">NovaTech LMS</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text">Hello, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</span>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center mb-5">Choose Your Learning Package</h2>
        <p class="text-center mb-5">Select the plan that fits your needs and budget. All packages include access to past papers, study guides, and live lessons.</p>

        <div class="row justify-content-center">
            <?php foreach ($packages as $package): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow">
                        <div class="card-header text-center bg-light">
                            <h3 class="mb-0"><?php echo htmlspecialchars($package['package_name']); ?></h3>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="text-center mb-3">
                                <span class="display-5 fw-bold">R<?php echo number_format($package['price'], 2); ?></span>
                                <br>
                                <small class="text-muted">for <?php echo $package['duration_days']; ?> days</small>
                            </div>
                            <ul class="list-unstyled mb-4 flex-grow-1">
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Access to <?php echo htmlspecialchars($package['description']); ?></li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Live Lessons via MS Teams</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Mock Exams & Feedback</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Progress Tracking</li>
                            </ul>
                            <a href="process_payment.php?package_id=<?php echo $package['package_id']; ?>" class="btn btn-primary w-100 mt-auto">Subscribe Now</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="student_dashboard.php" class="btn btn-outline-secondary">← Back to Dashboard</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>