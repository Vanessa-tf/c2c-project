<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
checkAdmin();
$pdo = getPDO();


if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $user_id = $_POST['user_id'] ?? null;
    
    try {
        switch($action) {
            case 'create':
                // Validate new user
                $name = htmlspecialchars($_POST['name']);
                $email = htmlspecialchars($_POST['email']);
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $role = $_POST['role'];
                
                // Check existing email
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if($stmt->rowCount() > 0) {
                    throw new Exception("Email already exists");
                }
                
                // Create user
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?,?,?,?)");
                $stmt->execute([$name, $email, $password, $role]);
                header("Location: users.php?success=User created");
                break;
                
            case 'update':
                // Validate existing user
                $name = htmlspecialchars($_POST['name']);
                $email = htmlspecialchars($_POST['email']);
                $role = $_POST['role'];
                $password = !empty($_POST['password']) ? 
                    password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
                
                // Check email conflict
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $user_id]);
                if($stmt->rowCount() > 0) {
                    throw new Exception("Email already taken by another user");
                }
                
                // Build update query
                $updates = ["name = ?", "email = ?", "role = ?"];
                $params = [$name, $email, $role];
                
                if($password) {
                    $updates[] = "password = ?";
                    $params[] = $password;
                }
                
                $params[] = $user_id;
                $sql = "UPDATE users SET ".implode(", ", $updates)." WHERE id = ?";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                header("Location: users.php?success=User updated");
                break;
                
            case 'delete':
                // Prevent self-deletion
                if($user_id == $_SESSION['admin']['id']) {
                    throw new Exception("Cannot delete your own account");
                }
                
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                header("Location: users.php?success=User deleted");
                break;
        }
        
    } catch(Exception $e) {
        $location = ($action === 'create') ? 'add_user.php' : "edit_user.php?id=$user_id";
        header("Location: $location?error=".urlencode($e->getMessage()));
        exit();
    }
}