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

// Fetch study groups with course names
$stmt = $pdo->prepare("
    SELECT sg.id, sg.name, sg.description, sg.members_count, sg.last_active, c.title AS course_name 
    FROM study_groups sg 
    LEFT JOIN courses c ON sg.course_id = c.id 
    ORDER BY sg.last_active DESC
");
$stmt->execute();
$study_groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch messages for selected group (if any)
$messages = [];
$selected_group_id = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
if ($selected_group_id) {
    $stmt = $pdo->prepare("
        SELECT m.message, m.created_at, u.username 
        FROM messages m 
        JOIN users u ON m.user_id = u.id 
        WHERE m.study_group_id = :group_id 
        ORDER BY m.created_at ASC
    ");
    $stmt->execute(['group_id' => $selected_group_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Groups - NovaTech FET College</title>
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
        .chat-container { max-height: 500px; overflow-y: auto; }
        .message { margin-bottom: 1rem; }
        .message .username { font-weight: 600; }
        .message .time { font-size: 0.75rem; color: #6b7280; }
        @media (max-width: 768px) {
            .sidebar { position: fixed; left: -300px; z-index: 1000; height: 100vh; }
            .sidebar.active { left: 0; }
            .overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 999; }
            .overlay.active { display: block; }
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
            <?php include(__DIR__ . '/includes/sidebar-nav.php'); ?>
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
                    <h1 class="text-xl font-bold text-navy">Study Groups</h1>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <button id="notificationButton" class="text-navy relative">
                                <i class="fas fa-bell"></i>
                                <span id="notificationDot" class="absolute top-[-5px] right-[-5px] w-3 h-3 bg-red-500 rounded-full hidden"></span>
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

        <!-- Study Groups Content -->
        <main class="container mx-auto px-6 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Study Groups List -->
                <div class="lg:col-span-1 bg-white rounded-xl shadow-lg p-6 dashboard-card">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-navy">Study Groups</h2>
                        <a href="#" class="text-gold hover:underline">Create Group</a>
                    </div>
                    <?php if (empty($study_groups)): ?>
                        <p class="text-gray-600">No study groups available. Create one to start collaborating!</p>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($study_groups as $group): ?>
                                <a href="?group_id=<?php echo $group['id']; ?>" 
                                   class="block p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition <?php echo $selected_group_id == $group['id'] ? 'bg-gold text-navy' : ''; ?>">
                                    <h3 class="font-medium text-navy">
                                        <?php echo htmlspecialchars($group['name']); ?>
                                        <?php echo $group['course_name'] ? '(' . htmlspecialchars($group['course_name']) . ')' : ''; ?>
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        <?php echo $group['members_count']; ?> members • 
                                        Last active: <?php echo date('h:i A', strtotime($group['last_active'])); ?>
                                    </p>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Chatroom -->
                <div class="lg:col-span-3 bg-white rounded-xl shadow-lg p-6 dashboard-card">
                    <?php if ($selected_group_id): ?>
                        <h2 class="text-xl font-bold text-navy mb-6">
                            Chat: <?php echo htmlspecialchars($study_groups[array_search($selected_group_id, array_column($study_groups, 'id'))]['name']); ?>
                        </h2>
                        <div id="chatContainer" class="chat-container mb-4 p-4 border border-gray-200 rounded-lg">
                            <?php foreach ($messages as $message): ?>
                                <div class="message">
                                    <span class="username text-navy"><?php echo htmlspecialchars($message['username']); ?>:</span>
                                    <span class="text-gray-800"><?php echo htmlspecialchars($message['message']); ?></span>
                                    <div class="time"><?php echo date('M d, h:i A', strtotime($message['created_at'])); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="flex space-x-2">
                            <input type="text" id="messageInput" class="flex-1 border p-2 rounded-lg" placeholder="Type a message...">
                            <button id="sendMessage" class="bg-gold text-navy font-bold py-2 px-4 rounded-lg hover:bg-yellow-500 transition">Send</button>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-600">Select a study group to start chatting.</p>
                    <?php endif; ?>
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

        // WebSocket for real-time chat
        let socket = null;
        const chatContainer = document.getElementById('chatContainer');
        const messageInput = document.getElementById('messageInput');
        const sendMessageButton = document.getElementById('sendMessage');
        const selectedGroupId = <?php echo json_encode($selected_group_id); ?>;
        const userId = <?php echo json_encode($user_id); ?>;
        const username = <?php echo json_encode($username); ?>;

        if (selectedGroupId) {
            socket = new WebSocket('ws://localhost:8080');

            socket.onopen = () => {
                console.log('Connected to WebSocket server');
            };

            socket.onmessage = (event) => {
                const data = JSON.parse(event.data);
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message';
                messageDiv.innerHTML = `
                    <span class="username text-navy">${data.username}:</span>
                    <span class="text-gray-800">${data.message}</span>
                    <div class="time">${new Date(data.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: 'numeric', hour12: true })}</div>
                `;
                chatContainer.appendChild(messageDiv);
                chatContainer.scrollTop = chatContainer.scrollHeight;
            };

            socket.onclose = () => {
                console.log('Disconnected from WebSocket server');
            };

            socket.onerror = (error) => {
                console.error('WebSocket error:', error);
            };

            sendMessageButton.addEventListener('click', sendMessage);
            messageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') sendMessage();
            });

            function sendMessage() {
                const message = messageInput.value.trim();
                if (message && socket.readyState === WebSocket.OPEN) {
                    const data = {
                        group_id: selectedGroupId,
                        user_id: userId,
                        username: username,
                        message: message
                    };
                    socket.send(JSON.stringify(data));
                    messageInput.value = '';
                }
            }
        }
    </script>
</body>
</html>
