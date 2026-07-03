<?php
// /ojt1/email_notification.php

require_once __DIR__ . '/email_config.php';

/**
 * Notify coordinator about new student registration
 */
function notifyCoordinator(mysqli $conn, array $student): bool {
    $stmt = $conn->prepare("
        SELECT ca.email, ca.full_name 
        FROM coordinator_accounts ca
        JOIN coordinator_department_assignments cda ON ca.id = cda.coordinator_id
        WHERE cda.department = ? AND ca.status = 'active'
        LIMIT 1
    ");
    $stmt->bind_param('s', $student['department']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($coordinator = $result->fetch_assoc()) {
        $baseUrl = getBaseUrl();
        $subject = "New Student Registration - " . $student['name'];
        $body = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #0d6efd; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { padding: 20px; background: #f8f9fa; border-radius: 0 0 8px 8px; }
                .details { background: #e9ecef; padding: 15px; border-radius: 8px; margin: 15px 0; }
                .button { display: inline-block; padding: 12px 24px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>📋 New Student Registration</h2>
                    <p>OJT Monitoring System</p>
                </div>
                <div class='content'>
                    <p>Hello <strong>{$coordinator['full_name']}</strong>,</p>
                    <p>A new student has registered and needs your approval:</p>
                    <div class='details'>
                        <p><strong>👤 Name:</strong> {$student['name']}</p>
                        <p><strong>🆔 Student ID:</strong> {$student['student_id']}</p>
                        <p><strong>🏫 Department:</strong> {$student['department']}</p>
                        <p><strong>📧 Email:</strong> {$student['email']}</p>
                        <p><strong>✅ Status:</strong> <span style='color: #ffc107;'>Pending Approval</span></p>
                    </div>
                    <p>Please log in to review and approve this registration.</p>
                    <p style='text-align: center;'>
                        <a href='{$baseUrl}/coodinator/pending_approvals.php' class='button'>📋 Review Approvals</a>
                    </p>
                </div>
                <div class='footer'>
                    &copy; " . date('Y') . " OJT Monitoring System. All rights reserved.
                </div>
            </div>
        </body>
        </html>
        ";
        
        return sendEmail($coordinator['email'], $subject, $body);
    }
    return false;
}

/**
 * Send approval email to student
 */
function sendApprovalEmail(mysqli $conn, string $studentId): bool {
    $student = getStudentById($conn, $studentId);
    if (!$student) {
        return false;
    }
    
    $baseUrl = getBaseUrl();
    $subject = "✅ Your OJT Account Has Been Approved";
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #28a745; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { padding: 20px; background: #f8f9fa; border-radius: 0 0 8px 8px; }
            .button { display: inline-block; padding: 12px 24px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px; }
            .button:hover { background: #0a58ca; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            .login-details {
                background: #e9ecef;
                padding: 15px;
                border-radius: 8px;
                margin: 15px 0;
            }
            .login-details p {
                margin: 5px 0;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>✅ Account Approved!</h2>
                <p>OJT Monitoring System</p>
            </div>
            <div class='content'>
                <p>Hello <strong>{$student['name']}</strong>,</p>
                <p>We are pleased to inform you that your OJT account has been <strong style='color: #28a745;'>approved</strong> by your coordinator!</p>
                <p>You can now log in and start tracking your OJT hours.</p>
                <div class='login-details'>
                    <p><strong>🔑 Your Login Details:</strong></p>
                    <p><strong>Student ID:</strong> {$student['student_id']}</p>
                    <p><strong>Password:</strong> <i>(the password you set during registration)</i></p>
                </div>
                <p style='text-align: center;'>
                    <a href='{$baseUrl}/index.php' class='button'>🔐 Log In Now</a>
                </p>
                <p style='margin-top: 15px;'>If you have any questions, please contact your department coordinator.</p>
            </div>
            <div class='footer'>
                &copy; " . date('Y') . " OJT Monitoring System. All rights reserved.
            </div>
        </body>
        </html>
        ";
    
    return sendEmail($student['email'], $subject, $body);
}

/**
 * Send rejection email to student
 */
function sendRejectionEmail(mysqli $conn, string $studentId, string $reason = ''): bool {
    $student = getStudentById($conn, $studentId);
    if (!$student) {
        return false;
    }
    
    $subject = "❌ Your OJT Account Registration Update";
    $reasonText = $reason ? "Reason: {$reason}" : "Please contact your coordinator for more information.";
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { padding: 20px; background: #f8f9fa; border-radius: 0 0 8px 8px; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>❌ Registration Update</h2>
                <p>OJT Monitoring System</p>
            </div>
            <div class='content'>
                <p>Hello <strong>{$student['name']}</strong>,</p>
                <p>Your OJT account registration has been <strong style='color: #dc3545;'>rejected</strong> by your coordinator.</p>
                <p><strong>{$reasonText}</strong></p>
                <p>Please contact your department coordinator if you have any questions.</p>
            </div>
            <div class='footer'>
                &copy; " . date('Y') . " OJT Monitoring System. All rights reserved.
            </div>
        </body>
        </html>
        ";
    
    return sendEmail($student['email'], $subject, $body);
}
?>