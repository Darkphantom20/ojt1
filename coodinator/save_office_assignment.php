<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../dbconnection.php';

if (empty($_SESSION['coordinator_logged_in']) || $_SESSION['coordinator_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

$coordinatorId = $_SESSION['coordinator_id'] ?? null;
if (!$coordinatorId) {
    echo json_encode(['success' => false, 'message' => 'Coordinator missing']);
    exit;
}

$response = ['success' => true, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'undeploy') {
        $studentId = trim($_POST['student_id'] ?? '');
        if (!$studentId) {
            echo json_encode(['success' => false, 'message' => 'Student ID required']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM coordinator_office_assignments WHERE coordinator_id = ? AND student_id = ?");
        $stmt->bind_param('is', $coordinatorId, $studentId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $response['success'] = true;
            $response['message'] = 'Student undeployed successfully';
        } else {
            $response['success'] = false;
            $response['message'] = 'Student assignment not found';
        }

        $stmt = $conn->prepare("SELECT coa.student_id, s.name AS student_name, coa.location_name, coa.location_address, coa.lat, coa.lng, coa.assigned_date
                        FROM coordinator_office_assignments coa
                        JOIN students s ON coa.student_id = s.student_id
                        WHERE coa.coordinator_id = ?
                        ORDER BY coa.assigned_date DESC");
        $stmt->bind_param('i', $coordinatorId);
        $stmt->execute();
        $result = $stmt->get_result();
        $assignments = [];
        while ($row = $result->fetch_assoc()) {
            $assignments[] = $row;
        }
        $response['assignments'] = $assignments;
        echo json_encode($response);
        exit;
    }

    
    $studentIdsJson = $_POST['student_ids'] ?? '';
    $isBatch = !empty($studentIdsJson);
    
    if ($isBatch) {
        
        $studentIds = json_decode($studentIdsJson, true);
        if (!is_array($studentIds) || empty($studentIds)) {
            echo json_encode(['success' => false, 'message' => 'Invalid student list']);
            exit;
        }
        
        $locationName = trim($_POST['location_name'] ?? '');
        $locationAddress = trim($_POST['location_address'] ?? '');
        $lat = trim($_POST['lat'] ?? '');
        $lng = trim($_POST['lng'] ?? '');

        if (!$locationName || $lat === '' || $lng === '') {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        $deployed = 0;
        $failed = 0;
        
        foreach ($studentIds as $studentId) {
            $studentId = trim($studentId);
            if (empty($studentId)) continue;
            
            
            $stmt = $conn->prepare("SELECT student_id FROM students WHERE student_id = ? LIMIT 1");
            $stmt->bind_param('s', $studentId);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows === 0) {
                $failed++;
                continue;
            }

            
            $stmt2 = $conn->prepare("SELECT id FROM coordinator_office_assignments WHERE coordinator_id = ? AND student_id = ?");
            $stmt2->bind_param('is', $coordinatorId, $studentId);
            $stmt2->execute();
            $result = $stmt2->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $stmt3 = $conn->prepare("UPDATE coordinator_office_assignments SET location_name = ?, location_address = ?, lat = ?, lng = ?, assigned_date = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt3->bind_param('ssddi', $locationName, $locationAddress, $lat, $lng, $row['id']);
                $stmt3->execute();
                $deployed++;
            } else {
                $stmt3 = $conn->prepare("INSERT INTO coordinator_office_assignments (coordinator_id, student_id, location_name, location_address, lat, lng) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt3->bind_param('isssdd', $coordinatorId, $studentId, $locationName, $locationAddress, $lat, $lng);
                $stmt3->execute();
                $deployed++;
            }
        }
        
        $response['success'] = true;
        $response['message'] = "Deployed $deployed student(s)";
        $response['deployed_count'] = $deployed;
        $response['failed_count'] = $failed;

        $stmt = $conn->prepare("SELECT coa.student_id, s.name AS student_name, coa.location_name, coa.location_address, coa.lat, coa.lng, coa.assigned_date
                        FROM coordinator_office_assignments coa
                        JOIN students s ON coa.student_id = s.student_id
                        WHERE coa.coordinator_id = ?
                        ORDER BY coa.assigned_date DESC");
        $stmt->bind_param('i', $coordinatorId);
        $stmt->execute();
        $result = $stmt->get_result();
        $assignments = [];
        while ($row = $result->fetch_assoc()) {
            $assignments[] = $row;
        }
        $response['assignments'] = $assignments;
        echo json_encode($response);
        exit;
    }
    
    
    $studentId = trim($_POST['student_id'] ?? '');
    $locationName = trim($_POST['location_name'] ?? '');
    $locationAddress = trim($_POST['location_address'] ?? '');
    $lat = trim($_POST['lat'] ?? '');
    $lng = trim($_POST['lng'] ?? '');

    if (!$studentId || !$locationName || $lat === '' || $lng === '') {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    
    $stmt = $conn->prepare("SELECT student_id FROM students WHERE student_id = ? LIMIT 1");
    $stmt->bind_param('s', $studentId);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        exit;
    }

    $stmt = $conn->prepare("SELECT id FROM coordinator_office_assignments WHERE coordinator_id = ? AND student_id = ?");
    $stmt->bind_param('is', $coordinatorId, $studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt = $conn->prepare("UPDATE coordinator_office_assignments SET location_name = ?, location_address = ?, lat = ?, lng = ?, assigned_date = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param('ssddi', $locationName, $locationAddress, $lat, $lng, $row['id']);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO coordinator_office_assignments (coordinator_id, student_id, location_name, location_address, lat, lng) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('isssdd', $coordinatorId, $studentId, $locationName, $locationAddress, $lat, $lng);
        $stmt->execute();
    }

    $response['message'] = 'Student deployed successfully';
}

$stmt = $conn->prepare("SELECT coa.student_id, s.name AS student_name, coa.location_name, coa.location_address, coa.lat, coa.lng, coa.assigned_date
                        FROM coordinator_office_assignments coa
                        JOIN students s ON coa.student_id = s.student_id
                        WHERE coa.coordinator_id = ?
                        ORDER BY coa.assigned_date DESC");
$stmt->bind_param('i', $coordinatorId);
$stmt->execute();
$result = $stmt->get_result();
$assignments = [];
while ($row = $result->fetch_assoc()) {
    $assignments[] = $row;
}

$response['assignments'] = $assignments;
echo json_encode($response);
