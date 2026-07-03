<?php
// /ojt1/students/verify_email.php

session_start();
require_once '../dbconnection.php';
require_once '../email_verification.php';
require_once '../email_notification.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die('Invalid verification link.');
}

// Verify the token
$studentId = verifyEmailToken($conn, $token);

if (!$studentId) {
    die('Invalid or expired verification link. Please request a new one.');
}

// Update student status to 'pending' (awaiting coordinator approval)
$stmt = $conn->prepare("UPDATE students SET registration_status = 'pending' WHERE student_id = ?");
$stmt->bind_param('s', $studentId);

if ($stmt->execute()) {
    // Delete the used token
    $stmt2 = $conn->prepare("DELETE FROM email_verifications WHERE student_id = ?");
    $stmt2->bind_param('s', $studentId);
    $stmt2->execute();
    
    // Get student details and notify coordinator
    $student = getStudentById($conn, $studentId);
    if ($student) {
        notifyCoordinator($conn, $student);
    }
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Email Verified - OJT Monitoring System</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../assets/css/student-auth.css">
    </head>
    <body>
        <div class="card verified-card">
            <div class="icon">✅</div>
            <h2 class="fw-bold mb-3">Email Verified!</h2>
            <p class="text-muted">Your email has been successfully verified.</p>
            <p class="text-muted">Your account is now <strong>pending approval</strong> from your coordinator.</p>
            <p class="text-muted">You will receive a notification once your account is approved.</p>
            <a href="/ojt1/index.php" class="btn btn-primary w-100">Go to Login</a>
        </div>
    </body>
    </html>
    <?php
} else {
    echo "Failed to verify email. Please try again.";
}
?>