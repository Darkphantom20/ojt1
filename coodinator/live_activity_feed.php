<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once __DIR__ . '/../dbconnection.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['coordinator_logged_in']) || $_SESSION['coordinator_logged_in'] !== true) {
    echo json_encode(['error' => 'Not authorized']);
    exit;
}

$coordinatorId = $_SESSION['coordinator_id'] ?? null;
if (!$coordinatorId) {
    echo json_encode(['error' => 'Coordinator not found']);
    exit;
}

function formatExportTime(int $timestamp): string
{
    return date('M d, Y h:i A', $timestamp);
}

$feed = [];
$limit = !empty($_GET['all']) ? 100 : 7;

try {
    $createTableSql = "CREATE TABLE IF NOT EXISTS csv_export_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        coordinator_id INT NOT NULL,
        exported_at DATETIME NOT NULL,
        department VARCHAR(255) DEFAULT NULL,
        filters TEXT DEFAULT NULL,
        filename VARCHAR(255) DEFAULT NULL,
        rows_exported INT DEFAULT 0,
        user_agent VARCHAR(255) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->query($createTableSql);

    $feedStmt = $conn->prepare(
        "SELECT exported_at, department, filters, filename, rows_exported
         FROM csv_export_history
         WHERE coordinator_id = ?
         ORDER BY exported_at DESC
         LIMIT ?"
    );

    if ($feedStmt) {
        $feedStmt->bind_param('ii', $coordinatorId, $limit);
        $feedStmt->execute();
        $result = $feedStmt->get_result();

        while ($entry = $result->fetch_assoc()) {
            $time = strtotime($entry['exported_at']);
            if ($time <= 0) {
                continue;
            }

            $departmentLabel = 'All Departments';
            if (!empty($entry['department']) && $entry['department'] !== 'all') {
                $departmentLabel = $entry['department'];
            }

            $filters = [];
            if (!empty($entry['filters'])) {
                $decoded = json_decode($entry['filters'], true);
                if (is_array($decoded)) {
                    if (!empty($decoded['search'])) {
                        $filters[] = 'Search: ' . $decoded['search'];
                    }
                    if (!empty($decoded['department']) && $decoded['department'] !== 'all') {
                        $filters[] = 'Dept: ' . $decoded['department'];
                    }
                }
            }

            $details = $entry['rows_exported'] . ' row' . ($entry['rows_exported'] === 1 ? '' : 's');
            if (!empty($filters)) {
                $details .= ' · ' . implode(' · ', $filters);
            }

            if (!empty($entry['filename'])) {
                $details .= ' · ' . $entry['filename'];
            }

            $feed[] = [
                'student' => $departmentLabel,
                'event' => 'Exported Excel report',
                'details' => trim($details),
                'time' => formatExportTime($time),
                'icon' => 'fas fa-file-excel bg-success'
            ];
        }

        $feedStmt->close();
    }
} catch (Exception $e) {
    error_log('Live export history polling query failed: ' . $e->getMessage());
}

echo json_encode(['feed' => $feed]);
