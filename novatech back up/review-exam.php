<?php
session_start();
include(__DIR__ . "/includes/db.php");
include(__DIR__ . "/includes/functions.php");

check_session();
if ($_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$attempt_id = isset($_GET['attempt_id']) ? intval($_GET['attempt_id']) : 0;
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT mea.*, me.title, me.questions 
                       FROM mock_exam_attempts mea 
                       JOIN mock_exams me ON mea.exam_id = me.id 
                       WHERE mea.id = ? AND mea.user_id = ? AND mea.completed_at IS NOT NULL");
$stmt->execute([$attempt_id, $user_id]);
$attempt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$attempt) {
    header("Location: student-dashboard.php");
    exit;
}

$questions = json_decode($attempt['questions'], true);
$user_answers = json_decode($attempt['answers'], true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Exam - NovaTech FET College</title>
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
        .correct { color: green; font-weight: bold; }
        .incorrect { color: red; font-weight: bold; }
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
            <h1 class="text-2xl font-bold text-navy mb-6">Review: <?php echo htmlspecialchars($attempt['title']); ?></h1>
            <div class="bg-white rounded-xl shadow-lg p-6">
                <p class="text-lg mb-4">Score: <?php echo number_format($attempt['score'], 1); ?>%</p>
                <p class="mb-6">Completed on: <?php echo $attempt['completed_at']; ?></p>
                <?php foreach ($questions as $index => $q): ?>
                <div class="mb-6 border-b pb-4">
                    <p class="font-medium text-navy"><?php echo ($index + 1) . '. ' . htmlspecialchars($q['question']); ?></p>
                    <?php foreach ($q['options'] as $opt_index => $option): ?>
                    <p class="<?php 
                        if (isset($user_answers[$index]) && $user_answers[$index] == $opt_index) {
                            echo ($opt_index == $q['correct_answer']) ? 'correct' : 'incorrect';
                        } elseif ($opt_index == $q['correct_answer']) {
                            echo 'correct';
                        }
                    ?>">
                        <?php echo htmlspecialchars($option); ?>
                        <?php if (isset($user_answers[$index]) && $user_answers[$index] == $opt_index) { echo ' (Your answer)'; } ?>
                        <?php if ($opt_index == $q['correct_answer']) { echo ' (Correct)'; } ?>
                    </p>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
                <a href="review-exams.php" class="text-gold hover:underline">Back to Exam Reviews</a> | 
                <a href="student-dashboard.php" class="text-gold hover:underline">Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>