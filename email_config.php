<?php
// /ojt1/email_config.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer classes
$phpmailerPath = __DIR__ . '/PHPMailer/src/';
if (!file_exists($phpmailerPath . 'Exception.php')) {
    $phpmailerPath = __DIR__ . '/vendor/PHPMailer/src/';
}
require_once $phpmailerPath . 'Exception.php';
require_once $phpmailerPath . 'PHPMailer.php';
require_once $phpmailerPath . 'SMTP.php';

// Load mail configuration
$mailConfig = require_once __DIR__ . '/config/mail_ojt.php';

// ============================================
// YOUR NGROK URL - Change this to match your ngrok URL
// ============================================
$BASE_URL = 'https://spoon-hunchback-wrecker.ngrok-free.dev/ojt1';

/**
 * Get the base URL
 */
function getBaseUrl(): string {
    global $BASE_URL;
    return $BASE_URL;
}

/**
 * Send email using PHPMailer
 */
function sendEmail(string $to, string $subject, string $body, string $altBody = ''): bool {
    global $mailConfig;
    
    $mail = new PHPMailer(true);
    
    try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host       = $mailConfig['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $mailConfig['username'];
        $mail->Password   = $mailConfig['password'];
        $mail->SMTPSecure = $mailConfig['encryption'];
        $mail->Port       = $mailConfig['port'];
        
        $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
        $mail->addAddress($to);
        
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Get student by ID
 */
function getStudentById(mysqli $conn, string $studentId): ?array {
    $stmt = $conn->prepare("SELECT student_id, name, email, department, registration_status FROM students WHERE student_id = ?");
    $stmt->bind_param('s', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
?>