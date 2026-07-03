<?php
// /ojt1/students/reset_password.php

session_start();
require_once '../dbconnection.php';
require_once '../csrf_protection.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die('Invalid password reset link.');
}

// Verify token
$stmt = $conn->prepare("SELECT student_id FROM password_resets WHERE token = ? AND expires_at > NOW() LIMIT 1");
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();
$reset = $result->fetch_assoc();

if (!$reset) {
    die('Invalid or expired password reset link. Please request a new one.');
}

$studentId = $reset['student_id'];

// Get student details
$stmt = $conn->prepare("SELECT name, email FROM students WHERE student_id = ?");
$stmt->bind_param('s', $studentId);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    die('Student not found.');
}

// Handle password reset form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrfToken)) {
        $error = 'Invalid request. Please try again.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } else {
            // Update password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE students SET password_hash = ? WHERE student_id = ?");
            $stmt->bind_param('ss', $hashedPassword, $studentId);
            
            if ($stmt->execute()) {
                // Delete used token
                $stmt2 = $conn->prepare("DELETE FROM password_resets WHERE student_id = ?");
                $stmt2->bind_param('s', $studentId);
                $stmt2->execute();
                
                $message = 'Your password has been successfully reset! You can now log in.';
                regenerate_csrf_token();
                
                // Redirect to login after 3 seconds
                header('Refresh: 3; url=/ojt1/index.php');
            } else {
                $error = 'Failed to reset password. Please try again.';
            }
        }
    }
}

// Regenerate CSRF token for the form
$csrfToken = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - OJT Monitoring System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('../assets/img/users/wallpaper_19.png');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 1rem;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(7, 30, 65, 0.4);
            z-index: -1;
        }
        .reset-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            max-width: 480px;
            width: 100%;
            padding: 2rem;
        }
        .reset-card .header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .reset-card .header i {
            font-size: 3rem;
            color: #0d6efd;
            margin-bottom: 0.5rem;
        }
        .reset-card .header h3 {
            font-weight: 700;
            color: #0f172a;
        }
        .reset-card .header p {
            color: #64748b;
            font-size: 0.9rem;
        }
        .form-control {
            border: 1px solid rgba(100, 116, 139, 0.3);
            border-radius: 10px;
            padding: 0.7rem 1rem;
            font-size: 1rem;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
        }
        .form-label {
            font-weight: 600;
            color: #0f172a;
            font-size: 0.9rem;
        }
        .btn-primary {
            padding: 0.7rem;
            font-weight: 600;
            border-radius: 10px;
            box-shadow: 0 8px 18px rgba(13, 110, 253, 0.24);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(13, 110, 253, 0.31);
        }
        .password-toggle-wrapper {
            position: relative;
        }
        .password-toggle-wrapper .form-control {
            padding-right: 48px;
        }
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #6c757d;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 8px 4px;
            min-width: 44px;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .toggle-password:hover {
            color: #0d6efd;
        }
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #6c757d;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s ease;
        }
        .btn-back:hover {
            color: #0d6efd;
        }
        @media (max-width: 575.98px) {
            .reset-card { padding: 1.25rem; }
            .form-control { font-size: 0.95rem; }
            .btn { font-size: 0.9rem; }
        }
    </style>
</head>
<body>
    <div class="reset-card">
        <a href="/ojt1/index.php" class="btn-back mb-3">
            <i class="fas fa-arrow-left"></i> Back to Login
        </a>
        
        <div class="header">
            <i class="fas fa-key"></i>
            <h3>Reset Password</h3>
            <p>Enter your new password for <strong><?php echo htmlspecialchars($student['name']); ?></strong></p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success text-center">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $message; ?>
                <p class="mb-0 mt-2 small">You will be redirected to login...</p>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (!$message): ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($studentId); ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <div class="password-toggle-wrapper">
                        <input type="password" class="form-control password-toggle" name="password" required placeholder="Enter new password (min 8 characters)" minlength="8">
                        <button type="button" class="toggle-password" tabindex="-1" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Confirm Password</label>
                    <div class="password-toggle-wrapper">
                        <input type="password" class="form-control password-toggle" name="confirm_password" required placeholder="Confirm new password" minlength="8">
                        <button type="button" class="toggle-password" tabindex="-1" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-save me-2"></i> Reset Password
                </button>
            </form>
        <?php endif; ?>
    </div>

    <script src="../assets/js/student-reset-password.js"></script>
</body>
</html>