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

// Fetch schedule with course names
$stmt = $pdo->prepare("
    SELECT s.event_type, s.description, s.event_date AS date, s.start_time, s.end_time, c.title AS course_name
    FROM schedules s
    LEFT JOIN courses c ON s.course_id = c.id
    WHERE s.user_id = :user_id OR s.user_id IS NULL
    ORDER BY s.event_date ASC, s.start_time ASC
");
$stmt->execute(['user_id' => $user_id]);
$schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule - NovaTech FET College</title>
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
        .dashboard-card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .dashboard-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1); }
        .sidebar { transition: all 0.3s ease; }
        @media (max-width: 768px) {
            .sidebar { position: fixed; left: -300px; z-index: 1000; height: 100vh; }
            .sidebar.active { left: 0; }
            .overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 999; }
            .overlay.active { display: block; }
        }
        .notification-dot { position: absolute; top: -5px; right: -5px; width: 12px; height: 12px; background-color: #ef4444; border-radius: 50%; }
        .header-container { max-width: 1280px; margin: 0 auto; padding: 0 1.5rem; }
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
            <?php include(__DIR__ . '/includes/sidebar-nav.php'); ?>
        </div>
    </div>

    <!-- Main Content -->
    <div class="md:ml-64">
        <!-- Top Navigation -->
        <header class="bg-white shadow-md">
            <div class="header-container mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <button class="text-navy md:hidden" id="menuButton">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h1 class="text-xl font-bold text-navy">Schedule</h1>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <button class="text-navy focus:outline-none">
                                <i class="fas fa-bell text-xl"></i>
                                <span class="notification-dot"></span>
                            </button>
                        </div>
                        <div class="hidden md:flex items-center">
                            <div class="w-8 h-8 bg-gold rounded-full flex items-center justify-center mr-2">
                                <span class="text-navy font-bold text-sm"><?php echo $initials; ?></span>
                            </div>
                            <span class="text-navy font-medium"><?php echo $username; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Schedule Content -->
        <main class="container mx-auto px-6 py-8">
            <div class="bg-white rounded-xl shadow-lg p-6 dashboard-card">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-navy">Your Schedule</h2>
                    <a href="#" class="text-gold hover:underline">Add Event</a>
                </div>
                <?php if (empty($schedule)): ?>
                    <p class="text-gray-600">No scheduled events at the moment.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($schedule as $event): ?>
                            <div class="p-3 border border-gray-200 rounded-lg">
                                <h3 class="font-medium text-navy mb-2">
                                    <?php echo htmlspecialchars(ucfirst($event['event_type'])); ?>
                                    <?php echo $event['course_name'] ? '(' . htmlspecialchars($event['course_name']) . ')' : ''; ?>
                                </h3>
                                <p class="text-sm text-gray-600 mb-1">
                                    <i class="far fa-calendar mr-2"></i>
                                    <?php echo date('M d, Y', strtotime($event['date'])); ?>
                                </p>
                                <?php if ($event['start_time'] && $event['end_time']): ?>
                                    <p class="text-sm text-gray-600">
                                        <i class="far fa-clock mr-2"></i>
                                        <?php echo date('h:i A', strtotime($event['start_time'])) . ' - ' . date('h:i A', strtotime($event['end_time'])); ?>
                                    </p>
                                <?php endif; ?>
                                <?php if ($event['description']): ?>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <?php echo htmlspecialchars($event['description']); ?>
                                    </p>
                                <?php endif; ?>
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