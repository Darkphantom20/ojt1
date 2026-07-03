<?php
session_start();
require_once __DIR__ . '/../dbconnection.php';
require_once __DIR__ . '/../csrf_protection.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrfToken)) {
        header('Location: faculty.php?error=' . urlencode('Security validation failed.'));
        exit;
    }
}

$action = $_REQUEST['action'] ?? 'create';
if (!in_array($action, ['create','edit','delete'])) {
    header('Location: faculty.php?error=' . urlencode('Invalid action.')); exit;
}

if ($action === 'delete') {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        header('Location: faculty.php?error=' . urlencode('Invalid faculty ID.')); exit;
    }

    $stmt = $conn->prepare('DELETE FROM coordinator_department_assignments WHERE coordinator_id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $conn->prepare('DELETE FROM coordinator_student_assignments WHERE coordinator_id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $conn->prepare('DELETE FROM coordinator_accounts WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        header('Location: faculty.php?success=' . urlencode('Coordinator account deleted.')); exit;
    }
    header('Location: faculty.php?error=' . urlencode('Unable to delete coordinator account.')); exit;
}

$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$department = trim($_POST['department'] ?? '');
if ($fullName === '' || $email === '' || $department === '') {
    header('Location: faculty.php?error=' . urlencode('All fields are required.')); exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: faculty.php?error=' . urlencode('Please enter a valid email address.')); exit;
}

if ($action === 'create') {
    $stmt = $conn->prepare('SELECT id FROM faculty_directory WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->fetch_assoc()) {
        header('Location: faculty.php?error=' . urlencode('Email is already in use by another faculty.')); exit;
    }
    $stmt = $conn->prepare('INSERT INTO faculty_directory (full_name, email, department) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $fullName, $email, $department);
    if ($stmt->execute()) {
        header('Location: faculty.php?success=' . urlencode('Faculty added successfully.')); exit;
    }
    header('Location: faculty.php?error=' . urlencode('Failed to create faculty.')); exit;
}

if ($action === 'edit') {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        header('Location: faculty.php?error=' . urlencode('Invalid faculty ID.')); exit;
    }
    $stmt = $conn->prepare('SELECT id FROM faculty_directory WHERE email = ? AND id != ? LIMIT 1');
    $stmt->bind_param('si', $email, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->fetch_assoc()) {
        header('Location: faculty.php?error=' . urlencode('Another faculty account is already using that email.')); exit;
    }
    $stmt = $conn->prepare('UPDATE faculty_directory SET full_name = ?, email = ?, department = ? WHERE id = ?');
    $stmt->bind_param('sssi', $fullName, $email, $department, $id);
    if ($stmt->execute()) {
        header('Location: faculty.php?success=' . urlencode('Faculty updated successfully.')); exit;
    }
    header('Location: faculty.php?error=' . urlencode('Failed to update faculty.')); exit;
}

header('Location: faculty.php?error=' . urlencode('Unknown operation.')); exit;
