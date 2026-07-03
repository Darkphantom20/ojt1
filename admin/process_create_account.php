<?php
// admin/process_create_account.php
session_start();
require_once __DIR__ . '/../dbconnection.php';
require_once __DIR__ . '/../csrf_protection.php';

// ================================================================
// 1. Use PHPMailer classes
// ================================================================
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ================================================================
// 2. Attempt to include PHPMailer files
// ================================================================
$phpmailerAvailable = false;

$phpmailerPath = __DIR__ . '/../PHPMailer/src/PHPMailer.php';
if (file_exists($phpmailerPath)) {
    require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
    require_once __DIR__ . '/../PHPMailer/src/Exception.php';
    $phpmailerAvailable = true;
} else {
    $phpmailerPathLower = __DIR__ . '/../phpmailer/src/PHPMailer.php';
    if (file_exists($phpmailerPathLower)) {
        require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
        require_once __DIR__ . '/../phpmailer/src/SMTP.php';
        require_once __DIR__ . '/../phpmailer/src/Exception.php';
        $phpmailerAvailable = true;
    } else {
        error_log("PHPMailer not found at: " . $phpmailerPath);
    }
}

// ================================================================
// 3. CSRF Protection
// ================================================================
$csrfToken = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($csrfToken)) {
    header('Location: coordinator_access.php?error=csrf');
    exit;
}

// ================================================================
// 4. Access Code Generator
// ================================================================
function generateCoordinatorCode() {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $part1 = '';
    $part2 = '';
    for ($i = 0; $i < 4; $i++) {
        $part1 .= $chars[random_int(0, strlen($chars) - 1)];
        $part2 .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return 'COORD-' . $part1 . '-' . $part2;
}

// ================================================================
// 5. Get and sanitize input
// ================================================================
$fullName   = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$email      = isset($_POST['email']) ? trim($_POST['email']) : '';
$normalizedEmail = strtolower($email);
$department = isset($_POST['department']) ? trim($_POST['department']) : 'General';
$password   = isset($_POST['password']) ? $_POST['password'] : '';

$validDepartments = [
    'Education',
    'College of Arts',
    'College of Agriculture & Forestry',
    'College of Business & Management',
    'College of Computing Studies',
    'College of Criminology',
];
if (!in_array($department, $validDepartments, true)) {
    $normalizedDepartment = strtolower(trim($department));
    $matched = array_filter($validDepartments, function ($dep) use ($normalizedDepartment) {
        return strtolower($dep) === $normalizedDepartment;
    });
    if (count($matched) > 0) {
        $department = array_values($matched)[0];
    } elseif ($department === '') {
        $department = 'Education';
    } else {
        $department = trim($department);
    }
}

if (!$fullName || !$email || !$password) {
    header('Location: coordinator_access.php?error=missing');
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: coordinator_access.php?error=' . urlencode('Invalid email address.'));
    exit;
}

// ================================================================
// 6. Helper for redirects
// ================================================================
function redirectWithError(string $message): void {
    header('Location: coordinator_access.php?error=' . urlencode($message));
    exit;
}

// ================================================================
// 7. Generate access code and hash password
// ================================================================
$accessCode = generateCoordinatorCode();
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// ================================================================
// 8. Check for duplicate email
// ================================================================
$checkStmt = $conn->prepare("SELECT id FROM coordinator_accounts WHERE LOWER(email) = LOWER(?) LIMIT 1");
if ($checkStmt === false) {
    redirectWithError('Database error. Please try again.');
}
$checkStmt->bind_param('s', $normalizedEmail);
$checkStmt->execute();
$checkStmt->store_result();
if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    redirectWithError('Email already exists (duplicate coordinator).');
}
$checkStmt->close();

