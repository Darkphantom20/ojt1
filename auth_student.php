<?php
session_start();
require_once 'dbconnection.php';
require_once 'csrf_protection.php';

function sanitizeInput(string $value): string
{
    return trim(str_replace(['<', '>', '"', "'", '`'], '', $value));
}

function isValidStudentIdFormat(string $studentId): bool
{
    return preg_match('/^TC-\d{2}-[A-Z]-\d{5}$/', strtoupper($studentId)) === 1;
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($csrfToken)) {
    header('Location: index.php?student_login_failed=8');
    exit;
}

$studentId = isset($_POST['student_id']) ? strtoupper(sanitizeInput($_POST['student_id'])) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if (!isValidStudentIdFormat($studentId) || empty($password)) {
    header('Location: index.php?student_login_failed=1');
    exit;
}

$stmt = $conn->prepare("SELECT id, student_id, password_hash, name, email, department, required_ojt_hours, avatar, registration_status FROM students WHERE student_id = ? LIMIT 1");
if (!$stmt) {
    header('Location: index.php?student_login_failed=1');
    exit;
}
$stmt->bind_param('s', $studentId);
$stmt->execute();

$student = null;
if (method_exists($stmt, 'get_result')) {
    $result = $stmt->get_result();
    $student = $result ? $result->fetch_assoc() : null;
} else {
    $stmt->bind_result($id, $dbStudentId, $passwordHash, $name, $email, $department, $requiredOjtHours, $avatar, $registrationStatus);
    if ($stmt->fetch()) {
        $student = [
            'id' => $id,
            'student_id' => $dbStudentId,
            'password_hash' => $passwordHash,
            'name' => $name,
            'email' => $email,
            'department' => $department,
            'required_ojt_hours' => $requiredOjtHours,
            'avatar' => $avatar,
            'registration_status' => $registrationStatus,
        ];
    }
}

// ============================================
// CHECK REGISTRATION STATUS - CORRECT ORDER
// ============================================
if ($student) {
    // FIRST: Check if email is verified (pending_verification)
    // This is the INITIAL state after registration
    if ($student['registration_status'] === 'pending_verification') {
        // Student hasn't verified their email yet
        header('Location: index.php?student_login_failed=7&student_id=' . urlencode($studentId));
        exit;
    }
    
    // SECOND: Check if pending approval from coordinator
    if ($student['registration_status'] === 'pending') {
        // Student verified email but waiting for coordinator approval
        header('Location: index.php?student_login_failed=5');
        exit;
    }
    
    // THIRD: Check if rejected
    if ($student['registration_status'] === 'rejected') {
        header('Location: index.php?student_login_failed=6');
        exit;
    }
    
    // FOURTH: Check if approved - proceed to password verification
    if ($student['registration_status'] === 'approved') {
        // Verify password
        if (password_verify($password, $student['password_hash'])) {
            $_SESSION['student_logged_in'] = true;
            $_SESSION['student_id'] = $student['student_id'];
            $_SESSION['student_name'] = $student['name'];
            $_SESSION['student_email'] = $student['email'];
            $_SESSION['student_department'] = $student['department'];
            $_SESSION['student_required_ojt_hours'] = $student['required_ojt_hours'];
            $_SESSION['student_avatar'] = $student['avatar'] ?? '';
            $_SESSION['user_name'] = $student['name'];
            $_SESSION['user_email'] = $student['email'];

            regenerate_csrf_token();

            header('Location: students/dashboard.php');
            exit;
        }
    }
}

// If we get here, login failed
header('Location: index.php?student_login_failed=1');
exit;
?>