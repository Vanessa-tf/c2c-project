<?php
session_start();
include(__DIR__ . "/includes/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $login_type = $_POST['login_type'] ?? 'student'; // Default to student login

    if (!empty($username) && !empty($password)) {
        
        if ($login_type === 'parent') {
            // Parent login using ID number
            $stmt = $pdo->prepare("SELECT id, username, password, role, id_number FROM users WHERE id_number = :id_number AND role = 'parent'");
            $stmt->execute(['id_number' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            // Regular login for students/teachers/admin using username or email
            $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE (username = :username OR email = :username) AND role != 'parent'");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if ($user) {
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Store session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                switch ($user['role']) {
                    case 'student':
                        header("Location: student-dashboard.php");
                        break;
                    case 'parent':
                        header("Location:parent_dashboard.php");
                        break;
                    case 'teacher':
                        header("Location: teacher_dashboard.php");
                        break;
                    case 'content':
                        header("Location: content_dashboard.php");
                        break;
                    case 'admin':
                        header("Location: admin_dashboard.php");
                        break;
                    default:
                        echo "Invalid role assigned.";
                        break;
                }
                exit;
            } else {
                $error = "Invalid password!";
            }
        } else {
            if ($login_type === 'parent') {
                $error = "No parent account found with that ID number.";
            } else {
                $error = "No user found with that username/email.";
            }
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NovaTech FET College - LMS Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style_login.css">
    <style>
        .login-type-selector {
            display: flex;
            margin-bottom: 20px;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid var(--yellow);
        }
        .login-type-btn {
            flex: 1;
            padding: 10px 15px;
            background: white;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .login-type-btn.active {
            background: var(--yellow);
            color: var(--navy);
        }
        .login-type-btn:hover {
            background: #f0f9ff;
        }
        .login-type-btn.active:hover {
            background: var(--yellow);
        }
        :root {
            --navy: #1e3a6c;
            --yellow: #fbbf24;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <div class="college-logo">
                <i class="fas fa-graduation-cap" style="font-size: 48px; color: var(--yellow);"></i>
                <h1>NovaTech FET College</h1>
            </div>
            
            <div class="welcome-text">
                <h2>Welcome to LMS Portal</h2>
                <p>Access your educational resources and continue your learning journey</p>
            </div>
            
            <ul class="features-list">
                <li><i class="fas fa-check-circle"></i> Access to past exam papers and study guides</li>
                <li><i class="fas fa-check-circle"></i> Live and recorded lessons</li>
                <li><i class="fas fa-check-circle"></i> Personalized learning pathways</li>
                <li><i class="fas fa-check-circle"></i> Peer collaboration and support</li>
                <li><i class="fas fa-check-circle"></i> Progress tracking and analytics</li>
                <li><i class="fas fa-check-circle"></i> Parent monitoring and communication</li>
            </ul>
            
            <div class="quote">
                <p>"Success is not the absence of obstacles, but the courage to push through them."</p>
            </div>
        </div>
        
        <div class="login-right">
            <div class="login-header">
                <h2>Login to Your Account</h2>
                <p>Enter your credentials to access the portal</p>
            </div>

            <!-- Login Type Selector -->
            <div class="login-type-selector">
                <button type="button" class="login-type-btn active" data-type="student" id="studentBtn">
                    <i class="fas fa-user-graduate"></i> Student/Staff
                </button>
                <button type="button" class="login-type-btn" data-type="parent" id="parentBtn">
                    <i class="fas fa-users"></i> Parent
                </button>
            </div>
            
            <form class="login-form" id="loginForm" method="POST" action="login.php">
                <input type="hidden" name="login_type" id="loginType" value="student">
                
                <div class="form-group">
                    <label for="username" id="usernameLabel">Username or Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user" id="usernameIcon"></i>
                        <input type="text" id="username" name="username" placeholder="Enter your username or email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>
                
                <div class="remember-forgot">
                    <div class="remember">
                        <input type="checkbox" id="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="#" class="forgot-password">Forgot Password?</a>
                </div>
                
                <button type="submit" class="btn-login">Login</button>
            </form>
            
            <?php if (isset($error)): ?>
                <div class="error-message" style="color: #dc3545; margin-top: 10px; padding: 10px; background-color: #f8d7da; border-radius: 5px; border: 1px solid #f5c6cb;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <div class="role-info" id="roleInfo">
                <p>Your account type will be automatically detected</p>
            </div>
            
            <div class="signup-link">
                Don't have an account? <a href="#">Contact Administrator</a>
            </div>
        </div>
    </div>

    <script>
        const studentBtn = document.getElementById('studentBtn');
        const parentBtn = document.getElementById('parentBtn');
        const loginType = document.getElementById('loginType');
        const usernameLabel = document.getElementById('usernameLabel');
        const usernameInput = document.getElementById('username');
        const usernameIcon = document.getElementById('usernameIcon');
        const roleInfo = document.getElementById('roleInfo');

        studentBtn.addEventListener('click', function() {
            setLoginType('student');
        });

        parentBtn.addEventListener('click', function() {
            setLoginType('parent');
        });

        function setLoginType(type) {
            // Update button states
            studentBtn.classList.toggle('active', type === 'student');
            parentBtn.classList.toggle('active', type === 'parent');
            
            // Update form
            loginType.value = type;
            
            if (type === 'parent') {
                usernameLabel.textContent = 'ID Number';
                usernameInput.placeholder = 'Enter your 13-digit ID number';
                usernameIcon.className = 'fas fa-id-card';
                roleInfo.innerHTML = '<p>Enter your South African ID number to access the parent portal</p>';
            } else {
                usernameLabel.textContent = 'Username or Email';
                usernameInput.placeholder = 'Enter your username or email';
                usernameIcon.className = 'fas fa-user';
                roleInfo.innerHTML = '<p>Your account type will be automatically detected</p>';
            }
            
            // Clear any previous values
            usernameInput.value = '';
        }

        // Form validation for ID number
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            if (loginType.value === 'parent') {
                const idNumber = usernameInput.value.trim();
                
                // Basic South African ID number validation
                if (!/^\d{13}$/.test(idNumber)) {
                    e.preventDefault();
                    alert('Please enter a valid 13-digit ID number');
                    return false;
                }
            }
        });
    </script>
</body>
</html>