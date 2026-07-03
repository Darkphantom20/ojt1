<?php
// /ojt1/students/forgot_password.php

session_start();
require_once '../dbconnection.php';
require_once '../csrf_protection.php';
require_once '../email_config.php';

// Verify CSRF token
$csrfToken = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($csrfToken)) {
    header('Location: /ojt1/index.php?forgot_password_failed=1');
    exit;
}

// Get email from POST
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: /ojt1/index.php?forgot_password_failed=1');
    exit;
}

// Check if student exists with this email and is approved
$stmt = $conn->prepare("SELECT student_id, name, email FROM students WHERE email = ? AND registration_status = 'approved' LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    // Check if student exists but not approved
    $stmt2 = $conn->prepare("SELECT student_id, name, email, registration_status FROM students WHERE email = ? LIMIT 1");
    $stmt2->bind_param('s', $email);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $student2 = $result2->fetch_assoc();
    
    if ($student2) {
        if ($student2['registration_status'] === 'pending') {
            header('Location: /ojt1/index.php?student_login_failed=5');
            exit;
        } elseif ($student2['registration_status'] === 'pending_verification') {
            header('Location: /ojt1/index.php?student_login_failed=7');
            exit;
        } elseif ($student2['registration_status'] === 'rejected') {
            header('Location: /ojt1/index.php?student_login_failed=6');
            exit;
        }
    }
    
    header('Location: /ojt1/index.php?forgot_password_failed=1');
    exit;
}

// Generate a unique reset token
$resetToken = bin2hex(random_bytes(32));
$expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

// Delete any existing tokens for this student
$stmt = $conn->prepare("DELETE FROM password_resets WHERE student_id = ?");
$stmt->bind_param('s', $student['student_id']);
$stmt->execute();

// Insert new reset token
$stmt = $conn->prepare("INSERT INTO password_resets (student_id, token, expires_at) VALUES (?, ?, ?)");
$stmt->bind_param('sss', $student['student_id'], $resetToken, $expiresAt);
$stmt->execute();

// Build reset link
$baseUrl = getBaseUrl();
$resetLink = $baseUrl . "/students/reset_password.php?token=" . urlencode($resetToken);

// Send email
$subject = "🔑 Password Reset Request - OJT Monitoring System";
$body = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #0d6efd, #0a58ca); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { padding: 20px; background: #f8f9fa; border-radius: 0 0 8px 8px; }
        .button { display: inline-block; padding: 12px 30px; background: #0d6efd; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; }
        .button:hover { background: #0a58ca; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .code-block { background: #e9ecef; padding: 10px; border-radius: 5px; word-break: break-all; font-size: 12px; }
        .warning { background: #fff3cd; padding: 12px; border-radius: 8px; border-left: 4px solid #ffc107; margin: 15px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>🔑 Password Reset Request</h2>
            <p>OJT Monitoring System</p>
        </div>
        <div class='content'>
            <p>Hello <strong>{$student['name']}</strong>,</p>
            <p>We received a request to reset your password for your OJT Monitoring System account.</p>
            <p style='text-align: center;'>
                <a href='{$resetLink}' class='button'>Reset Password</a>
            </p>
            <p>If the button doesn't work, copy and paste this link into your browser:</p>
            <div class='code-block'>{$resetLink}</div>
            <div class='warning'>
                <strong>⏰ This link will expire in 1 hour.</strong>
                <p class='mb-0' style='margin-top: 5px;'>If you did not request this password reset, please ignore this email.</p>
            </div>
        </div>
        <div class='footer'>
            &copy; " . date('Y') . " OJT Monitoring System. All rights reserved.
        </div>
    </div>
</body>
</html>
";

$emailSent = sendEmail($student['email'], $subject, $body);

if ($emailSent) {
    header('Location: /ojt1/index.php?forgot_password_success=1');
} else {
    error_log("Password reset email failed for student: {$student['student_id']}");
    header('Location: /ojt1/index.php?forgot_password_failed=1');
}
exit;
?>