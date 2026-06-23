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

// If no children linked, redirect or show message
if (empty($children)) {
    $no_children = true;
    $courses = [];
    $child_name = '';
} else {
    $no_children = false;
    // For simplicity, we'll focus on the first child. In a real system, you'd want parent to select which child
    $selected_child = $children[0];
    $child_id = $selected_child['id'];
    $child_name = htmlspecialchars($selected_child['username']);

    // Fetch child's enrolled courses with detailed information
    $stmt = $pdo->prepare("
     SELECT e.course_id, e.course_name, e.progress, e.lessons_remaining, e.enrollment_date,
         c.description,
               0 as completed_exercises,
         COUNT(a.id) as total_exercises
        FROM enrollments e 
        LEFT JOIN courses c ON e.course_id = c.id 
    LEFT JOIN exercises a ON c.id = a.course_id
        WHERE e.user_id = :child_id 
    GROUP BY e.course_id, e.course_name, e.progress, e.lessons_remaining, e.enrollment_date, 
         c.description
        ORDER BY e.enrollment_date DESC
    ");
    $stmt->execute(['child_id' => $child_id]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch recent activity for each course
    foreach ($courses as &$course) {
        $stmt = $pdo->prepare("
            SELECT activity_type, description, created_at 
            FROM student_activities 
            WHERE user_id = :child_id AND course_id = :course_id 
            ORDER BY created_at DESC 
            LIMIT 3
        ");
        $stmt->execute(['child_id' => $child_id, 'course_id' => $course['course_id']]);
        $course['recent_activities'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Define subjects with their chapters and descriptions
$subject_details = [
    'Mathematics' => [
        'icon' => 'fa-calculator',
        'color' => 'blue',
        'description' => 'Comprehensive mathematics curriculum covering all Grade 12 topics including algebra, calculus, trigonometry, and more.',
        'chapters' => [
            'Chapter 1: Algebra and Equations' => ['Linear equations', 'Quadratic equations', 'Simultaneous equations', 'Word problems'],
            'Chapter 2: Functions and Graphs' => ['Linear functions', 'Quadratic functions', 'Exponential functions', 'Graph interpretations'],
            'Chapter 3: Trigonometry' => ['Basic trigonometry', 'Compound angles', 'Identities', 'Applications'],
            'Chapter 4: Calculus' => ['Limits', 'Differentiation', 'Integration', 'Applications'],
            'Chapter 5: Statistics and Probability' => ['Data handling', 'Probability', 'Normal distribution', 'Correlation'],
            'Chapter 6: Euclidean Geometry' => ['Triangles', 'Quadrilaterals', 'Circles', 'Proofs'],
            'Chapter 7: Analytical Geometry' => ['Coordinate geometry', 'Distance formula', 'Midpoint', 'Gradients'],
            'Chapter 8: Financial Mathematics' => ['Simple interest', 'Compound interest', 'Annuities', 'Loans']
        ]
    ],
    'Physical Sciences' => [
        'icon' => 'fa-atom',
        'color' => 'purple',
        'description' => 'Physics and Chemistry curriculum designed for Grade 12 students preparing for final examinations.',
        'chapters' => [
            'Chapter 1: Mechanics' => ['Motion in one dimension', 'Motion in two dimensions', 'Forces', 'Energy'],
            'Chapter 2: Waves, Sound and Light' => ['Wave motion', 'Sound waves', 'Light waves', 'Electromagnetic spectrum'],
            'Chapter 3: Electricity and Magnetism' => ['Electric circuits', 'Electromagnetic induction', 'AC/DC current', 'Motors and generators'],
            'Chapter 4: Matter and Materials' => ['Atomic structure', 'Periodic table', 'Chemical bonding', 'Properties of materials'],
            'Chapter 5: Chemical Change' => ['Physical and chemical change', 'Representing change', 'Energy changes', 'Types of reactions'],
            'Chapter 6: Chemical Systems' => ['Exploiting the lithosphere', 'The chemical industry', 'Fertilizers', 'The atmosphere'],
            'Chapter 7: Reactions and Rates' => ['Reaction rates', 'Collision theory', 'Catalysts', 'Chemical equilibrium'],
            'Chapter 8: Organic Chemistry' => ['Organic molecules', 'IUPAC naming', 'Functional groups', 'Reactions of organic compounds']
        ]
    ],
    'English' => [
        'icon' => 'fa-book',
        'color' => 'red',
        'description' => 'English curriculum focusing on language, literature, comprehension, and writing skills.',
        'chapters' => [
            'Chapter 1: Language Structures' => ['Grammar', 'Vocabulary', 'Sentence construction'],
            'Chapter 2: Comprehension' => ['Reading skills', 'Understanding texts', 'Critical analysis'],
            'Chapter 3: Literature' => ['Poetry', 'Drama', 'Prose', 'Literary devices'],
            'Chapter 4: Writing' => ['Essays', 'Transactional writing', 'Creative writing'],
            'Chapter 5: Listening and Speaking' => ['Oral presentations', 'Listening skills', 'Debate', 'Discussion']
        ]
    ],
    'Computational Application Technology' => [
        'icon' => 'fa-desktop',
        'color' => 'teal',
        'description' => 'CAT curriculum covering computer literacy, applications, and technology in society.',
        'chapters' => [
            'Chapter 1: Introduction to Computers' => ['Hardware', 'Software', 'Operating systems'],
            'Chapter 2: Word Processing' => ['Document creation', 'Formatting', 'Editing'],
            'Chapter 3: Spreadsheets' => ['Data entry', 'Formulas', 'Charts', 'Analysis'],
            'Chapter 4: Presentations' => ['Slides', 'Design', 'Delivery'],
            'Chapter 5: Internet and Email' => ['Web browsing', 'Online safety', 'Email communication'],
            'Chapter 6: Social and Ethical Issues' => ['Digital citizenship', 'Ethics', 'Cyberbullying', 'Copyright']
        ]
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child's Enrolled Subjects - NovaTech FET College</title>
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
        .course-card { transition: all 0.3s ease; }
        .course-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1); }
        .sidebar { transition: all 0.3s ease; }
        @media (max-width: 768px) {
            .sidebar { position: fixed; left: -300px; z-index: 1000; height: 100vh; }
            .sidebar.active { left: 0; }
            .overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 999; }
            .overlay.active { display: block; }
        }
        .progress-circle {
            transform: rotate(-90deg);
            transition: stroke-dashoffset 1s ease-in-out;
        }
        .chapter-item { transition: all 0.2s ease; }
        .chapter-item:hover { background-color: rgba(0, 0, 0, 0.02); }
        .activity-timeline { position: relative; }
        .activity-timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 20px;
            bottom: 0;
            width: 2px;
            background-color: #e5e5e5;
        }
        .stats-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .filter-tabs { border-bottom: 2px solid #e5e5e5; }
        .filter-tab.active { border-bottom-color: var(--gold); color: var(--gold); }
        .modal { backdrop-filter: blur(5px); }
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
                <a href="child-progress.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white">
                    <i class="fas fa-chart-line mr-3"></i><span>Child's Progress</span>
                </a>
                <a href="child-courses.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white border-b-2 border-gold">
                    <i class="fas fa-book-open mr-3"></i><span>Enrolled Subjects</span>
                </a>
                <a href="child-schedule.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white">
                    <i class="fas fa-calendar-alt mr-3"></i><span>Class Schedule</span>
                </a>
                <a href="exam-results.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white">
                    <i class="fas fa-clipboard-check mr-3"></i><span>Exam Results</span>
                </a>
                <a href="attendance.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white">
                    <i class="fas fa-user-check mr-3"></i><span>Attendance</span>
                </a>
                
                <a href="package-info.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white">
                    <i class="fas fa-box mr-3"></i><span>Package Details</span>
                </a>
                <a href="settings.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition text-white">
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
                    <h1 class="text-xl font-bold text-navy">Enrolled Subjects</h1>
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
                <p class="text-gray-600 mb-6">Your parent account is not currently linked to any student accounts. Please contact the school administrator to link your child's account.</p>
                <a href="#" class="bg-navy text-white font-bold py-2 px-6 rounded-lg hover:bg-opacity-90 transition">Contact Administrator</a>
            </div>
            <?php else: ?>

            <!-- Header Section -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <div class="flex flex-col md:flex-row items-start justify-between">
                    <div class="mb-4 md:mb-0">
                        <h2 class="text-2xl font-bold text-navy mb-2"><?php echo $child_name; ?>'s Enrolled Subjects</h2>
                        <p class="text-gray-600">
                            Currently enrolled in <?php echo count($courses); ?> subject<?php echo count($courses) != 1 ? 's' : ''; ?>. 
                            Track progress, view course materials, and monitor academic performance.
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <button id="summaryViewBtn" class="bg-gold text-navy font-bold py-2 px-4 rounded-lg hover:bg-yellow-500 transition">
                            <i class="fas fa-list mr-2"></i>Summary View
                        </button>
                        <button id="detailedViewBtn" class="bg-navy text-white font-bold py-2 px-4 rounded-lg hover:bg-opacity-90 transition">
                            <i class="fas fa-th mr-2"></i>Detailed View
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistics Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="stats-card text-white rounded-xl p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-book-open text-white"></i>
                        </div>
                        <div>
                            <p class="text-white text-opacity-80 text-sm">Total Subjects</p>
                            <p class="text-2xl font-bold"><?php echo count($courses); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-green-500 text-white rounded-xl p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-chart-line text-white"></i>
                        </div>
                        <div>
                            <p class="text-white text-opacity-80 text-sm">Average Progress</p>
                            <p class="text-2xl font-bold"><?php echo empty($courses) ? 0 : round(array_sum(array_column($courses, 'progress')) / count($courses)); ?>%</p>
                        </div>
                    </div>
                </div>
                <div class="bg-blue-500 text-white rounded-xl p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-tasks text-white"></i>
                        </div>
                        <div>
                            <p class="text-white text-opacity-80 text-sm">Exercises Due</p>
                            <p class="text-2xl font-bold"><?php echo array_sum(array_column($courses, 'lessons_remaining')); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-purple-500 text-white rounded-xl p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-graduation-cap text-white"></i>
                        </div>
                        <div>
                            <p class="text-white text-opacity-80 text-sm">Completion Rate</p>
                            <p class="text-2xl font-bold"><?php 
                                $total_exercises = array_sum(array_column($courses, 'total_exercises'));
                                $completed_exercises = array_sum(array_column($courses, 'completed_exercises'));
                                echo $total_exercises > 0 ? round(($completed_exercises / $total_exercises) * 100) : 0;
                            ?>%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="bg-white rounded-xl shadow-lg mb-8">
                <div class="filter-tabs px-6">
                    <div class="flex space-x-6">
                        <button class="filter-tab active py-4 px-2 text-gold font-medium border-b-2" data-filter="all">
                            All Subjects (<?php echo count($courses); ?>)
                        </button>
                        <button class="filter-tab py-4 px-2 text-gray-600 font-medium border-b-2 border-transparent hover:text-gold" data-filter="in-progress">
                            In Progress
                        </button>
                        <button class="filter-tab py-4 px-2 text-gray-600 font-medium border-b-2 border-transparent hover:text-gold" data-filter="completed">
                            Completed
                        </button>
                    </div>
                </div>
            </div>

            <!-- Course Cards Container -->
            <div id="courseContainer" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <?php foreach ($courses as $index => $course): 
                    $course_name = htmlspecialchars($course['course_name']);
                    $subject_info = isset($subject_details[$course_name]) ? $subject_details[$course_name] : [
                        'icon' => 'fa-book',
                        'color' => 'gray',
                        'description' => 'Course curriculum designed for comprehensive learning.',
                        'chapters' => []
                    ];
                    $progress = floatval($course['progress']);
                    $status_class = $progress >= 100 ? 'completed' : 'in-progress';
                ?>
                
                <div class="course-card bg-white rounded-xl shadow-lg overflow-hidden <?php echo $status_class; ?>" data-course="<?php echo $course['course_id']; ?>">
                    <!-- Course Header -->
                    <div class="bg-<?php echo $subject_info['color']; ?>-500 text-white p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-4">
                                    <i class="fas <?php echo $subject_info['icon']; ?> text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold"><?php echo $course_name; ?></h3>
                                    <p class="text-white text-opacity-80"><?php echo htmlspecialchars($course['instructor_name'] ?? 'NovaTech Faculty'); ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="w-16 h-16">
                                    <svg class="w-16 h-16 progress-circle" viewBox="0 0 42 42">
                                        <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="rgba(255,255,255,0.2)" stroke-width="2"/>
                                        <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="white" stroke-width="2" 
                                                stroke-dasharray="<?php echo $progress; ?> <?php echo 100 - $progress; ?>" stroke-dashoffset="0"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-bold mt-2"><?php echo $progress; ?>%</p>
                            </div>
                        </div>
                        <p class="text-white text-opacity-90 text-sm"><?php echo $subject_info['description']; ?></p>
                    </div>

                    <!-- Course Content -->
                    <div class="p-6">
                        <!-- Progress Details -->
                        <div class="mb-6">
                            <div class="flex justify-between text-sm text-gray-600 mb-2">
                                <span>Course Progress</span>
                                <span><?php echo intval($progress / 12.5); ?>/8 Chapters</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-<?php echo $subject_info['color']; ?>-500 h-2 rounded-full transition-all duration-1000" style="width: <?php echo $progress; ?>%"></div>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="grid grid-cols-3 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-<?php echo $subject_info['color']; ?>-600"><?php echo $course['total_lessons'] ?? 24; ?></p>
                                <p class="text-xs text-gray-600">Total Lessons</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-green-600"><?php echo $course['completed_exercises']; ?></p>
                                <p class="text-xs text-gray-600">Completed</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-orange-600"><?php echo $course['lessons_remaining']; ?></p>
                                <p class="text-xs text-gray-600">Remaining</p>
                            </div>
                        </div>

                        <!-- Chapter Breakdown -->
                        <div class="mb-6">
                            <button class="flex items-center justify-between w-full text-left font-semibold text-navy mb-3 toggle-chapters" data-target="chapters-<?php echo $index; ?>">
                                <span><i class="fas fa-list-ul mr-2"></i>Course Chapters</span>
                                <i class="fas fa-chevron-down transition-transform"></i>
                            </button>
                            <div id="chapters-<?php echo $index; ?>" class="chapter-content max-h-0 overflow-hidden transition-all duration-300">
                                <div class="space-y-2">
                                    <?php 
                                    $chapter_index = 0;
                                    foreach ($subject_info['chapters'] as $chapter_title => $topics): 
                                        $chapter_progress = min(100, max(0, ($progress - ($chapter_index * 12.5)) * 8));
                                        $is_completed = $chapter_progress >= 100;
                                        $is_current = $chapter_progress > 0 && $chapter_progress < 100;
                                    ?>
                                    <div class="chapter-item p-3 border border-gray-200 rounded-lg <?php echo $is_completed ? 'bg-green-50 border-green-200' : ($is_current ? 'bg-blue-50 border-blue-200' : ''); ?>">
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="font-medium text-sm <?php echo $is_completed ? 'text-green-700' : ($is_current ? 'text-blue-700' : 'text-gray-700'); ?>">
                                                <i class="fas <?php echo $is_completed ? 'fa-check-circle text-green-500' : ($is_current ? 'fa-play-circle text-blue-500' : 'fa-circle text-gray-400'); ?> mr-2"></i>
                                                <?php echo $chapter_title; ?>
                                            </h4>
                                            <span class="text-xs <?php echo $is_completed ? 'text-green-600' : ($is_current ? 'text-blue-600' : 'text-gray-500'); ?>">
                                                <?php echo number_format($chapter_progress, 0); ?>%
                                            </span>
                                        </div>
                                        <?php if (!empty($topics)): ?>
                                        <div class="text-xs text-gray-600 ml-6">
                                            <p class="mb-1">Topics: <?php echo implode(', ', array_slice($topics, 0, 2)); ?>
                                            <?php if (count($topics) > 2): ?> and <?php echo count($topics) - 2; ?> more<?php endif; ?></p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php 
                                    $chapter_index++;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="mb-6">
                            <h4 class="font-semibold text-navy mb-3"><i class="fas fa-clock mr-2"></i>Recent Activity</h4>
                            <?php if (!empty($course['recent_activities'])): ?>
                            <div class="activity-timeline space-y-3">
                                <?php foreach ($course['recent_activities'] as $activity): ?>
                                <div class="flex items-start ml-6 relative">
                                    <div class="w-3 h-3 bg-<?php echo $subject_info['color']; ?>-500 rounded-full absolute -left-7 top-2"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-navy"><?php echo htmlspecialchars($activity['description']); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <p class="text-sm text-gray-500">No recent activity recorded.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex space-x-3">
                            <button class="flex-1 bg-<?php echo $subject_info['color']; ?>-500 text-white py-2 px-4 rounded-lg hover:bg-<?php echo $subject_info['color']; ?>-600 transition text-sm font-medium view-details-btn" data-course="<?php echo $course['course_id']; ?>" data-name="<?php echo $course_name; ?>">
                                <i class="fas fa-eye mr-2"></i>View Details
                            </button>
                            <button class="flex-1 border border-<?php echo $subject_info['color']; ?>-500 text-<?php echo $subject_info['color']; ?>-500 py-2 px-4 rounded-lg hover:bg-<?php echo $subject_info['color']; ?>-50 transition text-sm font-medium view-resources-btn" data-course="<?php echo $course['course_id']; ?>">
                                <i class="fas fa-book mr-2"></i>Resources
                            </button>
                        </div>
                    </div>

                    <!-- Course Status Badge -->
                    <div class="absolute top-4 right-4">
                        <?php if ($progress >= 100): ?>
                        <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-medium">Completed</span>
                        <?php elseif ($progress >= 80): ?>
                        <span class="bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-medium">Almost Done</span>
                        <?php elseif ($progress > 0): ?>
                        <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-xs font-medium">In Progress</span>
                        <?php else: ?>
                        <span class="bg-gray-500 text-white px-3 py-1 rounded-full text-xs font-medium">Not Started</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($courses)): ?>
            <!-- Empty State -->
            <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                <i class="fas fa-book-open text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-bold text-navy mb-2">No Subjects Enrolled</h3>
                <p class="text-gray-600 mb-6"><?php echo $child_name; ?> is not currently enrolled in any subjects. Contact the school administrator for enrollment assistance.</p>
                <a href="#" class="bg-navy text-white font-bold py-2 px-6 rounded-lg hover:bg-opacity-90 transition">Contact Administrator</a>
            </div>
            <?php endif; ?>

            <?php endif; ?>
        </main>
    </div>

    <!-- Course Details Modal -->
    <div id="courseModal" class="modal fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-4xl w-full max-h-90vh overflow-y-auto">
            <div class="p-6 border-b">
                <div class="flex items-center justify-between">
                    <h2 id="modalTitle" class="text-2xl font-bold text-navy">Course Details</h2>
                    <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <div id="modalContent" class="p-6">
                <!-- Modal content will be loaded here -->
            </div>
        </div>
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

        // Filter functionality
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                // Update active tab
                document.querySelectorAll('.filter-tab').forEach(t => {
                    t.classList.remove('active', 'text-gold', 'border-gold');
                    t.classList.add('text-gray-600', 'border-transparent');
                });
                tab.classList.add('active', 'text-gold', 'border-gold');
                tab.classList.remove('text-gray-600', 'border-transparent');

                // Filter courses
                const filter = tab.getAttribute('data-filter');
                const courseCards = document.querySelectorAll('.course-card');
                
                courseCards.forEach(card => {
                    const progress = parseFloat(card.querySelector('.progress-circle circle:last-child').getAttribute('stroke-dasharray').split(' ')[0]);
                    let show = false;

                    switch(filter) {
                        case 'all':
                            show = true;
                            break;
                        case 'in-progress':
                            show = progress > 0 && progress < 100;
                            break;
                        case 'completed':
                            show = progress >= 100;
                            break;
                    }

                    card.style.display = show ? 'block' : 'none';
                });
            });
        });

        // Toggle chapters
        document.querySelectorAll('.toggle-chapters').forEach(button => {
            button.addEventListener('click', () => {
                const target = button.getAttribute('data-target');
                const chapters = document.getElementById(target);
                const icon = button.querySelector('i:last-child');
                
                if (chapters.style.maxHeight === '0px' || chapters.style.maxHeight === '') {
                    chapters.style.maxHeight = chapters.scrollHeight + 'px';
                    icon.style.transform = 'rotate(180deg)';
                } else {
                    chapters.style.maxHeight = '0px';
                    icon.style.transform = 'rotate(0deg)';
                }
            });
        });

        // View modes
        document.getElementById('summaryViewBtn').addEventListener('click', () => {
            document.getElementById('courseContainer').className = 'grid grid-cols-1 lg:grid-cols-3 gap-6';
            document.querySelectorAll('.chapter-content, .activity-timeline').forEach(el => {
                el.style.display = 'none';
            });
        });

        document.getElementById('detailedViewBtn').addEventListener('click', () => {
            document.getElementById('courseContainer').className = 'grid grid-cols-1 lg:grid-cols-2 gap-8';
            document.querySelectorAll('.chapter-content, .activity-timeline').forEach(el => {
                el.style.display = 'block';
            });
        });

        // Course details modal
        const courseModal = document.getElementById('courseModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalContent = document.getElementById('modalContent');
        const closeModal = document.getElementById('closeModal');

        document.querySelectorAll('.view-details-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const courseName = btn.getAttribute('data-name');
                const courseId = btn.getAttribute('data-course');
                
                modalTitle.textContent = courseName + ' - Course Details';
                modalContent.innerHTML = `
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold text-navy mb-4">Course Information</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Course Duration:</span>
                                        <span class="font-medium">16 Weeks</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Total Lessons:</span>
                                        <span class="font-medium">24 Lessons</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Instructor:</span>
                                        <span class="font-medium">NovaTech Faculty</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Class Schedule:</span>
                                        <span class="font-medium">Mon, Wed, Fri</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-navy mb-4">Performance Metrics</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Average Score:</span>
                                        <span class="font-medium text-green-600">78%</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Attendance Rate:</span>
                                        <span class="font-medium text-blue-600">95%</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Exercises Submitted:</span>
                                        <span class="font-medium">12/15</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Last Activity:</span>
                                        <span class="font-medium">2 hours ago</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-navy mb-4">Upcoming Deadlines</h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-navy">Chapter 5 Exercise</p>
                                        <p class="text-sm text-gray-600">Statistics and Probability</p>
                                    </div>
                                    <span class="text-sm text-yellow-600 font-medium">Due in 3 days</span>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-navy">Mock Exam 2</p>
                                        <p class="text-sm text-gray-600">Comprehensive Assessment</p>
                                    </div>
                                    <span class="text-sm text-red-600 font-medium">Due tomorrow</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                courseModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            });
        });

        closeModal.addEventListener('click', () => {
            courseModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        });

        // Close modal when clicking outside
        courseModal.addEventListener('click', (e) => {
            if (e.target === courseModal) {
                courseModal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        });

        // Resources button functionality
        document.querySelectorAll('.view-resources-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                // In a real application, this would navigate to a resources page
                alert('Resources functionality would open course materials, past papers, and study guides.');
            });
        });

        // Animate progress circles on page load
        setTimeout(() => {
            document.querySelectorAll('.progress-circle circle:last-child').forEach(circle => {
                const dashArray = circle.getAttribute('stroke-dasharray');
                circle.style.strokeDasharray = '0 100';
                setTimeout(() => {
                    circle.style.strokeDasharray = dashArray;
                }, 100);
            });
        }, 500);
    </script>
</body>
</html>