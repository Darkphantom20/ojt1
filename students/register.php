<?php
// /ojt1/students/register.php

session_start();
require_once '../csrf_protection.php';
require_once '../email_verification.php';

$csrfToken = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($csrfToken)) {
    header('Location: /ojt1/index.php?student_reg_failed=1');
    exit;
}

function sanitizeInput(string $value): string
{
    return trim(str_replace(['<', '>', '"', "'", '`'], '', $value));
}

function isValidStudentIdFormat(string $studentId): bool
{
    return preg_match('/^TC-\d{2}-[A-Z]-\d{5}$/', strtoupper($studentId)) === 1;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /ojt1/index.php');
    exit;
}

$studentId = strtoupper(sanitizeInput($_POST['student_id'] ?? ''));
$name = sanitizeInput($_POST['name'] ?? '');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$department = sanitizeInput($_POST['department'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validate all fields are filled
if (empty($studentId) || empty($name) || empty($email) || empty($department) || empty($password) || empty($confirmPassword)) {
    header('Location: /ojt1/index.php?student_reg_failed=1');
    exit;
}

// Validate student ID format
if (!isValidStudentIdFormat($studentId)) {
    header('Location: /ojt1/index.php?student_reg_failed=3');
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: /ojt1/index.php?student_reg_failed=1');
    exit;
}

// Validate password
if ($password !== $confirmPassword) {
    header('Location: /ojt1/index.php?student_reg_failed=1');
    exit;
}

if (strlen($password) < 8) {
    header('Location: /ojt1/index.php?student_reg_failed=1');
    exit;
}

// Get required OJT hours for department
$requiredOjtHours = 0;
require_once __DIR__ . '/../dbconnection.php';

if (!empty($department)) {
    $stmt = $conn->prepare('SELECT required_hours FROM department_required_hours WHERE department = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('s', $department);
        $stmt->execute();

        if (method_exists($stmt, 'get_result')) {
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            if ($row) {
                $requiredOjtHours = intval($row['required_hours']);
            }
        } else {
            $stmt->bind_result($requiredHours);
            if ($stmt->fetch()) {
                $requiredOjtHours = intval($requiredHours);
            }
        }
        $stmt->close();
    }
}

$normalizedStudentId = strtoupper(trim($studentId));

// Check if student ID already exists
$stmt = $conn->prepare("SELECT id FROM students WHERE student_id = ? LIMIT 1");
if ($stmt) {
    $stmt->bind_param('s', $normalizedStudentId);
    $stmt->execute();

    $idExists = false;
    if (method_exists($stmt, 'get_result')) {
        $result = $stmt->get_result();
        $idExists = $result && $result->num_rows > 0;
    } else {
        $stmt->bind_result($existingId);
        $idExists = $stmt->fetch();
    }
    $stmt->close();

    if ($idExists) {
        header('Location: /ojt1/index.php?student_reg_failed=2');
        exit;
    }
}

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM students WHERE email = ? LIMIT 1");
if ($stmt) {
    $stmt->bind_param('s', $email);
    $stmt->execute();

    $emailExists = false;
    if (method_exists($stmt, 'get_result')) {
        $result = $stmt->get_result();
        $emailExists = $result && $result->num_rows > 0;
    } else {
        $stmt->bind_result($existingId);
        $emailExists = $stmt->fetch();
    }
    $stmt->close();

    if ($emailExists) {
        header('Location: /ojt1/index.php?student_reg_failed=4');
        exit;
    }
}

// ============================================
// INSERT STUDENT WITH PENDING_VERIFICATION STATUS
// ============================================
$registrationStatus = 'pending_verification';
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO students (student_id, password_hash, name, email, department, required_ojt_hours, avatar, registration_status, enrolled_at) VALUES (?, ?, ?, ?, ?, ?, '', ?, NOW())");
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    header('Location: /ojt1/index.php?student_reg_failed=1');
    exit;
}

$stmt->bind_param('sssssis', $normalizedStudentId, $hashedPassword, $name, $email, $department, $requiredOjtHours, $registrationStatus);
$success = $stmt->execute();

if (!$success) {
    error_log("Registration failed: " . $stmt->error);
    header('Location: /ojt1/index.php?student_reg_failed=1');
    exit;
}
$stmt->close();

// ============================================
// GENERATE AND STORE VERIFICATION TOKEN
// ============================================
$token = generateVerificationToken();
storeVerificationToken($conn, $normalizedStudentId, $token, 24);

// ============================================
// SEND VERIFICATION EMAIL
// ============================================
$emailSent = sendVerificationEmail($conn, $normalizedStudentId);

if (!$emailSent) {
    error_log("Verification email failed to send for student: {$normalizedStudentId}");
    // Still redirect to success, but they'll need to request resend
}

regenerate_csrf_token();

// Redirect with success message
header('Location: /ojt1/index.php?student_reg_success=1');
exit;
?>