// ================================================================
// 9. Insert coordinator into database
// ================================================================
$insertError = '';
$success = false;
$attempts = 0;
while ($attempts < 5 && !$success) {
    $stmt = $conn->prepare("INSERT INTO coordinator_accounts (full_name, email, password_hash, access_code, department, status) VALUES (?, ?, ?, ?, ?, 'unused')");
    if ($stmt === false) {
        $insertError = $conn->error ?: 'prepare_failed';
        break;
    }
    $stmt->bind_param('sssss', $fullName, $email, $passwordHash, $accessCode, $department);
    $success = $stmt->execute();
    if ($success) {
        $stmt->close();
        break;
    }
    $insertError = $stmt->error;
    $stmt->close();

    if (stripos($insertError, 'Duplicate entry') !== false) {
        if (stripos($insertError, 'access_code') !== false || stripos($insertError, 'access code') !== false) {
            $accessCode = generateCoordinatorCode();
            $attempts++;
            continue;
        }
        if (stripos($insertError, 'email') !== false) {
            redirectWithError('Email already exists (duplicate coordinator).');
        }
    }
    break;
}

if (!$success) {
    if (stripos($insertError, 'Duplicate entry') !== false && stripos($insertError, 'email') !== false) {
        redirectWithError('Email already exists (duplicate coordinator).');
    }
    $error = 'insert_failed: ' . ($insertError ?: ($conn->error ?: 'unknown'));
    redirectWithError($error);
}

// ================================================================
// 10. Build login URL - HARDCODED TO YOUR NGROK URL
// ================================================================
$loginUrl = 'https://spoon-hunchback-wrecker.ngrok-free.dev/ojt1/index.php';

