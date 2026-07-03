<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['coordinator_logged_in']) || $_SESSION['coordinator_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not authorized.']);
    exit;
}

$coordinatorId = $_SESSION['coordinator_id'] ?? null;
if (!$coordinatorId) {
    echo json_encode(['success' => false, 'message' => 'Coordinator ID missing.']);
    exit;
}

$studentId = trim($_POST['student_id'] ?? '');
$locationName = trim($_POST['location_name'] ?? '');
$locationAddress = trim($_POST['location_address'] ?? '');
$lat = trim($_POST['lat'] ?? '');
$lng = trim($_POST['lng'] ?? '');

if (!$studentId || !$locationName || !$lat || !$lng) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

require_once __DIR__ . '/../dbconnection.php';

try {
    /** @var \PDOStatement|false $stmt */
    $stmt = $conn->prepare("SELECT s.student_id FROM students s
        LEFT JOIN coordinator_department_assignments cda ON s.department = cda.department AND cda.coordinator_id = ?
        WHERE s.student_id = ?");
    if ($stmt) {
        $stmt->execute([$coordinatorId, $studentId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $student = null;
    }

    if (!$student) {
        echo json_encode(['success' => false, 'message' => 'Student not found or not in your department.']);
        exit;
    }

    /** @var \PDOStatement|false $stmt */
    $stmt = $conn->prepare("SELECT id FROM coordinator_student_assignments WHERE coordinator_id = ? AND student_id = ?");
    if ($stmt) {
        $stmt->execute([$coordinatorId, $studentId]);
    /** @var array<string,mixed>|false $existing */
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if (is_array($existing) && isset($existing['id'])) {
            /** @var \PDOStatement|false $stmt */
            $stmt = $conn->prepare("UPDATE coordinator_student_assignments SET location = ?, lat = ?, lng = ?, assigned_date = CURRENT_TIMESTAMP WHERE id = ?");
            if ($stmt) {
                $stmt->execute([$locationName, floatval($lat), floatval($lng), $existing['id']]);
            }
        } else {
            /** @var \PDOStatement|false $stmt */
            $stmt = $conn->prepare("INSERT INTO coordinator_student_assignments (coordinator_id, student_id, assigned_date, location, lat, lng) VALUES (?, ?, CURRENT_TIMESTAMP, ?, ?, ?)");
            if ($stmt) {
                $stmt->execute([$coordinatorId, $studentId, $locationName, floatval($lat), floatval($lng)]);
            }
        }
    }

    echo json_encode(['success' => true, 'message' => 'Student assigned to location successfully.']);
    exit;
} catch (Exception $e) {
    error_log('Assign location error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
