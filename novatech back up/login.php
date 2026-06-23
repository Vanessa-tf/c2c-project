<?php
session_start();
include(__DIR__ . "/includes/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']); // Added trim for password

    if (!empty($username) && !empty($password)) {
        // Fetch user from DB with PDO
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = :username OR email = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Debugging: Inspect fetched user data
        echo '<pre>'; var_dump($user); echo '</pre>';

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
                        header("Location: parent_dashboard.php");
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
            $error = "No user found with that username/email.";
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
    <style>
        :root {
            --dark-blue: #1a3a6c;
            --yellow: #ffc107;
            --beige: #f5f1e3;
            --white: #ffffff;
            --light-blue: #e0f0ff;
            --gray: #6c757d;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #4a6fa5, #1a3a6c);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            display: flex;
            width: 100%;
            max-width: 900px;
            min-height: 500px;
            background-color: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25);
            animation: fadeIn 0.8s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-left {
            flex: 1;
            background: linear-gradient(to bottom right, var(--dark-blue), #2a4a7c);
            color: var(--white);
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-left::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: var(--yellow);
            opacity: 0.1;
        }
        
        .login-left::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: var(--yellow);
            opacity: 0.1;
        }
        
        .college-logo {
            margin-bottom: 30px;
            text-align: center;
        }
        
        .college-logo h1 {
            font-size: 28px;
            margin-top: 10px;
            font-weight: 700;
        }
        
        .welcome-text {
            margin-bottom: 30px;
        }
        
        .welcome-text h2 {
            font-size: 24px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .features-list {
            list-style-type: none;
            margin-bottom: 40px;
        }
        
        .features-list li {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .features-list i {
            margin-right: 10px;
            color: var(--yellow);
            font-size: 18px;
        }
        
        .quote {
            font-style: italic;
            text-align: center;
            margin-top: auto;
            padding: 15px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }
        
        .login-right {
            flex: 1;
            background-color: var(--beige);
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h2 {
            color: var(--dark-blue);
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: var(--gray);
        }
        
        .login-form {
            margin-bottom: 25px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-blue);
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }
        
        .input-with-icon input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .input-with-icon input:focus {
            border-color: var(--dark-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(26, 58, 108, 0.1);
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .remember {
            display: flex;
            align-items: center;
        }
        
        .remember input {
            margin-right: 8px;
            accent-color: var(--dark-blue);
        }
        
        .forgot-password {
            color: var(--dark-blue);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .forgot-password:hover {
            color: #2a4a7c;
            text-decoration: underline;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(to right, var(--dark-blue), #2a4a7c);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(26, 58, 108, 0.25);
        }
        
        .btn-login:hover {
            background: linear-gradient(to right, #2a4a7c, var(--dark-blue));
            box-shadow: 0 6px 15px rgba(26, 58, 108, 0.35);
            transform: translateY(-2px);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .signup-link {
            text-align: center;
            margin-top: 20px;
            color: var(--gray);
        }
        
        .signup-link a {
            color: var(--dark-blue);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .signup-link a:hover {
            color: #2a4a7c;
            text-decoration: underline;
        }
        
        .role-info {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
            color: var(--gray);
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 100%;
            }
            
            .login-left, .login-right {
                padding: 30px;
            }
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
            
            <form class="login-form" id="loginForm" method="POST" action="login.php">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
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
            
            <div class="role-info">
                <p>Your account type will be automatically detected</p>
            </div>
            
            <div class="signup-link">
                Don't have an account? <a href="#">Contact Administrator</a>
            </div>
        </div>
    </div>
</body>
</html>