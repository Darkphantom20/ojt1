<?php
// /ojt1/email_verification.php

require_once __DIR__ . '/email_config.php';

/**
 * Generate a secure verification token
 */
function generateVerificationToken(int $length = 64): string {
    return bin2hex(random_bytes($length));
}

/**
 * Store verification token in database
 */
function storeVerificationToken(mysqli $conn, string $studentId, string $token, int $expiryHours = 24): bool {
    $expiry = date('Y-m-d H:i:s', strtotime("+{$expiryHours} hours"));
    
    $stmt = $conn->prepare("DELETE FROM email_verifications WHERE student_id = ?");
    $stmt->bind_param('s', $studentId);
    $stmt->execute();
    
    $stmt = $conn->prepare("INSERT INTO email_verifications (student_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $studentId, $token, $expiry);
    return $stmt->execute();
}

/**
 * Verify token and get student ID
 */
function verifyEmailToken(mysqli $conn, string $token) {
    $stmt = $conn->prepare("SELECT student_id FROM email_verifications WHERE token = ? AND expires_at > NOW()");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['student_id'];
    }
    return false;
}

/**
 * Send verification email to student
 */
function sendVerificationEmail(mysqli $conn, string $studentId): bool {
    $student = getStudentById($conn, $studentId);
    if (!$student) {
        return false;
    }
    
    $token = generateVerificationToken();
    storeVerificationToken($conn, $studentId, $token, 24);
    
    $baseUrl = getBaseUrl();
    $verificationLink = $baseUrl . "/students/verify_email.php?token=" . urlencode($token);
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #0d6efd; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { padding: 20px; background: #f8f9fa; border-radius: 0 0 8px 8px; }
            .button { display: inline-block; padding: 12px 24px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px; }
            .button:hover { background: #0a58ca; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            .code-block { background: #e9ecef; padding: 10px; border-radius: 5px; word-break: break-all; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>📧 Verify Your Email</h2>
                <p>OJT Monitoring System</p>
            </div>
            <div class='content'>
                <p>Hello <strong>{$student['name']}</strong>,</p>
                <p>Thank you for registering. Please verify your email address by clicking the button below:</p>
                <p style='text-align: center;'>
                    <a href='{$verificationLink}' class='button'>Verify Email Address</a>
                </p>
                <p>If the button doesn't work, copy and paste this link into your browser:</p>
                <div class='code-block'>{$verificationLink}</div>
                <p style='margin-top: 15px;'><strong>⏰ This link will expire in 24 hours.</strong></p>
                <p>If you did not create this account, please ignore this email.</p>
            </div>
            <div class='footer'>
                &copy; " . date('Y') . " OJT Monitoring System. All rights reserved.
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($student['email'], 'Verify Your Email - OJT Monitoring System', $body);
}
?>