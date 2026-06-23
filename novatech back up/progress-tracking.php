<?php
session_start();
include(__DIR__ . "/includes/db.php");

// Check if user is logged in and has 'student' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
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

// Fetch enrolled courses for progress
$stmt = $pdo->prepare("SELECT course_name, progress FROM enrollments WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch mock performance data (you can replace with real logs)
$performance_data = $courses; // Use enrollments progress for chart
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Tracking - NovaTech FET College</title>
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
        
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        .bg-navy {
            background-color: var(--navy);
        }
        
        .bg-gold {
            background-color: var(--gold);
        }
        
        .bg-beige {
            background-color: var(--beige);
        }
        
        .text-navy {
            color: var(--navy);
        }
        
        .text-gold {
            color: var(--gold);
        }
        
        .dashboard-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar {
            transition: all 0.3s ease;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -300px;
                z-index: 1000;
                height: 100vh;
            }
            
            .sidebar.active {
                left: 0;
            }
            
            .overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }
            
            .overlay.active {
                display: block;
            }
        }
        
        .progress-bar {
            transition: width 1s ease-in-out;
        }
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
                    <span class="ml-3 text-xl font-bold">NovaTech</span>
                </div>
                <button class="text-white md:hidden" id="closeSidebar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="mb-8 p-4 bg-white bg-opacity-10 rounded-lg">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gold rounded-full flex items-center justify-center mr-3">
                        <span class="text-navy font-bold"><?php echo $initials; ?></span>
                    </div>
                    <div>
                        <h3 class="font-semibold"><?php echo $username; ?></h3>
                        <p class="text-gold text-sm">Student</p>
                    </div>
                </div>
            </div>
            
  <nav class="space-y-2">
    <a href="student-dashboard.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition <?php echo basename($_SERVER['PHP_SELF']) == 'student-dashboard.php' ? 'border-b-2 border-gold' : ''; ?>">
        <i class="fas fa-home mr-3"></i>
        <span>Dashboard</span>
    </a>
    <a href="my-courses.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition <?php echo basename($_SERVER['PHP_SELF']) == 'my-courses.php' ? 'border-b-2 border-gold' : ''; ?>">
        <i class="fas fa-book-open mr-3"></i>
        <span>My Subjects</span>
    </a>
    <a href="past-papers.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition <?php echo basename($_SERVER['PHP_SELF']) == 'past-papers.php' ? 'border-b-2 border-gold' : ''; ?>">
        <i class="fas fa-file-alt mr-3"></i>
        <span>Past Papers</span>
    </a>
    <a href="live-lessons.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition <?php echo basename($_SERVER['PHP_SELF']) == 'live-lessons.php' ? 'border-b-2 border-gold' : ''; ?>">
        <i class="fas fa-video mr-3"></i>
        <span>Live Lessons</span>
    </a>
    <a href="progress-tracking.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition <?php echo basename($_SERVER['PHP_SELF']) == 'progress-tracking.php' ? 'border-b-2 border-gold' : ''; ?>">
        <i class="fas fa-chart-line mr-3"></i>
        <span>Progress Tracking</span>
    </a>
    <a href="study-groups.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition <?php echo basename($_SERVER['PHP_SELF']) == 'study-groups.php' ? 'border-b-2 border-gold' : ''; ?>">
        <i class="fas fa-users mr-3"></i>
        <span>Social Forums</span>
    </a>
    <a href="schedule.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition <?php echo basename($_SERVER['PHP_SELF']) == 'schedule.php' ? 'border-b-2 border-gold' : ''; ?>">
        <i class="fas fa-calendar-alt mr-3"></i>
        <span>Schedule</span>
    </a>
    <a href="settings.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'border-b-2 border-gold' : ''; ?>">
        <i class="fas fa-cog mr-3"></i>
        <span>Settings</span>
    </a>
    <a href="logout.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:bg-opacity-10 transition <?php echo basename($_SERVER['PHP_SELF']) == 'logout.php' ? 'border-b-2 border-gold' : ''; ?>">
        <i class="fas fa-sign-out-alt mr-3"></i>
        <span>Logout</span>
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
                    <button class="text-navy md:hidden" id="menuButton">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="text-xl font-bold text-navy">Progress Tracking</h1>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <button class="text-navy">
                                <i class="fas fa-bell"></i>
                                <span class="absolute top-[-5px] right-[-5px] w-3 h-3 bg-red-500 rounded-full"></span>
                            </button>
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

        <!-- Progress Tracking Content -->
        <main class="container mx-auto px-6 py-8">
            <div class="bg-white rounded-xl shadow-lg p-6 dashboard-card">
                <h2 class="text-xl font-bold text-navy mb-6">Progress Overview</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <?php foreach ($courses as $course): ?>
                    <div>
                        <h3 class="font-semibold text-navy mb-3"><?php echo htmlspecialchars($course['course_name']); ?></h3>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                            <div class="bg-gold h-2.5 rounded-full progress-bar" style="width: <?php echo $course['progress']; ?>%"></div>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span><?php echo $course['progress']; ?>% Complete</span>
                            <span>Topics: <?php echo $course['progress'] / 5; ?>/20</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <h2 class="text-xl font-bold text-navy mb-6">Performance Analytics</h2>
                <canvas id="performanceChart" width="100%" height="300"></canvas>
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
        
        // Animate progress bars
        document.querySelectorAll('.progress-bar').forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0';
            setTimeout(() => {
                bar.style.width = width;
            }, 500);
        });
        
        // Performance Chart
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [<?php echo "'" . implode("', '", array_column($courses, 'course_name')) . "'"; ?>],
                datasets: [{
                    label: 'Current Progress',
                    data: [<?php echo implode(', ', array_column($courses, 'progress')); ?>],
                    backgroundColor: '#fbbf24',
                    borderColor: '#f59e0b',
                    borderWidth: 1
                }, {
                    label: 'Target Progress',
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
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>