// ================================================================
// 11. Email content (presentable, button only)
// ================================================================
$emailBodyHtml = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Coordinator Account Created</title>
    <style>
        body, html { margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f0f4f8; width: 100%; }
        .container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.12); overflow: hidden; }
        .header { background: linear-gradient(135deg, #0d2b5e, #1a4a8a); padding: 35px 30px 25px; text-align: center; color: #ffffff; }
        .header .icon { font-size: 48px; margin-bottom: 8px; display: block; }
        .header h1 { margin: 0; font-size: 26px; font-weight: 700; letter-spacing: 0.5px; }
        .header .sub { font-size: 14px; opacity: 0.85; margin-top: 4px; font-weight: 300; letter-spacing: 2px; }
        .header .badge { display: inline-block; background: rgba(255,255,255,0.2); padding: 3px 16px; border-radius: 20px; font-size: 12px; margin-top: 8px; letter-spacing: 1px; font-weight: 600; }
        .content { padding: 32px 30px 20px; }
        .content h2 { color: #0d2b5e; font-size: 20px; margin-top: 0; margin-bottom: 6px; }
        .content .greeting { font-size: 16px; color: #333; margin-bottom: 18px; }
        .divider { height: 3px; background: linear-gradient(90deg, #0d2b5e, #4a90d9, #0d2b5e); margin: 20px 0; border-radius: 2px; }
        .details-card { background: #f7faff; border-radius: 10px; padding: 18px 22px; border: 1px solid #e2e8f0; margin: 16px 0; }
        .details-card .row { display: flex; padding: 8px 0; border-bottom: 1px solid #eef2f7; }
        .details-card .row:last-child { border-bottom: none; }
        .details-card .label { font-weight: 600; color: #1e293b; width: 40%; flex-shrink: 0; }
        .details-card .value { color: #0a1e3c; word-break: break-word; }
        .details-card .code { background: #e2eaf6; padding: 4px 14px; border-radius: 6px; font-family: 'Courier New', monospace; font-size: 18px; font-weight: 700; color: #0d2b5e; display: inline-block; letter-spacing: 1px; }
        .details-card .password-code { background: #eef2f7; padding: 2px 10px; border-radius: 4px; font-family: 'Courier New', monospace; font-size: 14px; }
        .btn-container { text-align: center; margin: 25px 0 10px; }
        .btn { display: inline-block; background: linear-gradient(135deg, #0d2b5e, #1a4a8a); color: #ffffff !important; padding: 14px 38px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 14px rgba(13,43,94,0.25); }
        .btn:hover { background: linear-gradient(135deg, #0a1f47, #143a6e); }
        .footer { background: #f8fafc; padding: 18px 25px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e9edf4; }
        .footer p { margin: 3px 0; }
        .footer .brand { color: #0d2b5e; font-weight: 600; letter-spacing: 1px; }
        .footer .small { font-size: 11px; color: #b0bed0; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <span class='icon'>📋</span>
            <h1>OJT Monitoring System</h1>
            <div class='sub'>Coordinator Access Portal</div>
            <span class='badge'>✦ R.A. 9852 · Philippines ✦</span>
        </div>
        <div class='content'>
            <h2>Welcome, <span style='color:#0d2b5e;'>{$fullName}</span> 👋</h2>
            <p class='greeting'>Your coordinator account has been created successfully. Please find your login credentials below.</p>
            <div class='divider'></div>
            <div class='details-card'>
                <div class='row'>
                    <span class='label'>📧 Email</span>
                    <span class='value'><strong>{$email}</strong></span>
                </div>
                <div class='row'>
                    <span class='label'>🔑 Temporary Password</span>
                    <span class='value'><code class='password-code'>{$password}</code></span>
                </div>
                <div class='row'>
                    <span class='label'>🔐 Access Code</span>
                    <span class='value'><span class='code'>{$accessCode}</span></span>
                </div>
                <div class='row'>
                    <span class='label'>🏛️ Department</span>
                    <span class='value'><strong>{$department}</strong></span>
                </div>
            </div>
            <div style='background:#fff8e1; border-left:4px solid #f5a623; padding:12px 16px; border-radius:4px; margin:16px 0;'>
                <p style='margin:0; font-size:14px; color:#5d4e37;'>
                    <strong>⚠️ Important:</strong> Please log in and change your password immediately.
                </p>
            </div>
            <div class='btn-container'>
                <a href='{$loginUrl}' class='btn'>🔗 Go to Login Portal</a>
            </div>
        </div>
        <div class='footer'>
            <p class='brand'>R.A. 9852 · Philippines</p>
            <p>This is an automated message from the OJT Monitoring System.</p>
            <p class='small'>Please do not reply to this email. For assistance, contact your system administrator.</p>
            <p class='small'>&copy; " . date('Y') . " OJT Monitoring System · All rights reserved.</p>
        </div>
    </div>
</body>
</html>
";

$emailPlainText = "=============================================\n" .
                  "OJT MONITORING SYSTEM - COORDINATOR ACCESS\n" .
                  "=============================================\n\n" .
                  "Hello {$fullName},\n\n" .
                  "Your coordinator account was created successfully.\n\n" .
                  "Your Login Credentials:\n" .
                  "-----------------------------------------\n" .
                  "Email                 : {$email}\n" .
                  "Temporary Password    : {$password}\n" .
                  "Coordinator Access Code: {$accessCode}\n" .
                  "Department            : {$department}\n" .
                  "-----------------------------------------\n\n" .
                  "Log in at: {$loginUrl}\n\n" .
                  "IMPORTANT: Please change your password immediately after logging in.\n\n" .
                  "Thank you,\n" .
                  "OJT Monitoring System\n" .
                  "R.A. 9852 · Philippines\n" .
                  "=============================================\n";

// ================================================================
// 12. Send email
// ================================================================
$mailSent = false;

if ($phpmailerAvailable && class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    try {
        $mailConfigFile = __DIR__ . '/../config/mail_ojt.php';
        if (!file_exists($mailConfigFile)) {
            throw new Exception('Mail config file not found.');
        }
        $mailConfig = require $mailConfigFile;

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $mailConfig['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $mailConfig['username'];
        $mail->Password   = $mailConfig['password'];
        $mail->SMTPSecure = $mailConfig['encryption'];
        $mail->Port       = $mailConfig['port'];

        $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
        $mail->addAddress($email, $fullName);

        $mail->isHTML(true);
        $mail->Subject = 'OJT Monitoring System - Coordinator Access Details';
        $mail->Body    = $emailBodyHtml;
        $mail->AltBody = $emailPlainText;

        $mail->send();
        $mailSent = true;
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $e->getMessage());
        $mailSent = false;
    }
}

if (!$mailSent) {
    $subject = 'OJT Monitoring System - Coordinator Access Details';
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: no-reply@yourdomain.com\r\n";
    $headers .= "Reply-To: no-reply@yourdomain.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    $mailSent = mail($email, $subject, $emailBodyHtml, $headers);
}

// ================================================================
// 13. Redirect back with status
// ================================================================
$redirect = 'coordinator_access.php?created=1&code=' . urlencode($accessCode);
if (!$mailSent) {
    $redirect .= '&mail=failed';
}
header('Location: ' . $redirect);
exit;