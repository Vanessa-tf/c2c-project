<?php
session_start();
include(__DIR__ . "/includes/db.php");
include(__DIR__ . "/includes/functions.php");

check_session();
$user_id = $_SESSION['user_id'];

// Fetch exam details
$exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;
$stmt = $pdo->prepare("SELECT * FROM mock_exams WHERE id = ? AND is_active = 1");
$stmt->execute([$exam_id]);
$current_exam = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$current_exam) {
    header("Location: student-dashboard.php");
    exit;
}

// Check for active attempt
$stmt = $pdo->prepare("SELECT * FROM mock_exam_attempts WHERE user_id = ? AND exam_id = ? AND completed_at IS NULL");
$stmt->execute([$user_id, $exam_id]);
$attempt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$attempt) {
    // Start new attempt
    $start_time = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("INSERT INTO mock_exam_attempts (user_id, exam_id, start_time) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $exam_id, $start_time]);
    $attempt_id = $pdo->lastInsertId();
} else {
    $attempt_id = $attempt['id'];
    $start_time = $attempt['start_time'];
}

$questions = json_decode($current_exam['questions'], true);
$duration = $current_exam['duration'] * 60; // Convert minutes to seconds
$elapsed = strtotime('now') - strtotime($start_time);
$time_remaining = max(0, $duration - $elapsed);

$submitted = false;
$score = 0;
$percentage = 0;
$completed_at = null;

// Handle exam submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attempt_id'])) {
    $attempt_id = intval($_POST['attempt_id']);
    $answers = $_POST['answers'] ?? [];
    $total_questions = count($questions);

    // For auto-marking (as per FR09)
    foreach ($questions as $index => $q) {
        if (isset($answers[$index]) && $answers[$index] == $q['correct_answer']) {
            $score++;
        }
    }

    $percentage = ($score / $total_questions) * 100;
    $completed_at = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare("UPDATE mock_exam_attempts SET answers = ?, score = ?, completed_at = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([json_encode($answers), $percentage, $completed_at, $attempt_id, $user_id]);

    $submitted = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mock Exam - NovaTech FET College</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        .border-gold { border-color: var(--gold); }
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
            <h1 class="text-2xl font-bold text-navy mb-6"><?php echo htmlspecialchars($current_exam['title']); ?></h1>
            <div class="bg-white rounded-xl shadow-lg p-6">
                <?php if (!$submitted): ?>
                    <div id="timer" class="text-lg mb-4 text-navy">Time Remaining: <span id="time-display"></span></div>
                    <form id="exam-form" action="mock-exams.php?exam_id=<?php echo $exam_id; ?>" method="POST">
                        <input type="hidden" name="attempt_id" value="<?php echo $attempt_id; ?>">
                        <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">
                        <?php foreach ($questions as $index => $q): ?>
                        <div class="mb-6">
                            <p class="font-medium text-navy"><?php echo ($index + 1) . '. ' . htmlspecialchars($q['question']); ?></p>
                            <?php foreach ($q['options'] as $opt_index => $option): ?>
                            <label class="block text-gray-700">
                                <input type="radio" name="answers[<?php echo $index; ?>]" value="<?php echo $opt_index; ?>" class="mr-2">
                                <?php echo htmlspecialchars($option); ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php endforeach; ?>
                        <button type="submit" class="bg-navy text-white px-4 py-2 rounded-lg hover:bg-opacity-90 transition">Submit Exam</button>
                    </form>
                <?php else: ?>
                    <div class='bg-green-100 p-4 rounded-lg'>
                        <h3 class='text-lg font-semibold text-navy'>Exam Submitted Successfully!</h3>
                        <p>Your exam has been saved and auto-marked.</p>
                        <p>Score: <?php echo $score; ?> / <?php echo count($questions); ?> (<?php echo number_format($percentage, 1); ?>%)</p>
                        <p>Completed at: <?php echo $completed_at; ?></p>
                        <a href='review-exam.php?attempt_id=<?php echo $attempt_id; ?>' class='text-gold hover:underline'>Review Answers</a> | 
                        <a href='student-dashboard.php' class='text-gold hover:underline'>Back to Dashboard</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        <?php if (!$submitted): ?>
        const duration = <?php echo $time_remaining; ?>;
        let timeLeft = duration;
        const timerDisplay = document.getElementById('time-display');
        const examForm = document.getElementById('exam-form');

        function updateTimer() {
            if (timeLeft <= 0) {
                alert('Time is up! Submitting exam...');
                examForm.submit();
                return;
            }
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerDisplay.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
            timeLeft--;
            setTimeout(updateTimer, 1000);
        }

        if (timeLeft > 0) {
            updateTimer();
        }

        // Basic browser lock
        window.addEventListener('blur', () => {
            alert('Please stay on the exam page to avoid submission issues.');
        });

        // Save progress via AJAX
        examForm.addEventListener('change', () => {
            const formData = new FormData(examForm);
            fetch('save-exam-progress.php', {
                method: 'POST',
                body: formData
            }).then(response => response.json())
              .then(data => {
                  if (data.status === 'success') {
                      console.log('Progress saved');
                  }
              });
        });
        <?php endif; ?>
    </script>
</body>
</html>