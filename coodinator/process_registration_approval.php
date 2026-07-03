<?php
// /ojt1/coodinator/process_registration_approval.php

session_start();
ob_start();
require_once __DIR__ . '/../includes/header.php';
ob_end_clean();
require_once __DIR__ . '/../dbconnection.php';
require_once __DIR__ . '/../email_notification.php'; // Changed

header('Content-Type: application/json');

if (!isset($_SESSION['coordinator_logged_in']) || $_SESSION['coordinator_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

$coordinatorDepartment = $_SESSION['coordinator_department'] ?? '';

$studentId = isset($_POST['student_id']) ? trim($_POST['student_id']) : '';
$action = isset($_POST['action']) ? trim($_POST['action']) : '';
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

if (empty($studentId) || empty($action) || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Get student details
$stmt = $conn->prepare("
    SELECT id, student_id, name, email, department, registration_status 
    FROM students 
    WHERE student_id = ? 
    LIMIT 1
");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$stmt->bind_param('s', $studentId);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    echo json_encode(['success' => false, 'message' => 'Student not found']);
    exit;
}

// Check if student belongs to coordinator's department
if (!isDepartmentInCollege($student['department'], $coordinatorDepartment) && strcasecmp(trim($student['department']), trim($coordinatorDepartment)) !== 0) {
    echo json_encode(['success' => false, 'message' => 'You can only approve students from your department or college']);
    exit;
}

if ($student['registration_status'] !== 'pending') {
    echo json_encode(['success' => false, 'message' => 'This student registration is no longer pending']);
    exit;
}

// Set new status
if ($action === 'approve') {
    $newStatus = 'approved';
} else {
    $newStatus = 'rejected';
}

// Update student status
$updateStmt = $conn->prepare("
    UPDATE students 
    SET registration_status = ? 
    WHERE student_id = ? 
    LIMIT 1
");

if (!$updateStmt) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$updateStmt->bind_param('ss', $newStatus, $studentId);
$success = $updateStmt->execute();
$updateStmt->close();

if ($success) {
    // ============================================
    // SEND EMAIL NOTIFICATION TO STUDENT
    // ============================================
    if ($action === 'approve') {
        sendApprovalEmail($conn, $studentId);
    } else {
        sendRejectionEmail($conn, $studentId, $reason);
    }
    
    echo json_encode([
        'success' => true, 
        'message' => $action === 'approve' ? 'Student approved successfully' : 'Student registration rejected'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update student status']);
}
?>