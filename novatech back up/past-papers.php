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

// Fetch past papers (assuming exam_papers table exists)
$stmt = $pdo->prepare("SELECT title, description, file_link, year FROM exam_papers ORDER BY year DESC");
$stmt->execute();
$past_papers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Past Papers - NovaTech FET College</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <h1 class="text-xl font-bold text-navy">Past Papers</h1>
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

        <!-- Past Papers Content -->
        <main class="container mx-auto px-6 py-8">
            <div class="bg-white rounded-xl shadow-lg p-6 dashboard-card">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-navy">Past Exam Papers</h2>
                    <a href="#" class="text-gold hover:underline">Search Papers</a>
                </div>
                <?php if (empty($past_papers)): ?>
                    <p class="text-gray-600">No past papers available at the moment.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($past_papers as $paper): ?>
                            <div class="flex justify-between items-center p-3 border border-gray-200 rounded-lg">
                                <div>
                                    <h3 class="font-medium text-navy"><?php echo htmlspecialchars($paper['title']); ?> - <?php echo htmlspecialchars($paper['year']); ?></h3>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($paper['description']); ?></p>
                                </div>
                                <a href="<?php echo htmlspecialchars($paper['file_link']); ?>" class="bg-navy text-white text-sm py-1 px-3 rounded-lg hover:bg-opacity-90 transition">Download</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
    </script>
</body>
</html>