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

// If no children linked, set flag
if (empty($children)) {
    $no_children = true;
    $child_name = '';
} else {
    $no_children = false;
    // For simplicity, focus on the first child
    $selected_child = $children[0];
    $child_id = $selected_child['id'];
    $child_name = htmlspecialchars($selected_child['username']);

    // Get current week dates
    $current_date = date('Y-m-d');
    $current_week_start = date('Y-m-d', strtotime('monday this week'));
    $current_week_end = date('Y-m-d', strtotime('sunday this week'));

    // Fetch child's enrolled courses
    $stmt = $pdo->prepare("SELECT course_id, course_name FROM enrollments WHERE user_id = :child_id");
    $stmt->execute(['child_id' => $child_id]);
    $enrolled_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $enrolled_course_ids = array_column($enrolled_courses, 'course_id');
    // Safety check for empty enrolled courses
    if (empty($enrolled_course_ids)) {
        $enrolled_course_ids = [0]; // Use 0 to avoid SQL error
    }
    $placeholders = implode(',', array_fill(0, count($enrolled_course_ids), '?'));
    // Fetch regular class schedule
    $stmt = $pdo->prepare("
        SELECT cs.*, c.description as course_description
        FROM class_timetable cs
        LEFT JOIN courses c ON cs.course_id = c.id
        WHERE cs.course_id IN ($placeholders)
        ORDER BY 
            CASE cs.day_of_week 
                WHEN 'Monday' THEN 1 
                WHEN 'Tuesday' THEN 2 
                WHEN 'Wednesday' THEN 3 
                WHEN 'Thursday' THEN 4 
                WHEN 'Friday' THEN 5 
                WHEN 'Saturday' THEN 6 
                WHEN 'Sunday' THEN 7 
            END, cs.start_time
    ");
    $stmt->execute($enrolled_course_ids);
    $regular_schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch live lessons for next 30 days
    $enrolled_course_ids = array_column($enrolled_courses, 'course_id');
    if (empty($enrolled_course_ids)) {
        $enrolled_course_ids = [0];
    }
    $placeholders = implode(',', array_fill(0, count($enrolled_course_ids), '?'));
    $stmt = $pdo->prepare("
        SELECT ll.*, 
               CASE 
                   WHEN ll.date = CURDATE() THEN 'Today'
                   WHEN ll.date = DATE_ADD(CURDATE(), INTERVAL 1 DAY) THEN 'Tomorrow'
                   ELSE DATE_FORMAT(ll.date, '%M %d, %Y')
               END as formatted_date,
               CASE 
                   WHEN ll.date = CURDATE() THEN 'today'
                   WHEN ll.date = DATE_ADD(CURDATE(), INTERVAL 1 DAY) THEN 'tomorrow'
                   WHEN ll.date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'this-week'
                   ELSE 'later'
               END as time_category
        FROM live_lessons ll
        WHERE ll.course_id IN ($placeholders)
          AND ll.date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ORDER BY ll.date ASC, ll.start_time ASC
    ");
    $stmt->execute($enrolled_course_ids);
    $live_lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch exam schedules
    $stmt = $pdo->prepare("
        SELECT es.*
        FROM exam_schedules es
        LEFT JOIN courses c ON es.course_id = c.id
        WHERE es.course_id IN ($placeholders)
          AND es.exam_date >= CURDATE()
        ORDER BY es.exam_date ASC, es.start_time ASC
    ");
    $stmt->execute($enrolled_course_ids);
    $exam_schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format time for display
    foreach ($live_lessons as &$lesson) {
        $lesson['formatted_time'] = date('g:i A', strtotime($lesson['start_time'])) . ' - ' . date('g:i A', strtotime($lesson['end_time']));
        $lesson['day_name'] = date('l', strtotime($lesson['date']));
    }
    foreach ($exam_schedules as &$exam) {
        $exam['formatted_time'] = date('g:i A', strtotime($exam['start_time'])) . ' - ' . date('g:i A', strtotime($exam['end_time']));
        $exam['formatted_date'] = date('M d, Y', strtotime($exam['exam_date']));
        $exam['day_name'] = date('l', strtotime($exam['exam_date']));
    }
}

// Define subject colors for consistent theming
$subject_colors = [
    'Mathematics' => ['bg' => 'bg-blue-500', 'light' => 'bg-blue-100', 'text' => 'text-blue-700', 'border' => 'border-blue-200'],
    'Physical Sciences' => ['bg' => 'bg-purple-500', 'light' => 'bg-purple-100', 'text' => 'text-purple-700', 'border' => 'border-purple-200'],
    'Life Sciences' => ['bg' => 'bg-green-500', 'light' => 'bg-green-100', 'text' => 'text-green-700', 'border' => 'border-green-200'],
    'English' => ['bg' => 'bg-red-500', 'light' => 'bg-red-100', 'text' => 'text-red-700', 'border' => 'border-red-200'],
    'Afrikaans' => ['bg' => 'bg-orange-500', 'light' => 'bg-orange-100', 'text' => 'text-orange-700', 'border' => 'border-orange-200']
];

function getSubjectColor($subject, $type = 'bg') {
    global $subject_colors;
    return isset($subject_colors[$subject]) ? $subject_colors[$subject][$type] : $subject_colors['Mathematics'][$type];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Schedule - NovaTech FET College</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        .schedule-card { transition: all 0.3s ease; }
        .schedule-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1); }
        .sidebar { transition: all 0.3s ease; }
        @media (max-width: 768px) {
            .sidebar { position: fixed; left: -300px; z-index: 1000; height: 100vh; }
            .sidebar.active { left: 0; }
            .overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 999; }
            .overlay.active { display: block; }
        }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; }
        .calendar-day { min-height: 120px; background: white; position: relative; }
        .calendar-event { font-size: 0.75rem; padding: 2px 4px; margin-bottom: 1px; border-radius: 3px; cursor: pointer; }
        .time-slot { border-bottom: 1px solid #e5e5e5; }
        .time-slot:last-child { border-bottom: none; }
        .current-time { position: relative; }
        .current-time::after { content: ''; position: absolute; left: 0; right: 0; top: 50%; height: 2px; background: #ef4444; z-index: 10; }
        .view-tab { transition: all 0.2s ease; }
        .view-tab.active { background-color: var(--gold); color: var(--navy); }
        .lesson-card { border-left: 4px solid; }
        .pulse-dot { animation: pulse 2s infinite; }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .upcoming-badge { position: absolute; top: -8px; right: -8px; }
        .schedule-timeline { position: relative; }
        .schedule-timeline::before { content: ''; position: absolute; left: 24px; top: 0; bottom: 0; width: 2px; background: #e5e5e5; }
        .timeline-item { position: relative; }
        .timeline-dot { position: absolute; left: 16px; top: 20px; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; z-index: 1; }
        .child-selector { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; }
        .child-selector.active { max-height: 200px; }
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
                <a href="parent_dashboard.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white">
                    <i class="fas fa-home mr-3"></i><span>Dashboard</span>
                </a>
                <a href="child_progress.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white">
                    <i class="fas fa-chart-line mr-3"></i><span>Child's Progress</span>
                </a>
                <a href="child-courses.php" class="flex items-center p-3 rounded-lg hover:bg-white hover-bg-opacity-10 transition text-white">
                    <i class="fas fa-book-open mr-3"></i><span>Enrolled Subjects</span>
                </a>
                <a href="child-schedule.php" class="flex items-center p-3 rounded-lg hover:bg-white hover-bg-opacity-10 transition text-white border-b-2 border-gold">
                    <i class="fas fa-calendar-alt mr-3"></i><span>Class Schedule</span>
                </a>
                <a href="exam-results.php" class="flex items-center p-3 rounded-lg hover:bg-white hover-bg-opacity-10 transition text-white">
                    <i class="fas fa-clipboard-check mr-3"></i><span>Exam Results</span>
                </a>
                <a href="attendance.php" class="flex items-center p-3 rounded-lg hover:bg-white hover-bg-opacity-10 transition text-white">
                    <i class="fas fa-user-check mr-3"></i><span>Attendance</span>
                </a>
                
                <a href="package-info.php" class="flex items-center p-3 rounded-lg hover:bg-white hover-bg-opacity-10 transition text-white">
                    <i class="fas fa-box mr-3"></i><span>Package Details</span>
                </a>
                <a href="settings.php" class="flex items-center p-3 rounded-lg hover:bg-white hover-bg-opacity-10 transition text-white">
                    <i class="fas fa-cog mr-3"></i><span>Settings</span>
                </a>
                <a href="logout.php" class="flex items-center p-3 rounded-lg hover:bg-white hover-bg-opacity-10 transition text-white">
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
                    <h1 class="text-xl font-bold text-navy">Class Schedule</h1>
                    <div class="flex items-center space-x-4">
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

        <!-- Page Content -->
        <main class="container mx-auto px-6 py-8">
            <?php if ($no_children): ?>
            <!-- No Children Linked -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-8 text-center">
                <i class="fas fa-user-plus text-6xl text-gold mb-4"></i>
                <h2 class="text-2xl font-bold text-navy mb-4">No Children Linked</h2>
                <p class="text-gray-600 mb-6">Your parent account is not currently linked to any student accounts.</p>
                <a href="#" class="bg-navy text-white font-bold py-2 px-6 rounded-lg hover:bg-opacity-90 transition">Contact Administrator</a>
            </div>
            <?php else: ?>
            <!-- Header Section with Quick Stats -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <div class="flex flex-col lg:flex-row items-start justify-between mb-6">
                    <div class="mb-4 lg:mb-0">
                        <h2 class="text-2xl font-bold text-navy mb-2"><?php echo $child_name; ?>'s Class Schedule</h2>
                        <p class="text-gray-600">
                            Track all classes, live sessions, and upcoming exams. 
                            <?php echo count($live_lessons); ?> upcoming sessions this month.
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <button id="weekViewBtn" class="view-tab active bg-gold text-navy font-bold py-2 px-4 rounded-lg transition">
                            <i class="fas fa-calendar-week mr-2"></i>Week View
                        </button>
                        <button id="listViewBtn" class="view-tab bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-lg hover:bg-gray-300 transition">
                            <i class="fas fa-list mr-2"></i>List View
                        </button>
                    </div>
                </div>
                <!-- Quick Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-calendar-day text-blue-500 text-2xl mr-3"></i>
                            <div>
                                <p class="text-blue-700 font-bold text-xl"><?php echo count(array_filter($live_lessons, function($l) { return $l['time_category'] == 'today'; })); ?></p>
                                <p class="text-blue-600 text-sm">Today's Classes</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-calendar-plus text-green-500 text-2xl mr-3"></i>
                            <div>
                                <p class="text-green-700 font-bold text-xl"><?php echo count(array_filter($live_lessons, function($l) { return $l['time_category'] == 'this-week'; })); ?></p>
                                <p class="text-green-600 text-sm">This Week</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-chalkboard-teacher text-purple-500 text-2xl mr-3"></i>
                            <div>
                                <p class="text-purple-700 font-bold text-xl"><?php echo count($regular_schedule); ?></p>
                                <p class="text-purple-600 text-sm">Regular Classes</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-clipboard-check text-red-500 text-2xl mr-3"></i>
                            <div>
                                <p class="text-red-700 font-bold text-xl"><?php echo count($exam_schedules); ?></p>
                                <p class="text-red-600 text-sm">Upcoming Exams</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Week View Content -->
            <div id="weekViewContent" class="space-y-8">
                <!-- Current Week Schedule -->
                <div class="bg-white rounded-xl shadow-lg">
                    <div class="p-6 border-b">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold text-navy">This Week's Schedule</h3>
                            <div class="flex items-center space-x-2">
                                <button id="prevWeek" class="text-gray-400 hover:text-navy transition">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <span id="currentWeekDisplay" class="text-sm font-medium text-gray-600">
                                    <?php echo date('M d', strtotime($current_week_start)) . ' - ' . date('M d, Y', strtotime($current_week_end)); ?>
                                </span>
                                <button id="nextWeek" class="text-gray-400 hover:text-navy transition">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Weekly Calendar Grid -->
                    <div class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-7 gap-4">
                            <?php 
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            $current_day = date('l');
                            foreach ($days as $day): 
                                $day_date = date('Y-m-d', strtotime($day . ' this week'));
                                $is_today = ($day == $current_day);
                            ?>
                            <div class="border rounded-lg min-h-60 <?php echo $is_today ? 'border-gold bg-gold bg-opacity-5' : 'border-gray-200'; ?>">
                                <div class="p-3 border-b <?php echo $is_today ? 'bg-gold bg-opacity-10' : 'bg-gray-50'; ?>">
                                    <h4 class="font-semibold text-navy"><?php echo $day; ?></h4>
                                    <p class="text-sm text-gray-600"><?php echo date('M d', strtotime($day_date)); ?></p>
                                    <?php if ($is_today): ?>
                                    <span class="inline-block mt-1 bg-gold text-navy text-xs px-2 py-1 rounded-full font-medium">Today</span>
                                    <?php endif; ?>
                                </div>
                                <div class="p-3 space-y-2">
                                    <!-- Regular Classes -->
                                    <?php foreach ($regular_schedule as $class): 
                                        if ($class['day_of_week'] == $day): ?>
                                    <div class="lesson-card border-l-4 <?php echo getSubjectColor($class['course_name'], 'border'); ?> bg-white rounded-r p-2 shadow-sm">
                                        <h5 class="font-medium text-sm <?php echo getSubjectColor($class['course_name'], 'text'); ?>"><?php echo htmlspecialchars($class['course_name']); ?></h5>
                                        <p class="text-xs text-gray-600"><?php echo date('g:i A', strtotime($class['start_time'])) . ' - ' . date('g:i A', strtotime($class['end_time'])); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($class['classroom'] ?? 'Online'); ?></p>
                                    </div>
                                    <?php endif; endforeach; ?>
                                    <!-- Live Lessons -->
                                    <?php foreach ($live_lessons as $lesson): 
                                        if ($lesson['day_name'] == $day): ?>
                                    <div class="lesson-card border-l-4 border-red-400 bg-red-50 rounded-r p-2">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <h5 class="font-medium text-sm text-red-700"><?php echo htmlspecialchars($lesson['lesson_name']); ?></h5>
                                                <p class="text-xs text-red-600"><?php echo $lesson['formatted_time']; ?></p>
                                                <p class="text-xs text-red-500">Live Session</p>
                                            </div>
                                            <?php if ($lesson['time_category'] == 'today'): ?>
                                            <span class="pulse-dot w-2 h-2 bg-red-500 rounded-full"></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endif; endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Live Sessions -->
                <?php if (!empty($live_lessons)): ?>
                <div class="bg-white rounded-xl shadow-lg">
                    <div class="p-6 border-b">
                        <h3 class="text-xl font-bold text-navy">Upcoming Live Sessions</h3>
                        <p class="text-gray-600 mt-1">Special live classes and interactive sessions</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach (array_slice($live_lessons, 0, 6) as $lesson): ?>
                            <div class="schedule-card border border-gray-200 rounded-lg p-4 relative hover:shadow-lg transition-all">
                                <?php if ($lesson['time_category'] == 'today'): ?>
                                <div class="upcoming-badge">
                                    <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full font-medium">Live Today</span>
                                </div>
                                <?php elseif ($lesson['time_category'] == 'tomorrow'): ?>
                                <div class="upcoming-badge">
                                    <span class="bg-yellow-500 text-white text-xs px-2 py-1 rounded-full font-medium">Tomorrow</span>
                                </div>
                                <?php endif; ?>
                                <div class="<?php echo getSubjectColor($lesson['course_name'], 'light'); ?> p-3 rounded-lg mb-3">
                                    <h4 class="font-bold <?php echo getSubjectColor($lesson['course_name'], 'text'); ?>"><?php echo htmlspecialchars($lesson['lesson_name']); ?></h4>
                                    <p class="text-sm <?php echo getSubjectColor($lesson['course_name'], 'text'); ?> opacity-75"><?php echo htmlspecialchars($lesson['course_name']); ?></p>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-calendar-day mr-2 text-gray-400"></i>
                                        <span><?php echo $lesson['formatted_date']; ?></span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-clock mr-2 text-gray-400"></i>
                                        <span><?php echo $lesson['formatted_time']; ?></span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-video mr-2 text-gray-400"></i>
                                        <span><?php echo htmlspecialchars($lesson['platform'] ?? 'Microsoft Teams'); ?></span>
                                    </div>
                                    <?php if ($lesson['time_category'] == 'today'): ?>
                                    <a href="<?php echo htmlspecialchars($lesson['link']); ?>" target="_blank" class="mt-3 w-full bg-red-500 text-white text-center py-2 rounded-lg hover:bg-red-600 transition block">
                                        <i class="fas fa-play-circle mr-2"></i>Join Session
                                    </a>
                                    <?php else: ?>
                                    <div class="mt-3 text-center">
                                        <button class="text-sm text-blue-500 hover:text-blue-700 add-to-calendar" data-lesson='<?php echo json_encode($lesson); ?>'>
                                            <i class="far fa-calendar-plus mr-1"></i>Add to Calendar
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Upcoming Exams -->
                <?php if (!empty($exam_schedules)): ?>
                <div class="bg-white rounded-xl shadow-lg">
                    <div class="p-6 border-b">
                        <h3 class="text-xl font-bold text-navy">Upcoming Exams</h3>
                        <p class="text-gray-600 mt-1">Scheduled tests and examination dates</p>
                    </div>
                    <div class="p-6">
                        <div class="schedule-timeline space-y-6">
                            <?php foreach ($exam_schedules as $exam): ?>
                            <div class="timeline-item pl-10">
                                <div class="timeline-dot <?php echo getSubjectColor($exam['course_name'], 'bg'); ?>"></div>
                                <div class="schedule-card border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-all">
                                    <div class="flex flex-col md:flex-row md:items-center justify-between mb-3">
                                        <h4 class="font-bold text-lg <?php echo getSubjectColor($exam['course_name'], 'text'); ?>">
                                            <?php echo htmlspecialchars($exam['exam_title']); ?>
                                        </h4>
                                        <span class="text-sm font-medium <?php 
                                            $days_until = floor((strtotime($exam['exam_date']) - time()) / (60 * 60 * 24));
                                            if ($days_until <= 7) echo 'text-red-500';
                                            elseif ($days_until <= 14) echo 'text-yellow-500';
                                            else echo 'text-green-500';
                                        ?>">
                                            <?php echo $exam['formatted_date']; ?>
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                                        <div>
                                            <p class="text-sm text-gray-600"><i class="fas fa-book mr-2"></i><?php echo htmlspecialchars($exam['course_name']); ?></p>
                                            <p class="text-sm text-gray-600"><i class="fas fa-clock mr-2"></i><?php echo $exam['formatted_time']; ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-600"><i class="fas fa-map-marker-alt mr-2"></i><?php echo htmlspecialchars($exam['exam_location'] ?? 'Online'); ?></p>
                                            <p class="text-sm text-gray-600"><i class="fas fa-hourglass-half mr-2"></i>Duration: <?php echo htmlspecialchars($exam['duration'] ?? '2 hours'); ?></p>
                                        </div>
                                    </div>
                                    <?php if (!empty($exam['exam_notes'])): ?>
                                    <div class="bg-gray-50 p-3 rounded-lg mb-3">
                                        <p class="text-sm text-gray-700"><i class="fas fa-sticky-note mr-2"></i><?php echo htmlspecialchars($exam['exam_notes']); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-500"><?php echo $exam['day_name']; ?></span>
                                        <button class="text-sm text-blue-500 hover:text-blue-700 add-to-calendar" data-exam='<?php echo json_encode($exam); ?>'>
                                            <i class="far fa-calendar-plus mr-1"></i>Add to Calendar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- List View Content (Initially Hidden) -->
            <div id="listViewContent" class="hidden space-y-8">
                <!-- Regular Classes List -->
                <div class="bg-white rounded-xl shadow-lg">
                    <div class="p-6 border-b">
                        <h3 class="text-xl font-bold text-navy">Regular Class Schedule</h3>
                        <p class="text-gray-600 mt-1">Weekly recurring classes</p>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Day</th>
                                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Time</th>
                                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Subject</th>
                                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Instructor</th>
                                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Location</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($regular_schedule as $class): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($class['day_of_week']); ?></td>
                                        <td class="px-4 py-3 text-sm text-gray-600"><?php echo date('g:i A', strtotime($class['start_time'])) . ' - ' . date('g:i A', strtotime($class['end_time'])); ?></td>
                                        <td class="px-4 py-3 text-sm font-medium <?php echo getSubjectColor($class['course_name'], 'text'); ?>"><?php echo htmlspecialchars($class['course_name']); ?></td>
                                        <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($class['instructor_name'] ?? 'TBA'); ?></td>
                                        <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($class['classroom'] ?? 'Online'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- All Upcoming Events List -->
                <div class="bg-white rounded-xl shadow-lg">
                    <div class="p-6 border-b">
                        <h3 class="text-xl font-bold text-navy">All Upcoming Events</h3>
                        <p class="text-gray-600 mt-1">Live sessions and exams</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <?php 
                            // Combine live lessons and exam schedules
                            $all_events = array_merge($live_lessons, $exam_schedules);
                            // Sort by date
                            usort($all_events, function($a, $b) {
                                $dateA = isset($a['date']) ? $a['date'] : $a['exam_date'];
                                $dateB = isset($b['date']) ? $b['date'] : $b['exam_date'];
                                return strtotime($dateA) - strtotime($dateB);
                            });
                            foreach ($all_events as $event): 
                                $is_exam = isset($event['exam_title']);
                                $event_date = $is_exam ? $event['exam_date'] : $event['date'];
                                $event_time = $is_exam ? ($event['start_time'] . ' - ' . $event['end_time']) : ($event['start_time'] . ' - ' . $event['end_time']);
                                $days_until = floor((strtotime($event_date) - time()) / (60 * 60 * 24));
                            ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                <div class="flex flex-col md:flex-row md:items-center justify-between mb-2">
                                    <h4 class="font-bold text-lg <?php echo $is_exam ? 'text-red-600' : getSubjectColor($event['course_name'], 'text'); ?>">
                                        <?php echo htmlspecialchars($is_exam ? $event['exam_title'] : $event['lesson_name']); ?>
                                    </h4>
                                    <span class="text-sm font-medium <?php 
                                        if ($days_until <= 2) echo 'text-red-500';
                                        elseif ($days_until <= 7) echo 'text-yellow-500';
                                        else echo 'text-green-500';
                                    ?>">
                                        <?php echo date('M d, Y', strtotime($event_date)); ?>
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-3">
                                    <div>
                                        <p class="text-sm text-gray-600"><i class="fas fa-book mr-2"></i><?php echo htmlspecialchars($event['course_name']); ?></p>
                                        <p class="text-sm text-gray-600"><i class="fas fa-clock mr-2"></i><?php echo date('g:i A', strtotime($event_time)); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">
                                            <i class="fas <?php echo $is_exam ? 'fa-clipboard-list' : 'fa-video'; ?> mr-2"></i>
                                            <?php echo $is_exam ? 'Exam' : 'Live Session'; ?>
                                        </p>
                                        <?php if ($is_exam && !empty($event['exam_location'])): ?>
                                        <p class="text-sm text-gray-600"><i class="fas fa-map-marker-alt mr-2"></i><?php echo htmlspecialchars($event['exam_location']); ?></p>
                                        <?php elseif (!$is_exam && !empty($event['platform'])): ?>
                                        <p class="text-sm text-gray-600"><i class="fas fa-video mr-2"></i><?php echo htmlspecialchars($event['platform']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs px-2 py-1 rounded-full <?php 
                                        if ($is_exam) echo 'bg-red-100 text-red-800';
                                        else echo 'bg-blue-100 text-blue-800';
                                    ?>">
                                        <?php echo $is_exam ? 'Exam' : 'Live Session'; ?>
                                    </span>
                                    <button class="text-sm text-blue-500 hover:text-blue-700 add-to-calendar" data-event='<?php echo json_encode($event); ?>' data-type="<?php echo $is_exam ? 'exam' : 'lesson'; ?>">
                                        <i class="far fa-calendar-plus mr-1"></i>Add to Calendar
                                    </button>
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
                    // or make an AJAX request to update the schedule content
                    alert('Switching to view ' + childName + "'s schedule. In a real application, this would update the schedule.");
                });
            });
        }

        // View toggle functionality
        const weekViewBtn = document.getElementById('weekViewBtn');
        const listViewBtn = document.getElementById('listViewBtn');
        const weekViewContent = document.getElementById('weekViewContent');
        const listViewContent = document.getElementById('listViewContent');

        weekViewBtn.addEventListener('click', () => {
            weekViewBtn.classList.add('active', 'bg-gold', 'text-navy');
            weekViewBtn.classList.remove('bg-gray-200', 'text-gray-700');
            listViewBtn.classList.remove('active', 'bg-gold', 'text-navy');
            listViewBtn.classList.add('bg-gray-200', 'text-gray-700');
            weekViewContent.classList.remove('hidden');
            listViewContent.classList.add('hidden');
        });

        listViewBtn.addEventListener('click', () => {
            listViewBtn.classList.add('active', 'bg-gold', 'text-navy');
            listViewBtn.classList.remove('bg-gray-200', 'text-gray-700');
            weekViewBtn.classList.remove('active', 'bg-gold', 'text-navy');
            weekViewBtn.classList.add('bg-gray-200', 'text-gray-700');
            listViewContent.classList.remove('hidden');
            weekViewContent.classList.add('hidden');
        });

        // Week navigation
        const prevWeekBtn = document.getElementById('prevWeek');
        const nextWeekBtn = document.getElementById('nextWeek');
        const currentWeekDisplay = document.getElementById('currentWeekDisplay');
        let currentWeekStart = new Date('<?php echo $current_week_start; ?>');

        prevWeekBtn.addEventListener('click', () => {
            currentWeekStart.setDate(currentWeekStart.getDate() - 7);
            updateWeekDisplay();
            // In a real application, you would fetch the new week's data
            alert('Loading previous week. In a real application, this would fetch new schedule data.');
        });

        nextWeekBtn.addEventListener('click', () => {
            currentWeekStart.setDate(currentWeekStart.getDate() + 7);
            updateWeekDisplay();
            // In a real application, you would fetch the new week's data
            alert('Loading next week. In a real application, this would fetch new schedule data.');
        });

        function updateWeekDisplay() {
            const weekEnd = new Date(currentWeekStart);
            weekEnd.setDate(weekEnd.getDate() + 6);
            const options = { month: 'short', day: 'numeric' };
            const startStr = currentWeekStart.toLocaleDateString('en-US', options);
            const endStr = weekEnd.toLocaleDateString('en-US', options);
            currentWeekDisplay.textContent = `${startStr} - ${endStr}, ${weekEnd.getFullYear()}`;
        }

        // Add to calendar functionality
        document.querySelectorAll('.add-to-calendar').forEach(button => {
            button.addEventListener('click', function() {
                const eventData = this.getAttribute('data-event') || 
                                 this.getAttribute('data-lesson') || 
                                 this.getAttribute('data-exam');
                const event = JSON.parse(eventData);
                // In a real application, this would create a calendar event
                alert('Adding event to your calendar: ' + (event.lesson_name || event.exam_title));
            });
        });
    </script>
</body>
</html>
