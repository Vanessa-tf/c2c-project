<?php
session_start();
include(__DIR__ . "/includes/db.php");
include(__DIR__ . "/includes/functions.php");

check_session();
if ($_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all attempts
$stmt = $pdo->prepare("SELECT mea.id, mea.score, mea.completed_at, me.title 
                       FROM mock_exam_attempts mea 
                       JOIN mock_exams me ON mea.exam_id = me.id 
                       WHERE mea.user_id = ? 
                       ORDER BY mea.completed_at DESC");
$stmt->execute([$user_id]);
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Exams - NovaTech FET College</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --navy: #1e3a6c;
            --gold: #fbbf24;
            --beige: #f5f5dc;
        }
        body { font-family: 'Poppins', sans-serif; background-color: var(--beige); }
        .bg-navy { background-color: var(--navy); }
        .bg-gold { background-color: var(--gold); }
        .text-navy { color: var(--navy); }
        .text-gold { color: var(--gold); }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar bg-navy text-white w-64 fixed h-screen overflow-y-auto" id="sidebar">
        <div class="p-6">
            <div class="flex items-center justify-between mb-10">
                <div class="flex items-center">
                    <img src="Images/ChatGPT Image Sep 15, 2025, 08_43_22 PM.png" alt="NovaTech Logo" class="h-10 w-auto"/>
                    <span class="ml-3 text-xl font-bold"><span>NovaTech</span></span>
                </div>
                <button class="text-white md:hidden" id="closeSidebar"><i class="fas fa-times"></i></button>
            </div>
            <?php include(__DIR__ . '/includes/sidebar-nav.php'); ?>
        </div>
    </div>

    <!-- Main Content -->
    <div class="md:ml-64 p-6">
        <div class="container mx-auto max-w-4xl">
            <h1 class="text-2xl font-bold text-navy mb-6">My Mock Exam Reviews</h1>
            <div class="bg-white rounded-xl shadow-lg p-6">
                <?php if (empty($attempts)): ?>
                <p class="text-gray-600">No completed mock exams yet.</p>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($attempts as $attempt): ?>
                    <div class="flex justify-between items-center p-3 border border-gray-200 rounded-lg">
                        <div>
                            <h3 class="font-medium text-navy"><?php echo htmlspecialchars($attempt['title']); ?></h3>
                            <p class="text-sm text-gray-600">Completed: <?php echo $attempt['completed_at']; ?></p>
                            <p class="text-sm text-gray-600">Score: <?php echo number_format($attempt['score'], 1); ?>%</p>
                        </div>
                        <a href="review-exam.php?attempt_id=<?php echo $attempt['id']; ?>" class="text-gold hover:underline">Review</a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <a href="student-dashboard.php" class="text-gold hover:underline mt-4 inline-block">Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>