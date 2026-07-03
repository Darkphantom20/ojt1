<?php
// /ojt1/students/resend_verification.php

session_start();
require_once '../dbconnection.php';
require_once '../email_verification.php';

$studentId = $_GET['student_id'] ?? '';

if (empty($studentId)) {
    die('Student ID required');
}

// Get student details
$student = getStudentById($conn, $studentId);

if (!$student) {
    die('Student not found.');
}

if ($student['registration_status'] !== 'pending_verification') {
    die('This account has already been verified.');
}

// Generate new token and send email
$token = generateVerificationToken();
storeVerificationToken($conn, $studentId, $token, 24);

$baseUrl = getBaseUrl();
$verificationLink = $baseUrl . "/students/verify_email.php?token=" . urlencode($token);

$emailBody = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #0d6efd; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { padding: 20px; background: #f8f9fa; border-radius: 0 0 8px 8px; }
        .button { display: inline-block; padding: 12px 24px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .code-block { background: #e9ecef; padding: 10px; border-radius: 5px; word-break: break-all; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>📧 Resend Verification</h2>
            <p>OJT Monitoring System</p>
        </div>
        <div class='content'>
            <p>Hello <strong>{$student['name']}</strong>,</p>
            <p>You requested a new verification link. Please verify your email by clicking the button below:</p>
            <p style='text-align: center;'>
                <a href='{$verificationLink}' class='button'>Verify Email Address</a>
            </p>
            <p>If the button doesn't work, copy and paste this link into your browser:</p>
            <div class='code-block'>{$verificationLink}</div>
            <p style='margin-top: 15px;'><strong>⏰ This link will expire in 24 hours.</strong></p>
        </div>
        <div class='footer'>
            &copy; " . date('Y') . " OJT Monitoring System. All rights reserved.
        </div>
    </div>
</body>
</html>
";

$emailSent = sendEmail($student['email'], 'Resend Verification - OJT Monitoring System', $emailBody);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Verification Sent - OJT Monitoring System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/student-auth.css">
</head>
<body>
    <div class="card resend-card">
        <div class="icon">📧</div>
        <h2 class="fw-bold mb-3">Verification Sent!</h2>
        <p class="text-muted">A new verification email has been sent to <strong><?php echo htmlspecialchars($student['email']); ?></strong></p>
        <p class="text-muted">Please check your inbox and spam folder.</p>
        <a href="/ojt1/index.php" class="btn btn-primary w-100">Back to Login</a>
    </div>
</body>
</html>