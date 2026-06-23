<?php
session_start();
require_once 'includes/db.php';
require_once 'send-email.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_step = (int)$_POST['current_step'];
    $next_step = $current_step; // Stay on same step until valid
    $errors = [];
    
    // Get all form data
    $data = $_SESSION['enroll_data'] ?? [];
    foreach ($_POST as $key => $value) {
        if ($key !== 'current_step' && $key !== 'action') {
            $data[$key] = $value;
        }
    }
    
    // Handle subjects array (checkboxes)
    if (isset($_POST['subjects']) && is_array($_POST['subjects'])) {
        $data['subjects'] = $_POST['subjects'];
    }
    
    // Handle file uploads
    if (!empty($_FILES['proof_upload']['name'])) {
        $file = $_FILES['proof_upload'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        if (in_array(strtolower($ext), $allowed)) {
            $new_name = uniqid() . '.' . $ext;
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $path = $upload_dir . $new_name;
            if (move_uploaded_file($file['tmp_name'], $path)) {
                $data['proof_upload'] = $path;
            }
        }
    }
    
    if (!empty($_FILES['sponsor_letter']['name'])) {
        $file = $_FILES['sponsor_letter'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        if (in_array(strtolower($ext), $allowed)) {
            $new_name = uniqid() . '.' . $ext;
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $path = $upload_dir . $new_name;
            if (move_uploaded_file($file['tmp_name'], $path)) {
                $data['sponsor_letter_upload'] = $path;
            }
        }
    }
    
    // Generate password if not exists
    if (empty($data['password'])) {
        $data['password'] = bin2hex(random_bytes(4));
    }
    
    // Get or create student record
    $email = $data['email'] ?? '';
    
    // STEP 1 VALIDATION
    if ($current_step == 1) {
        if (empty($email)) {
            $errors['email'] = "Email is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Please enter a valid email address.";
        }
        
        if (empty($errors)) {
            $next_step = 2;
        }
    }
    
    // STEP 2 VALIDATION
    if ($current_step == 2) {
        if (empty($data['first_name'])) {
            $errors['first_name'] = "First name is required.";
        }
        
        if (empty($data['surname'])) {
            $errors['surname'] = "Surname is required.";
        }
        
        if (empty($data['id_number'])) {
            $errors['id_number'] = "ID/Passport number is required.";
        } else {
            // Validate ID number (13 digits for South African ID)
            $id_number = $data['id_number'];
            if (!preg_match('/^\d{13}$/', $id_number)) {
                $errors['id_number'] = "ID number must be 13 digits.";
            }
        }
        
        if (empty($data['dob'])) {
            $errors['dob'] = "Date of birth is required.";
        }
        
        if (empty($data['gender'])) {
            $errors['gender'] = "Gender is required.";
        }
        
        if (empty($data['address'])) {
            $errors['address'] = "Address is required.";
        }
        
        if (empty($data['city'])) {
            $errors['city'] = "City is required.";
        }
        
        if (empty($data['province'])) {
            $errors['province'] = "Province is required.";
        }
        
        if (empty($data['postal_code'])) {
            $errors['postal_code'] = "Postal code is required.";
        }
        
        if (empty($data['nationality'])) {
            $errors['nationality'] = "Nationality is required.";
        }
        
        if (empty($data['emergency_contact_name'])) {
            $errors['emergency_contact_name'] = "Emergency contact name is required.";
        }
        
        if (empty($data['emergency_contact_phone'])) {
            $errors['emergency_contact_phone'] = "Emergency contact phone is required.";
        } else {
            // Validate emergency phone (South African format)
            $phone = $data['emergency_contact_phone'];
            if (!preg_match('/^0[6-8]\d{8}$/', $phone)) {
                $errors['emergency_contact_phone'] = "Phone must be 10 digits and start with 0.";
            }
        }
        
        if (empty($errors)) {
            $next_step = 3;
        }
    }
    
    // STEP 3 VALIDATION
    if ($current_step == 3) {
        if (empty($data['subjects']) || !is_array($data['subjects']) || count($data['subjects']) == 0) {
            $errors['subjects'] = "Please select at least one subject.";
        }
        
        if (empty($errors)) {
            $next_step = 4;
        }
    }
    
    // STEP 4 VALIDATION
    if ($current_step == 4) {
        if (empty($data['financier_type'])) {
            $errors['financier_type'] = "Please select who is financing your studies.";
        }
        
        if ($data['financier_type'] == 'Self') {
            if (empty($data['confirm_age'])) {
                $errors['confirm_age'] = "You must confirm you are 18 years or older.";
            }
            
            if (empty($data['occupation'])) {
                $errors['occupation'] = "Occupation/Income source is required.";
            }
            
            if (empty($data['payment_method_self'])) {
                $errors['payment_method_self'] = "Payment method is required.";
            }
            
            $data['payment_method'] = $data['payment_method_self'];
        }
        
        if ($data['financier_type'] == 'Parent/Guardian') {
            if (empty($data['financier_name'])) {
                $errors['financier_name'] = "Parent/Guardian name is required.";
            }
            
            if (empty($data['financier_relationship'])) {
                $errors['financier_relationship'] = "Relationship is required.";
            }
            
            if (empty($data['financier_id'])) {
                $errors['financier_id'] = "Parent ID is required.";
            } else {
                // Validate parent ID (13 digits)
                if (!preg_match('/^\d{13}$/', $data['financier_id'])) {
                    $errors['financier_id'] = "ID number must be 13 digits.";
                }
            }
            
            if (empty($data['financier_phone'])) {
                $errors['financier_phone'] = "Parent phone is required.";
            } else {
                // Validate parent phone
                if (!preg_match('/^0[6-8]\d{8}$/', $data['financier_phone'])) {
                    $errors['financier_phone'] = "Phone must be 10 digits and start with 0.";
                }
            }
            
            if (empty($data['financier_email'])) {
                $errors['financier_email'] = "Parent email is required.";
            } elseif (!filter_var($data['financier_email'], FILTER_VALIDATE_EMAIL)) {
                $errors['financier_email'] = "Please enter a valid email address.";
            }
            
            if (empty($data['financier_address'])) {
                $errors['financier_address'] = "Parent address is required.";
            }
            
            if (empty($data['payment_method_parent'])) {
                $errors['payment_method_parent'] = "Payment method is required.";
            }
            
            $data['payment_method'] = $data['payment_method_parent'];
        }
        
        if ($data['financier_type'] == 'Sponsor/Other') {
            if (empty($data['sponsor_org_name'])) {
                $errors['sponsor_org_name'] = "Sponsor organization name is required.";
            }
            
            if (empty($data['sponsor_contact_person'])) {
                $errors['sponsor_contact_person'] = "Contact person name is required.";
            }
            
            if (empty($data['sponsor_email'])) {
                $errors['sponsor_email'] = "Sponsor email is required.";
            } elseif (!filter_var($data['sponsor_email'], FILTER_VALIDATE_EMAIL)) {
                $errors['sponsor_email'] = "Please enter a valid email address.";
            }
            
            if (empty($data['sponsor_phone'])) {
                $errors['sponsor_phone'] = "Sponsor phone is required.";
            } else {
                // Validate sponsor phone
                if (!preg_match('/^0[6-8]\d{8}$/', $data['sponsor_phone'])) {
                    $errors['sponsor_phone'] = "Phone must be 10 digits and start with 0.";
                }
            }
            
            if (empty($data['sponsor_address'])) {
                $errors['sponsor_address'] = "Sponsor address is required.";
            }
            
            if (empty($data['payment_method_sponsor'])) {
                $errors['payment_method_sponsor'] = "Payment method is required.";
            }
            
            $data['payment_method'] = $data['payment_method_sponsor'];
        }
        
        if (empty($errors)) {
            $next_step = 5;
        }
    }
    
    // STEP 5 VALIDATION
    if ($current_step == 5) {
        if (empty($data['package_selected'])) {
            $errors['package_selected'] = "Please select a package.";
        }
        
        if (empty($data['payment_method_real'])) {
            $errors['payment_method_real'] = "Please select a payment method.";
        }
        
        if (empty($errors)) {
            $next_step = 6;
        }
    }
    
    // STEP 6 VALIDATION
    if ($current_step == 6) {
        if (empty($data['confirm_details'])) {
            $errors['confirm_details'] = "You must agree to the terms and conditions.";
        }
        
        if (empty($errors)) {
            $next_step = 7;
        }
    }
    
    // STEP 7 VALIDATION (OTP)
    if ($current_step == 7 && isset($_POST['action']) && $_POST['action'] === 'verify_otp') {
        $entered_otp = $_POST['otp'] ?? '';
        if (strlen($entered_otp) !== 6 || !ctype_digit($entered_otp)) {
            $errors['otp'] = "Invalid OTP. Please enter 6 digits.";
        } else {
            // In a real system, verify OTP against database
            $stmt = $pdo->prepare("SELECT otp, otp_expiry FROM students WHERE email = ?");
            $stmt->execute([$email]);
            $result = $stmt->fetch();
            
            if ($result && $result['otp'] == $entered_otp && strtotime($result['otp_expiry']) > time()) {
                $_SESSION['enrollment_complete'] = true;
                $_SESSION['final_data'] = $data;
                
                // Send welcome email
                $welcome_subject = "Welcome to NovaTech FET College!";
                $welcome_body = "
                <h2>Congratulations!</h2>
                <p>Your enrollment is complete!</p>
                <p><strong>Student Email:</strong> $email</p>
                <p><strong>Password:</strong> " . ($data['password'] ?? 'Check your initial email') . "</p>
                <p>You can now log in to your student dashboard.</p>
                <p>Best regards,<br>The NovaTech Team</p>
                ";
                sendEmail($email, $welcome_subject, $welcome_body);
                
                header("Location: confirm.php");
                exit;
            } else {
                $errors['otp'] = "Invalid or expired OTP.";
            }
        }
    }
    
    // Store data and errors in session
    $_SESSION['enroll_data'] = $data;
    $_SESSION['enroll_errors'] = $errors;
    
    if (!empty($errors)) {
        header("Location: enroll.php?step=" . $current_step);
        exit;
    }
    
    try {
        // Check if student exists
        $stmt = $pdo->prepare("SELECT id FROM students WHERE email = ?");
        $stmt->execute([$email]);
        $student = $stmt->fetch();
        
        if (!$student) {
            // Create new student
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO students (email, phone, password) VALUES (?, ?, ?)");
            $stmt->execute([$email, $data['phone'] ?? '', $password_hash]);
            $student_id = $pdo->lastInsertId();
        } else {
            $student_id = $student['id'];
        }
        
        // Save to session
        $_SESSION['student_id'] = $student_id;
        $_SESSION['enroll_email'] = $email;
        
        // Save progress
        $json_data = json_encode($data);
        $stmt = $pdo->prepare("INSERT INTO enrollment_progress (student_id, current_step, data) VALUES (?, ?, ?) 
                               ON DUPLICATE KEY UPDATE current_step = VALUES(current_step), data = VALUES(data)");
        $stmt->execute([$student_id, $next_step, $json_data]);
        
        // If we're going to step 7, generate OTP
        if ($next_step == 7) {
            $otp = rand(100000, 999999);
            $expiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            $stmt = $pdo->prepare("UPDATE students SET otp = ?, otp_expiry = ? WHERE id = ?");
            $stmt->execute([$otp, $expiry, $student_id]);
            
            // Send OTP email
            $email_subject = "Your NovaTech Enrollment OTP";
            $email_body = "
            <h2>Hello!</h2>
            <p>Thank you for enrolling at NovaTech FET College!</p>
            <p>Your One-Time Password (OTP) is: <strong style='font-size: 24px; color: #0d6efd;'>$otp</strong></p>
            <p>This OTP will expire in 30 minutes.</p>
            <p>Enter this code on the enrollment page to complete your registration.</p>
            <p>Best regards,<br>The NovaTech Team</p>
            ";
            
            if (sendEmail($email, $email_subject, $email_body)) {
                error_log("OTP email sent successfully to: $email");
            } else {
                error_log("Failed to send OTP email to: $email");
            }
        }
        
        // Redirect to next step
        header("Location: enroll.php?step=" . $next_step);
        exit;
        
    } catch (Exception $e) {
        error_log("Enrollment Error: " . $e->getMessage());
        $_SESSION['enroll_errors'] = ["Database error. Please try again."];
        header("Location: enroll.php?step=" . $current_step);
        exit;
    }
} else {
    // Redirect to enroll.php if accessed directly
    header("Location: enroll.php?step=1");
    exit;
}
?>