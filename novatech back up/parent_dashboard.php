<?php
session_start();
include(__DIR__ . "/includes/db.php");
include(__DIR__ . "/includes/functions.php");

// Check if user is logged in and has 'parent' role
check_session();
if ($_SESSION['role'] !== 'parent') {
    header("Location: login.php");
    exit;
}

// Fetch parent details
$parent_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, id_number FROM users WHERE id = :parent_id");
$stmt->execute(['parent_id' => $parent_id]);
$parent = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$parent) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Get parent's initials for avatar
$username = htmlspecialchars($parent['username']);
$initials = strtoupper(substr($username, 0, 2));

// Fetch child/children linked to this parent
$stmt = $pdo->prepare("SELECT u.id, u.username, u.email FROM users u 
                       INNER JOIN parent_child_links pcl ON u.id = pcl.child_id 
                       WHERE pcl.parent_id = :parent_id");
$stmt->execute(['parent_id' => $parent_id]);
$children = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If no children linked, show message
if (empty($children)) {
    $no_children = true;
    $courses = [];
    $live_lessons = [];
    $past_papers = [];
    $mock_exams = [];
    $assignments_due = 0;
} else {
    $no_children = false;
    // For simplicity, we'll focus on the first child. In a real system, you'd want parent to select which child
    $selected_child = $children[0];
    $child_id = $selected_child['id'];
    $child_name = htmlspecialchars($selected_child['username']);

    // Fetch child's enrolled courses
    $stmt = $pdo->prepare("SELECT course_id, course_name, progress, lessons_remaining FROM enrollments WHERE user_id = :child_id");
    $stmt->execute(['child_id' => $child_id]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch upcoming live lessons for child's enrolled courses
    $live_lessons = [];
    if (!empty($courses)) {
        $course_names = implode("','", array_column($courses, 'course_name'));
        $stmt = $pdo->prepare("SELECT lesson_name, date, start_time, end_time, link 
                               FROM live_lessons 
                               WHERE course_name IN ('$course_names') AND date >= CURDATE() 
                               ORDER BY date ASC, start_time ASC 
                               LIMIT 3");
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
    $stmt = $pdo->prepare("SELECT title, description, file_link, year FROM exam_papers ORDER BY year DESC LIMIT 3");
    $stmt->execute();
    $past_papers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate assignments due
    $assignments_due = array_sum(array_column($courses, 'lessons_remaining'));

    // Fetch mock exams for child's enrolled courses
    $course_ids = implode(',', array_column($courses, 'course_id'));
    $mock_exams = [];
    if ($course_ids) {
        $stmt = $pdo->prepare("SELECT me.id, me.course_id, me.title, me.description, mea.score, mea.completed_at 
                               FROM mock_exams me 
                               LEFT JOIN mock_exam_attempts mea ON me.id = mea.exam_id AND mea.user_id = :child_id 
                               WHERE me.course_id IN ($course_ids) AND me.is_active = 1 
                               ORDER BY me.created_at DESC LIMIT 3");
        $stmt->execute(['child_id' => $child_id]);
        $mock_exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Define subjects for progress tracking
$subject_chapters = [
    'Mathematics' => [
        'Chapter 1: Algebra and Equations',
        'Chapter 2: Functions and Graphs',
        'Chapter 3: Trigonometry',
        'Chapter 4: Calculus',
        'Chapter 5: Statistics and Probability',
        'Chapter 6: Euclidean Geometry',
        'Chapter 7: Analytical Geometry',
        'Chapter 8: Financial Mathematics'
    ],
    'Physical Sciences' => [
        'Chapter 1: Mechanics',
        'Chapter 2: Waves, Sound and Light',
        'Chapter 3: Electricity and Magnetism',
        'Chapter 4: Matter and Materials',
        'Chapter 5: Chemical Change',
        'Chapter 6: Chemical Systems',
        'Chapter 7: Reactions and Rates',
        'Chapter 8: Organic Chemistry'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard - NovaTech FET College</title>
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
        .child-selector { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; }
        .child-selector.active { max-height: 200px; }
        .chapter-list { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; }
        .chapter-list.active { max-height: 500px; }
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
                    <span class="ml-3 text-xl font-bold">NovaTech FET <span class="text-gold">College</span></span>
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
                        <p class="text-gold text-sm">Parent Portal</p>
                    </div>
                </div>
                
                <?php if (!$no_children && count($children) > 1): ?>
                <!-- Child Selector -->
                <div class="mt-4">
                    <button class="flex items-center justify-between w-full text-left text-sm text-white bg-white bg-opacity-10 rounded p-2" id="childSelector">
                        <span>Viewing: <?php echo $child_name; ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div id="childList" class="child-selector bg-white bg-opacity-5 rounded mt-2">
                        <?php foreach ($children as $child): ?>
                        <button class="block w-full text-left text-sm text-white hover:bg-white hover:bg-opacity-10 p-2 rounded child-option" 
                                data-child-id="<?php echo $child['id']; ?>" data-child-name="<?php echo htmlspecialchars($child['username']); ?>">
                            <?php echo htmlspecialchars($child['username']); ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <nav class="space-y-2">
                <a href="parent_dashboard.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white <?php echo basename($_SERVER['PHP_SELF']) == 'parent_dashboard.php' ? 'border-b-2 border-gold' : ''; ?>">
                    <i class="fas fa-home mr-3"></i><span>Dashboard</span>
                </a>
                <a href="child-progress.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white <?php echo basename($_SERVER['PHP_SELF']) == 'child-progress.php' ? 'border-b-2 border-gold' : ''; ?>">
                    <i class="fas fa-chart-line mr-3"></i><span>Child's Progress</span>
                </a>
                <a href="child-courses.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white <?php echo basename($_SERVER['PHP_SELF']) == 'child-courses.php' ? 'border-b-2 border-gold' : ''; ?>">
                    <i class="fas fa-book-open mr-3"></i><span>Enrolled Subjects</span>
                </a>
                <a href="child-schedule.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white <?php echo basename($_SERVER['PHP_SELF']) == 'child-schedule.php' ? 'border-b-2 border-gold' : ''; ?>">
                    <i class="fas fa-calendar-alt mr-3"></i><span>Class Schedule</span>
                </a>
                <a href="exam-results.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white <?php echo basename($_SERVER['PHP_SELF']) == 'exam-results.php' ? 'border-b-2 border-gold' : ''; ?>">
                    <i class="fas fa-clipboard-check mr-3"></i><span>Exam Results</span>
                </a>
                <a href="attendance.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white <?php echo basename($_SERVER['PHP_SELF']) == 'attendance.php' ? 'border-b-2 border-gold' : ''; ?>">
                    <i class="fas fa-user-check mr-3"></i><span>Attendance</span>
                </a>
                
                <a href="package-info.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white <?php echo basename($_SERVER['PHP_SELF']) == 'package-info.php' ? 'border-b-2 border-gold' : ''; ?>">
                    <i class="fas fa-box mr-3"></i><span>Package Details</span>
                </a>
                <a href="settings.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white <?php echo basename($_SERVER['PHP_SELF']) == 'parent-settings.php' ? 'border-b-2 border-gold' : ''; ?>">
                    <i class="fas fa-cog mr-3"></i><span>Settings</span>
                </a>
                <a href="logout.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white">
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
                    <h1 class="text-xl font-bold text-navy">Parent Dashboard</h1>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <button id="notificationButton" class="text-navy relative">
                                <i class="fas fa-bell"></i>
                                <span id="notificationDot" class="notification-dot hidden"></span>
                            </button>
                            <!-- Notification Dropdown -->
                            <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg z-50 max-h-96 overflow-y-auto">
                                <div class="p-4 border-b">
                                    <h3 class="text-lg font-semibold text-navy">Notifications</h3>
                                </div>
                                <div id="notificationList" class="divide-y">
                                    <!-- Notifications will be injected here -->
                                </div>
                                <div class="p-4 border-t text-center">
                                    <a href="notifications.php" class="text-gold hover:underline text-sm">View All Notifications</a>
                                </div>
                            </div>
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
            <?php if ($no_children): ?>
            <!-- No Children Linked -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-8 text-center">
                <i class="fas fa-user-plus text-6xl text-gold mb-4"></i>
                <h2 class="text-2xl font-bold text-navy mb-4">No Children Linked</h2>
                <p class="text-gray-600 mb-6">Your parent account is not currently linked to any student accounts. Please contact the school administrator to link your child's account.</p>
                <a href="#" class="bg-navy text-white font-bold py-2 px-6 rounded-lg hover:bg-opacity-90 transition">Contact Administrator</a>
            </div>
            <?php else: ?>
            
            <!-- Welcome Banner -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8 dashboard-card">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-navy mb-2">Welcome back, <?php echo $username; ?>!</h2>
                        <p class="text-gray-600">Monitoring <?php echo $child_name; ?>'s progress. 
                            <?php echo count($live_lessons); ?> upcoming classes and <?php echo $assignments_due; ?> exercises due this week.</p>
                    </div>
                    <div class="mt-4 md:mt-0 flex space-x-3">
                        <a href="child-progress.php" class="bg-gold text-navy font-bold py-2 px-6 rounded-lg hover:bg-yellow-500 transition">View Progress</a>
                        <a href="communication.php" class="bg-navy text-white font-bold py-2 px-6 rounded-lg hover:bg-opacity-90 transition">Message Teachers</a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Child's Progress Overview -->
                    <div class="bg-white rounded-xl shadow-lg p-6 dashboard-card">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-bold text-navy"><?php echo $child_name; ?>'s Progress Overview</h2>
                            <a href="child-progress.php" class="text-gold hover:underline">Detailed View</a>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php foreach ($courses as $course): ?>
                            <div>
                                <h3 class="font-semibold text-navy mb-3"><?php echo htmlspecialchars($course['course_name']); ?></h3>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-gold h-2.5 rounded-full progress-bar" style="width: <?php echo $course['progress']; ?>%"></div>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600 mt-2">
                                    <span><?php echo $course['progress']; ?>% Complete</span>
                                    <span><?php echo intval($course['progress'] / 5); ?>/20 Topics</span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Enrolled Subjects with Chapters -->
                    <div class="bg-white rounded-xl shadow-lg p-6 dashboard-card">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-bold text-navy">Enrolled Subjects</h2>
                            <a href="child-courses.php" class="text-gold hover:underline">View All</a>
                        </div>
                        <div class="space-y-4">
                            <?php foreach ($courses as $index => $course): 
                                $course_name = htmlspecialchars($course['course_name']);
                                $chapters = isset($subject_chapters[$course_name]) ? $subject_chapters[$course_name] : [];
                            ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 <?php echo $index % 2 == 0 ? 'bg-blue-100' : 'bg-purple-100'; ?> rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas <?php echo $index % 2 == 0 ? 'fa-calculator text-blue-600' : 'fa-atom text-purple-600'; ?>"></i>
                                        </div>
                                        <h3 class="font-semibold text-navy"><?php echo $course_name; ?></h3>
                                    </div>
                                    <button class="text-gold toggle-chapters" data-target="chapters-<?php echo $index; ?>">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </div>
                                <p class="text-gray-600 text-sm mb-3"><?php echo $index % 2 == 0 ? 'Mathematics curriculum covering all Grade 12 topics' : 'Physics and Chemistry for matric preparation'; ?></p>
                                
                                <!-- Chapters List -->
                                <div id="chapters-<?php echo $index; ?>" class="chapter-list">
                                    <h4 class="text-sm font-medium text-navy mb-2">Course Chapters:</h4>
                                    <ul class="text-sm text-gray-600 space-y-1 mb-3">
                                        <?php foreach ($chapters as $chapter): ?>
                                        <li class="flex items-center">
                                            <i class="fas fa-bookmark text-gold mr-2 text-xs"></i>
                                            <?php echo $chapter; ?>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500"><?php echo $course['lessons_remaining']; ?> lessons remaining</span>
                                    <div class="text-right">
                                        <span class="text-sm text-gold font-medium"><?php echo $course['progress']; ?>%</span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white rounded-xl shadow-lg p-6 dashboard-card">
                        <h2 class="text-xl font-bold text-navy mb-6">Recent Activity</h2>
                        <div class="space-y-4">
                            <div class="flex items-center p-3 border border-gray-200 rounded-lg">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-check-circle text-green-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium text-navy">Completed Mathematics Exercise</h3>
                                    <p class="text-sm text-gray-600">Chapter 3: Trigonometry - Score: 85%</p>
                                    <p class="text-xs text-gray-500">2 hours ago</p>
                                </div>
                            </div>
                            <div class="flex items-center p-3 border border-gray-200 rounded-lg">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-video text-blue-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium text-navy">Attended Live Lesson</h3>
                                    <p class="text-sm text-gray-600">Physical Sciences: Electricity & Magnetism</p>
                                    <p class="text-xs text-gray-500">Yesterday, 2:00 PM</p>
                                </div>
                            </div>
                            <div class="flex items-center p-3 border border-gray-200 rounded-lg">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-clipboard-check text-purple-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium text-navy">Mock Exam Completed</h3>
                                    <p class="text-sm text-gray-600">Mathematics Mock Paper 1 - Score: 78%</p>
                                    <p class="text-xs text-gray-500">2 days ago</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-8">
                    <!-- Quick Stats -->
                    <div class="bg-white rounded-xl shadow-lg p-6 dashboard-card">
                        <h2 class="text-xl font-bold text-navy mb-6">Quick Stats</h2>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Subjects Enrolled</span>
                                <span class="font-bold text-navy"><?php echo count($courses); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Average Progress</span>
                                <span class="font-bold text-gold"><?php echo empty($courses) ? 0 : round(array_sum(array_column($courses, 'progress')) / count($courses)); ?>%</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Exercises Due</span>
                                <span class="font-bold text-navy"><?php echo $assignments_due; ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Mock Exams Taken</span>
                                <span class="font-bold text-navy"><?php echo count(array_filter($mock_exams, function($exam) { return $exam['completed_at']; })); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Classes -->
                    <div class="bg-white rounded-xl shadow-lg p-6 dashboard-card">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-bold text-navy">Upcoming Classes</h2>
                            <a href="child-schedule.php" class="text-gold hover:underline">Full Schedule</a>
                        </div>
                        <div class="space-y-4">
                            <?php if (empty($live_lessons)): ?>
                            <p class="text-sm text-gray-600">No upcoming classes scheduled.</p>
                            <?php else: ?>
                            <?php foreach ($live_lessons as $lesson): ?>
                            <div class="p-3 border border-gray-200 rounded-lg">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-medium text-navy"><?php echo htmlspecialchars($lesson['lesson_name']); ?></h3>
                                    <span class="<?php echo $lesson['color']; ?> <?php echo $lesson['text_color']; ?> text-xs py-1 px-2 rounded-full"><?php echo $lesson['day']; ?></span>
                                </div>
                                <p class="text-sm text-gray-600"><i class="far fa-clock mr-2"></i><?php echo $lesson['time']; ?></p>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Exam Results -->
                    <div class="bg-white rounded-xl shadow-lg p-6 dashboard-card">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-bold text-navy">Recent Exam Results</h2>
                            <a href="exam-results.php" class="text-gold hover:underline">View All</a>
                        </div>
                        <div class="space-y-4">
                            <?php if (empty($mock_exams)): ?>
                            <p class="text-sm text-gray-600">No exam results available.</p>
                            <?php else: ?>
                            <?php foreach ($mock_exams as $exam): ?>
                            <div class="p-3 border border-gray-200 rounded-lg">
                                <h3 class="font-medium text-navy mb-2"><?php echo htmlspecialchars($exam['title']); ?></h3>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">
                                        <?php if ($exam['completed_at']): ?>
                                            Score: <?php echo number_format($exam['score'], 1); ?>%
                                        <?php else: ?>
                                            Not Attempted
                                        <?php endif; ?>
                                    </span>
                                    <?php if ($exam['completed_at']): ?>
                                        <span class="text-xs <?php echo $exam['score'] >= 80 ? 'text-green-600 bg-green-100' : ($exam['score'] >= 60 ? 'text-yellow-600 bg-yellow-100' : 'text-red-600 bg-red-100'); ?> py-1 px-2 rounded-full">
                                            <?php echo $exam['score'] >= 80 ? 'Excellent' : ($exam['score'] >= 60 ? 'Good' : 'Needs Work'); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
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
                            <div class="p-3 border border-gray-200 rounded-lg">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h3 class="font-medium text-navy"><?php echo htmlspecialchars($paper['title']); ?></h3>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($paper['description']); ?></p>
                                    </div>
                                    <span class="text-xs text-gray-500"><?php echo htmlspecialchars($paper['year']); ?></span>
                                </div>
                                <div class="flex justify-between items-center mt-3">
                                    <a href="<?php echo htmlspecialchars($paper['file_link']); ?>" class="text-gold text-sm font-medium" target="_blank">
                                        <i class="far fa-eye mr-1"></i> Preview
                                    </a>
                                    <a href="<?php echo htmlspecialchars($paper['file_link']); ?>" class="bg-navy text-white text-sm py-1 px-3 rounded-lg hover:bg-opacity-90 transition" download>
                                        <i class="fas fa-download mr-1"></i> Download
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
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
        
        // Child selector toggle
        const childSelector = document.getElementById('childSelector');
        if (childSelector) {
            childSelector.addEventListener('click', () => {
                const childList = document.getElementById('childList');
                childList.classList.toggle('active');
                
                const icon = childSelector.querySelector('i');
                if (childList.classList.contains('active')) {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                } else {
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
            });
            
            // Child selection
            document.querySelectorAll('.child-option').forEach(option => {
                option.addEventListener('click', () => {
                    const childId = option.getAttribute('data-child-id');
                    const childName = option.getAttribute('data-child-name');
                    
                    // Update the child selector text
                    childSelector.querySelector('span').textContent = 'Viewing: ' + childName;
                    
                    // Close the dropdown
                    document.getElementById('childList').classList.remove('active');
                    childSelector.querySelector('i').classList.remove('fa-chevron-up');
                    childSelector.querySelector('i').classList.add('fa-chevron-down');
                    
                    // In a real application, you would reload the page with the new child ID
                    // or make an AJAX request to update the dashboard content
                    // For now, we'll just show an alert
                    alert('Switching to view ' + childName + "'s progress. In a real application, this would update the dashboard.");
                });
            });
        }
        
        // Toggle chapters visibility
        document.querySelectorAll('.toggle-chapters').forEach(button => {
            button.addEventListener('click', () => {
                const target = button.getAttribute('data-target');
                const chapters = document.getElementById(target);
                chapters.classList.toggle('active');
                
                const icon = button.querySelector('i');
                if (chapters.classList.contains('active')) {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                } else {
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
            });
        });
        
        // Animate progress bars
        document.querySelectorAll('.progress-bar').forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0';
            setTimeout(() => { bar.style.width = width; }, 500);
        });

        // Notification handling
        const notificationButton = document.getElementById('notificationButton');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const notificationList = document.getElementById('notificationList');
        const notificationDot = document.getElementById('notificationDot');

        // Toggle dropdown
        notificationButton.addEventListener('click', () => {
            notificationDropdown.classList.toggle('hidden');
            // Mark notifications as read when dropdown is opened
            if (!notificationDropdown.classList.contains('hidden')) {
                markNotificationsAsRead();
            }
        });

        // Fetch notifications
        function fetchNotifications() {
            fetch('notifications_api.php')
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error fetching notifications:', data.error);
                        return;
                    }
                    notificationList.innerHTML = '';
                    let unreadCount = 0;

                    if (data.length === 0) {
                        notificationList.innerHTML = '<p class="p-4 text-sm text-gray-600">No notifications</p>';
                    } else {
                        data.forEach(notification => {
                            if (!notification.is_read) unreadCount++;
                            const notificationItem = document.createElement('div');
                            notificationItem.className = 'p-4 hover:bg-gray-50 cursor-pointer';
                            notificationItem.innerHTML = `
                                <div class="flex items-start">
                                    <i class="fas fa-${getNotificationIcon(notification.type)} text-${getNotificationColor(notification.type)} mr-3 mt-1"></i>
                                    <div>
                                        <p class="text-sm text-gray-800">${notification.message}</p>
                                        <p class="text-xs text-gray-500">${new Date(notification.created_at).toLocaleString()}</p>
                                    </div>
                                </div>
                            `;
                            notificationList.appendChild(notificationItem);
                        });
                    }

                    // Update notification dot visibility
                    notificationDot.classList.toggle('hidden', unreadCount === 0);
                })
                .catch(error => {
                    console.error('Error fetching notifications:', error);
                });
        }

        // Map notification types to icons
        function getNotificationIcon(type) {
            const icons = {
                general: 'bell',
                assignment: 'file-alt',
                live_lesson: 'video',
                exam: 'clipboard-check'
            };
            return icons[type] || 'bell';
        }

        // Map notification types to colors
        function getNotificationColor(type) {
            const colors = {
                general: 'gray-600',
                assignment: 'blue-600',
                live_lesson: 'green-600',
                exam: 'purple-600'
            };
            return colors[type] || 'gray-600';
        }

        // Mark notifications as read
        function markNotificationsAsRead() {
            fetch('mark_notifications_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            })
                .then(() => {
                    notificationDot.classList.add('hidden');
                })
                .catch(error => {
                    console.error('Error marking notifications as read:', error);
                });
        }

        // Poll for notifications every 30 seconds
        fetchNotifications();
        setInterval(fetchNotifications, 30000);
    </script>
</body>
</html>