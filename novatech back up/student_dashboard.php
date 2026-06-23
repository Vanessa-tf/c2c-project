<?php
session_start();
include(__DIR__ . "/includes/db.php");
include(__DIR__ . "/includes/functions.php");

// Check if user is logged in and has 'student' role
check_session();
if ($_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Get user's initials for avatar
$username = htmlspecialchars($user['username']);
$initials = strtoupper(substr($username, 0, 2));

// Fetch enrolled courses
$stmt = $pdo->prepare("SELECT course_id, course_name, progress, lessons_remaining FROM enrollments WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch upcoming live lessons for enrolled courses
$live_lessons = [];
if (!empty($courses)) {
    $course_names = implode("','", array_column($courses, 'course_name'));
    $stmt = $pdo->prepare("SELECT lesson_name, date, start_time, end_time, link 
                           FROM live_lessons 
                           WHERE course_name IN ('$course_names') AND date >= CURDATE() 
                           ORDER BY date ASC, start_time ASC 
                           LIMIT 2");
    $stmt->execute();
    $live_lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format live lessons for display
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    foreach ($live_lessons as &$lesson) {
        $lesson_date = $lesson['date'];
        if ($lesson_date == $today) {
            $lesson['day'] = 'Today';
            $lesson['color'] = 'bg-gold';
            $lesson['text_color'] = 'text-navy';
        } elseif ($lesson_date == $tomorrow) {
            $lesson['day'] = 'Tomorrow';
            $lesson['color'] = 'bg-blue-100';
            $lesson['text_color'] = 'text-blue-800';
        } else {
            $lesson['day'] = date('M d', strtotime($lesson_date));
            $lesson['color'] = 'bg-gray-100';
            $lesson['text_color'] = 'text-gray-800';
        }
        $lesson['time'] = date('h:i A', strtotime($lesson['start_time'])) . ' - ' . date('h:i A', strtotime($lesson['end_time']));
    }
}

// Fetch past exam papers
$stmt = $pdo->prepare("SELECT title, description, file_link, year FROM exam_papers ORDER BY year DESC LIMIT 2");
$stmt->execute();
$past_papers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate assignments due
$assignments_due = array_sum(array_column($courses, 'lessons_remaining'));

// Fetch mock exams for enrolled courses
$course_ids = implode(',', array_column($courses, 'course_id'));
$mock_exams = [];
if ($course_ids) {
    $stmt = $pdo->prepare("SELECT me.id, me.course_id, me.title, me.description, mea.score, mea.completed_at 
                           FROM mock_exams me 
                           LEFT JOIN mock_exam_attempts mea ON me.id = mea.exam_id AND mea.user_id = :user_id 
                           WHERE me.course_id IN ($course_ids) AND me.is_active = 1 
                           ORDER BY me.created_at DESC LIMIT 2");
    $stmt->execute(['user_id' => $user_id]);
    $mock_exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - NovaTech FET College</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --navy: #1e3a6c;
            --gold: #fbbf24;
            --beige: #f5f5dc;
        }
        body { font-family: 'Poppins', sans-serif; }
        .bg-navy { background-color: var(--navy); }
        .bg-gold { background-color: var(--gold); }
        .bg-beige { background-color: var(--beige); }
        .text-navy { color: var(--navy); }
        .text-gold { color: var(--gold); }
        .border-gold { border-color: var(--gold); }
        .dashboard-card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .dashboard-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1); }
        .sidebar { transition: all 0.3s ease; }
        @media (max-width: 768px) {
            .sidebar { position: fixed; left: -300px; z-index: 1000; height: 100vh; }
            .sidebar.active { left: 0; }
            .overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 999; }
            .overlay.active { display: block; }
        }
        .progress-bar { transition: width 1s ease-in-out; }
        .notification-dot { position: absolute; top: -5px; right: -5px; width: 12px; height: 12px; background-color: #ef4444; border-radius: 50%; }
    </style>
</head>
<body class="bg-beige">
    <!-- Overlay for mobile sidebar -->
    <div class="overlay" id="overlay"></div>

    <!-- Sidebar Navigation -->
    <div class="sidebar bg-navy text-white w-64 fixed h-screen overflow-y-auto" id="sidebar">
        <div class="p-6">
            <div class="flex items-center justify-between mb-10">
                <div class="flex items-center">
                    <img src="Images/ChatGPT Image Sep 15, 2025, 08_43_22 PM.png" alt="NovaTech Logo" class="h-10 w-auto"/>
                    <span class="ml-3 text-xl font-bold"><span>NovaTech</span></span>
                </div>
                <button class="text-white md:hidden" id="closeSidebar"><i class="fas fa-times"></i></button>
            </div>
            <div class="mb-8 p-4 bg-white bg-opacity-10 rounded-lg">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gold rounded-full flex items-center justify-center mr-3">
                        <span class="text-navy font-bold"><?php echo $initials; ?></span>
                    </div>
                    <div>
                        <h3 class="font-semibold"><?php echo $username; ?></h3>
                        <p class="text-gold text-sm"><?php echo implode(', ', array_column($courses, 'course_name')); ?></p>
                    </div>
                </div>
            </div>
            <nav class="space-y-2">
                <a href="student-dashboard.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white <?php echo basename($_SERVER['PHP_SELF']) == 'student-dashboard.php' ? 'border-b-2 border-gold' : ''; ?>">
                    <i class="fas fa-home mr-3"></i><span>Dashboard</span>
                </a>
                <a href="my-courses.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white <?php echo basename($_SERVER['PHP_SELF']) == 'my-courses.php' ? 'border-b-2 border-gold' : ''; ?>">
                    <i class="fas fa-book-open mr-3"></i><span>My Subjects</span>
                </a>
                <a href="past-papers.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white <?php echo basename($_SERVER['PHP_SELF']) == 'past-papers.php' ? 'border-b-2 border-gold' : ''; ?>">
                    <i class="fas fa-file-alt mr-3"></i><span>Past Papers</span>
                </a>
                <a href="live-lessons.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white <?php echo basename($_SERVER['PHP_SELF']) == 'live-lessons.php' ? 'border-b-2 border-gold' : ''; ?>">
                    <i class="fas fa-video mr-3"></i><span>Live Lessons</span>
                </a>
                <a href="progress-tracking.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white <?php echo basename($_SERVER['PHP_SELF']) == 'progress-tracking.php' ? 'border-b-2 border-gold' : ''; ?>">
                    <i class="fas fa-chart-line mr-3"></i><span>Progress Tracking</span>
                </a>
                <a href="study-groups.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white <?php echo basename($_SERVER['PHP_SELF']) == 'study-groups.php' ? 'border-b-2 border-gold' : ''; ?>">
                    <i class="fas fa-users mr-3"></i><span>Social Forums</span>
                </a>
                <a href="schedule.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white <?php echo basename($_SERVER['PHP_SELF']) == 'schedule.php' ? 'border-b-2 border-gold' : ''; ?>">
                    <i class="fas fa-calendar-alt mr-3"></i><span>Schedule</span>
                </a>
                <a href="settings.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'border-b-2 border-gold' : ''; ?>">
                    <i class="fas fa-cog mr-3"></i><span>Settings</span>
                </a>
                <a href="logout.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white <?php echo basename($_SERVER['PHP_SELF']) == 'logout.php' ? 'border-b-2 border-gold' : ''; ?>">
                    <i class="fas fa-sign-out-alt mr-3"></i><span>Logout</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="md:ml-64">
        <!-- Top Navigation -->
        <header class="bg-white shadow-md">
            <div class="container mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <button class="text-navy md:hidden" id="menuButton"><i class="fas fa-bars"></i></button>
                    <h1 class="text-xl font-bold text-navy">Student Dashboard</h1>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <button class="text-navy"><i class="fas fa-bell"></i><span class="notification-dot"></span></button>
                        </div>
                        <div class="hidden md:block">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gold rounded-full flex items-center justify-center mr-2">
                                    <span class="text-navy font-bold text-sm"><?php echo $initials; ?></span>
                                </div>
                                <span class="text-navy"><?php echo $username; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dashboard Content -->
        <main class="container mx-auto px-6 py-8">
            <!-- Welcome Banner -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8 dashboard-card">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-navy mb-2">Welcome back, <?php echo $username; ?>!</h2>
                        <p class="text-gray-600">You have <?php echo count($live_lessons); ?> upcoming live lessons and <?php echo $assignments_due; ?> assignments due this week.</p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <a href="my-courses.php" class="bg-gold text-navy font-bold py-2 px-6 rounded-lg hover:bg-yellow-500 transition inline-block">Continue Learning</a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Progress Overview -->
                    <div class="bg-white rounded-xl shadow-lg p-6 dashboard-card">
                        <h2 class="text-xl font-bold text-navy mb-6">Progress Overview</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php foreach ($courses as $course): ?>
                            <div>
                                <h3 class="font-semibold text-navy mb-3"><?php echo htmlspecialchars($course['course_name']); ?></h3>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-gold h-2.5 rounded-full progress-bar" style="width: <?php echo $course['progress']; ?>%"></div>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600 mt-2">
                                    <span><?php echo $course['progress']; ?>% Complete</span>
                                    <span><?php echo $course['progress'] / 5; ?>/20 Topics</span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- My Courses -->
                    <div class="bg-white rounded-xl shadow-lg p-6 dashboard-card">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-bold text-navy">My Courses</h2>
                            <a href="my-courses.php" class="text-gold hover:underline">View All</a>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($courses as $index => $course): ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                <div class="flex items-center mb-3">
                                    <div class="w-10 h-10 <?php echo $index % 2 == 0 ? 'bg-blue-100' : 'bg-purple-100'; ?> rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas <?php echo $index % 2 == 0 ? 'fa-calculator text-blue-600' : 'fa-atom text-purple-600'; ?>"></i>
                                    </div>
                                    <h3 class="font-semibold text-navy"><?php echo htmlspecialchars($course['course_name']); ?></h3>
                                </div>
                                <p class="text-gray-600 text-sm mb-3"><?php echo $index % 2 == 0 ? 'Master algebra, calculus, and problem-solving techniques' : 'Physics and chemistry concepts for matric success'; ?></p>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500"><?php echo $course['lessons_remaining']; ?> lessons remaining</span>
                                    <a href="my-courses.php" class="text-gold text-sm font-medium">Continue</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Past Exam Papers -->
                    <div class="bg-white rounded-xl shadow-lg p-6 dashboard-card">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-bold text-navy">Past Exam Papers</h2>
                            <a href="past-papers.php" class="text-gold hover:underline">View All</a>
                        </div>
                        <div class="space-y-4">
                            <?php foreach ($past_papers as $paper): ?>
                            <div class="flex justify-between items-center p-3 border border-gray-200 rounded-lg">
                                <div>
                                    <h3 class="font-medium text-navy"><?php echo htmlspecialchars($paper['title']); ?></h3>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($paper['description']); ?></p>
                                </div>
                                <a href="<?php echo htmlspecialchars($paper['file_link']); ?>" class="bg-navy text-white text-sm py-1 px-3 rounded-lg hover:bg-opacity-90 transition">Download</a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-8">
                    <!-- Upcoming Live Lessons -->
                    <div class="bg-white rounded-xl shadow-lg p-6 dashboard-card">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-bold text-navy">Live Lessons</h2>
                            <a href="live-lessons.php" class="text-gold hover:underline">View Schedule</a>
                        </div>
                        <div class="space-y-4">
                            <?php foreach ($live_lessons as $lesson): ?>
                            <div class="p-3 border border-gray-200 rounded-lg">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-medium text-navy"><?php echo htmlspecialchars($lesson['lesson_name']); ?></h3>
                                    <span class="<?php echo $lesson['color']; ?> <?php echo $lesson['text_color']; ?> text-xs py-1 px-2 rounded-full"><?php echo $lesson['day']; ?></span>
                                </div>
                                <p class="text-sm text-gray-600 mb-2"><i class="far fa-clock mr-2"></i><?php echo $lesson['time']; ?></p>
                                <a href="<?php echo htmlspecialchars($lesson['link']); ?>" class="text-gold text-sm font-medium">Join Microsoft Teams</a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Study Groups -->
                    <div class="bg-white rounded-xl shadow-lg p-6 dashboard-card">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-bold text-navy">Study Groups</h2>
                            <a href="study-groups.php" class="text-gold hover:underline">See All</a>
                        </div>
                        <div class="space-y-4">
                            <?php for ($i = 0; $i < 2 && $i < count($courses); $i++): ?>
                            <div class="flex items-center p-3 border border-gray-200 rounded-lg">
                                <div class="w-10 h-10 <?php echo $i % 2 == 0 ? 'bg-green-100' : 'bg-purple-100'; ?> rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-users <?php echo $i % 2 == 0 ? 'text-green-600' : 'text-purple-600'; ?>"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium text-navy"><?php echo htmlspecialchars($courses[$i]['course_name']); ?> Study Group</h3>
                                    <p class="text-sm text-gray-600"><?php echo ($i % 2 == 0 ? 5 : 8); ?> members • Last active: <?php echo ($i % 2 == 0 ? 2 : 5); ?> hours ago</p>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Performance Analytics -->
                    <div class="bg-white rounded-xl shadow-lg p-6 dashboard-card">
                        <h2 class="text-xl font-bold text-navy mb-6">Performance Analytics</h2>
                        <canvas id="performanceChart" width="100%" height="200"></canvas>
                    </div>

                    <!-- Mock Exams -->
                    <div class="bg-white rounded-xl shadow-lg p-6 dashboard-card">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-bold text-navy">Mock Exams</h2>
                            <a href="mock-exams.php" class="text-gold hover:underline">View All</a>
                        </div>
                        <div class="space-y-4">
                            <?php foreach ($mock_exams as $exam): ?>
                            <div class="p-3 border border-gray-200 rounded-lg">
                                <h3 class="font-medium text-navy mb-2"><?php echo htmlspecialchars($exam['title']); ?></h3>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">
                                        <?php echo $exam['completed_at'] ? 'Completed: ' . number_format($exam['score'], 1) . '%' : 'Not Started'; ?>
                                    </span>
                                    <a href="<?php echo $exam['completed_at'] ? 'review-exam.php?attempt_id=' . $exam['id'] : 'mock-exams.php?exam_id=' . $exam['id']; ?>" 
                                       class="text-gold text-sm font-medium"><?php echo $exam['completed_at'] ? 'Review' : 'Start'; ?></a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php if (empty($mock_exams)): ?>
                            <p class="text-sm text-gray-600">No mock exams available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Mobile sidebar toggle
        const menuButton = document.getElementById('menuButton');
        const closeSidebar = document.getElementById('closeSidebar');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        
        menuButton.addEventListener('click', () => {
            sidebar.classList.add('active');
            overlay.classList.add('active');
        });
        
        closeSidebar.addEventListener('click', () => {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
        
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
        
        // Performance Chart
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [<?php echo "'" . implode("', '", array_column($courses, 'course_name')) . "'"; ?>],
                datasets: [{
                    label: 'Current Score',
                    data: [<?php echo implode(', ', array_column($courses, 'progress')); ?>],
                    backgroundColor: '#fbbf24',
                    borderColor: '#f59e0b',
                    borderWidth: 1
                }, {
                    label: 'Target Score',
                    data: [<?php echo implode(', ', array_fill(0, count($courses), 85)); ?>],
                    backgroundColor: '#1e3a8a',
                    borderColor: '#1e40af',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { callback: function(value) { return value + '%'; } }
                    }
                }
            }
        });
        
        // Animate progress bars
        document.querySelectorAll('.progress-bar').forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0';
            setTimeout(() => { bar.style.width = width; }, 500);
        });
    </script>
</body>
</html>