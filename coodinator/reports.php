<?php
session_start();
date_default_timezone_set('Asia/Manila');

// ===== Include helper functions from header.php (to avoid undefined function error) =====
if (!function_exists('getDepartmentsForCollege')) {
    function getDepartmentsForCollege(string $college): array {
        $college = strtolower(trim($college));
        switch ($college) {
            case 'education':
            case 'college of education':
                return [
                    'Bachelor of Elementary Education (BEEd)',
                    'Bachelor of Physical Education (BPEd)',
                    'Bachelor of Secondary Education (BSEd) - Major in English',
                    'Bachelor of Secondary Education (BSEd) - Major in Filipino',
                    'Bachelor of Secondary Education (BSEd) - Major in Mathematics',
                    'Bachelor of Secondary Education (BSEd) - Major in Social Studies',
                ];
            case 'college of arts':
            case 'arts':
                return ['Bachelor of Arts in English Language Studies (BAELS)'];
            case 'college of agriculture & forestry':
            case 'college of agriculture':
            case 'agriculture & forestry':
            case 'agriculture':
                return [
                    'Bachelor of Science in Agriculture (BSA) - Major in Animal Science',
                    'Bachelor of Science in Agriculture (BSA) - Major in Crop Science',
                    'Bachelor of Science in Agriculture (BSA) - Major in Plant Pathology',
                    'Bachelor of Science in Agriculture (BSA) - Major in Soil Science',
                    'Bachelor of Science in Forestry (BSF)',
                ];
            case 'college of business & management':
            case 'college of business':
            case 'business & management':
            case 'business':
                return [
                    'Bachelor of Science in Agribusiness (BSAB)',
                    'Bachelor of Science in Business Administration (BSBA) - Major in Financial Management',
                    'Bachelor of Science in Hospitality Management (BSHM)',
                ];
            case 'college of computing studies':
            case 'college of computing':
            case 'computing':
                return ['Bachelor of Science in Computer Science (BSCS)', 'Bachelor of Science in Information Systems (BSIS)'];
            case 'college of criminology':
            case 'criminology':
                return ['Bachelor of Science in Criminology (BSCrim)'];
            default:
                if (strpos($college, 'computing') !== false) {
                    return ['Bachelor of Science in Computer Science (BSCS)', 'Bachelor of Science in Information Systems (BSIS)'];
                }
                if (strpos($college, 'business') !== false) {
                    return [
                        'Bachelor of Science in Agribusiness (BSAB)',
                        'Bachelor of Science in Business Administration (BSBA) - Major in Financial Management',
                        'Bachelor of Science in Hospitality Management (BSHM)',
                    ];
                }
                if (strpos($college, 'criminology') !== false) {
                    return ['Bachelor of Science in Criminology (BSCrim)'];
                }
                if (strpos($college, 'arts') !== false) {
                    return ['Bachelor of Arts in English Language Studies (BAELS)'];
                }
                if (strpos($college, 'education') !== false) {
                    return [
                        'Bachelor of Elementary Education (BEEd)',
                        'Bachelor of Physical Education (BPEd)',
                        'Bachelor of Secondary Education (BSEd) - Major in English',
                        'Bachelor of Secondary Education (BSEd) - Major in Filipino',
                        'Bachelor of Secondary Education (BSEd) - Major in Mathematics',
                        'Bachelor of Secondary Education (BSEd) - Major in Social Studies',
                    ];
                }
                if (strpos($college, 'agriculture') !== false || strpos($college, 'forestry') !== false) {
                    return [
                        'Bachelor of Science in Agriculture (BSA) - Major in Animal Science',
                        'Bachelor of Science in Agriculture (BSA) - Major in Crop Science',
                        'Bachelor of Science in Agriculture (BSA) - Major in Plant Pathology',
                        'Bachelor of Science in Agriculture (BSA) - Major in Soil Science',
                        'Bachelor of Science in Forestry (BSF)',
                    ];
                }
                return [];
        }
    }
}

if (!function_exists('getDepartmentThemeClass')) {
    function getDepartmentThemeClass($department = null) {
        $department = strtolower(trim($department ?? $_SESSION['student_department'] ?? $_SESSION['coordinator_department'] ?? ''));
        if ($department === '') {
            return 'default';
        }

        if (strpos($department, 'college of business') !== false
            || strpos($department, 'business administration') !== false
            || strpos($department, 'agribusiness') !== false
            || strpos($department, 'bsab') !== false
            || strpos($department, 'hospitality management') !== false
            || strpos($department, 'bshm') !== false
            || strpos($department, 'financial management') !== false
            || strpos($department, 'agriculture business') !== false
            || strpos($department, 'college of business & management') !== false) {
            return 'yellow';
        }

        if (strpos($department, 'college of computing') !== false
            || strpos($department, 'computer science') !== false
            || strpos($department, 'information systems') !== false
            || strpos($department, 'bscs') !== false
            || strpos($department, 'bsis') !== false) {
            return 'purple';
        }

        if (strpos($department, 'college of criminology') !== false
            || strpos($department, 'criminology') !== false
            || strpos($department, 'criminal justice') !== false
            || strpos($department, 'bscrim') !== false) {
            return 'red';
        }

        if (strpos($department, 'college of arts') !== false
            || strpos($department, 'baels') !== false
            || strpos($department, 'english language studies') !== false
            || strpos($department, 'arts') !== false) {
            return 'red';
        }

        if (strpos($department, 'college of education') !== false
            || strpos($department, 'beed') !== false
            || strpos($department, 'bped') !== false
            || strpos($department, 'bsed') !== false
            || strpos($department, 'elementary education') !== false
            || strpos($department, 'secondary education') !== false
            || strpos($department, 'physical education') !== false
            || strpos($department, 'education') !== false) {
            return 'blue';
        }

        if (strpos($department, 'college of agriculture') !== false
            || strpos($department, 'bsa') !== false
            || strpos($department, 'animal science') !== false
            || strpos($department, 'crop science') !== false
            || strpos($department, 'plant pathology') !== false
            || strpos($department, 'soil science') !== false
            || strpos($department, 'bsf') !== false
            || strpos($department, 'forestry') !== false
            || strpos($department, 'agriculture') !== false) {
            return 'green';
        }

        return 'default';
    }
}
// ===== End helper functions =====

// Handle CSV export before any output
function xmlEscape(mixed $value) {
    return htmlspecialchars((string)$value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function excelColumnLetter(int $index) {
    $letter = '';
    while ($index > 0) {
        $mod = ($index - 1) % 26;
        $letter = chr(65 + $mod) . $letter;
        $index = intdiv($index - 1, 26);
    }
    return $letter;
}

function createZipFromFiles(array $files) {
    $data = '';
    $directory = '';
    $offset = 0;

    foreach ($files as $name => $content) {
        $crc = crc32($content);
        $uncompressedSize = strlen($content);
        $compressedSize = $uncompressedSize;
        $nameLength = strlen($name);

        $localHeader = pack('V', 0x04034b50)
            . pack('v', 20)
            . pack('v', 0)
            . pack('v', 0)
            . pack('v', 0)
            . pack('V', 0)
            . pack('V', $crc)
            . pack('V', $compressedSize)
            . pack('V', $uncompressedSize)
            . pack('v', $nameLength)
            . pack('v', 0)
            . $name;

        $data .= $localHeader . $content;

        $directory .= pack('V', 0x02014b50)
            . pack('v', 0)
            . pack('v', 20)
            . pack('v', 0)
            . pack('v', 0)
            . pack('v', 0)
            . pack('V', 0)
            . pack('V', $crc)
            . pack('V', $compressedSize)
            . pack('V', $uncompressedSize)
            . pack('v', $nameLength)
            . pack('v', 0)
            . pack('v', 0)
            . pack('v', 0)
            . pack('v', 0)
            . pack('V', 0)
            . pack('V', $offset)
            . $name;

        $offset += strlen($localHeader) + $compressedSize;
    }

    $centralDirectorySize = strlen($directory);
    $centralDirectoryOffset = strlen($data);
    $data .= $directory;
    $data .= pack('V', 0x06054b50)
        . pack('v', 0)
        . pack('v', 0)
        . pack('v', count($files))
        . pack('v', count($files))
        . pack('V', $centralDirectorySize)
        . pack('V', $centralDirectoryOffset)
        . pack('v', 0);

    return $data;
}

function buildXlsxPackage(array $headers, array $rows) {
    $columnCount = count($headers);
    $maxWidths = array_map('mb_strlen', $headers);
    foreach ($rows as $row) {
        foreach ($row as $columnIndex => $cellValue) {
            $length = mb_strlen((string)$cellValue);
            if ($length > $maxWidths[$columnIndex]) {
                $maxWidths[$columnIndex] = $length;
            }
        }
    }

    $colsXml = '<cols>';
    for ($i = 0; $i < $columnCount; $i++) {
        $width = max($maxWidths[$i] + 2, 10);
        if ($width > 50) {
            $width = 50;
        }
        $colIndex = $i + 1;
        $colsXml .= sprintf('<col min="%d" max="%d" width="%s" customWidth="1"/>', $colIndex, $colIndex, $width);
    }
    $colsXml .= '</cols>';

    $lastColumnLetter = excelColumnLetter($columnCount);
    $rowCount = count($rows) + 1;
    $dimensionRef = 'A1:' . $lastColumnLetter . $rowCount;

    $sheetData = '<sheetData>';
    $sheetData .= '<row r="1">';
    foreach ($headers as $columnIndex => $header) {
        $cellRef = excelColumnLetter($columnIndex + 1) . '1';
        $sheetData .= '<c r="' . $cellRef . '" t="inlineStr"><is><t>' . xmlEscape($header) . '</t></is></c>';
    }
    $sheetData .= '</row>';

    foreach ($rows as $rowIndex => $row) {
        $sheetNumber = $rowIndex + 2;
        $sheetData .= '<row r="' . $sheetNumber . '">';
        foreach ($row as $columnIndex => $cellValue) {
            $cellRef = excelColumnLetter($columnIndex + 1) . $sheetNumber;
            $numericColumns = [3, 4, 5];
            if (in_array($columnIndex, $numericColumns, true) && is_numeric($cellValue) && $cellValue !== '') {
                $normalized = str_replace(',', '.', (string)$cellValue);
                if (is_numeric($normalized)) {
                    $sheetData .= '<c r="' . $cellRef . '"><v>' . xmlEscape($normalized) . '</v></c>';
                    continue;
                }
            }
            $sheetData .= '<c r="' . $cellRef . '" t="inlineStr"><is><t>' . xmlEscape($cellValue) . '</t></is></c>';
        }
        $sheetData .= '</row>';
    }
    $sheetData .= '</sheetData>';

    $sheetXml = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        . '<dimension ref="' . $dimensionRef . '"/>'
        . $colsXml
        . $sheetData
        . '</worksheet>';

    $contentTypes = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
        . '<Default Extension="xml" ContentType="application/xml"/>'
        . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
        . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
        . '</Types>';

    $relsXml = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
        . '</Relationships>';

    $workbookXml = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<sheets><sheet name="Report" sheetId="1" r:id="rId1"/></sheets></workbook>';

    $workbookRelsXml = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
        . '</Relationships>';

    return createZipFromFiles([
        '[Content_Types].xml' => $contentTypes,
        '_rels/.rels' => $relsXml,
        'xl/workbook.xml' => $workbookXml,
        'xl/_rels/workbook.xml.rels' => $workbookRelsXml,
        'xl/worksheets/sheet1.xml' => $sheetXml,
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_csv'])) {
    // Include database connection for export
    include __DIR__ . '/../dbconnection.php';

    $requiredHours = 480; // Default fallback
    $coordinatorId = $_SESSION['coordinator_id'] ?? null;

    $departmentFilter = $_POST['department'] ?? $_GET['department'] ?? 'all';
    $searchFilter = trim($_POST['search'] ?? $_GET['search'] ?? '');

    $students = [];

    if ($coordinatorId) {
        try {
            // Get departments assigned to this coordinator
            $stmt = $conn->prepare("SELECT department FROM coordinator_department_assignments WHERE coordinator_id = ? ORDER BY department");
            if (!$stmt) {
                throw new Exception('Prepare failed');
            }
            $stmt->bind_param('i', $coordinatorId);
            $stmt->execute();

            if (method_exists($stmt, 'get_result')) {
                $result = $stmt->get_result();
                $assignedDepartments = $result ? array_column($result->fetch_all(MYSQLI_ASSOC), 'department') : [];
            } else {
                $dept = null;
                $stmt->bind_result($dept);
                $assignedDepartments = [];
                while ($stmt->fetch()) {
                    $assignedDepartments[] = $dept;
                }
            }

            // Also include direct student assignments to this coordinator
            $assignedStudentIds = [];
            $stmt2 = $conn->prepare("SELECT student_id FROM coordinator_student_assignments WHERE coordinator_id = ?");
            if ($stmt2) {
                $stmt2->bind_param('i', $coordinatorId);
                $stmt2->execute();
                if (method_exists($stmt2, 'get_result')) {
                    $result2 = $stmt2->get_result();
                    $assignedStudentIds = $result2 ? array_column($result2->fetch_all(MYSQLI_ASSOC), 'student_id') : [];
                } else {
                    $studentIdRow = null;
                    $stmt2->bind_result($studentIdRow);
                    while ($stmt2->fetch()) {
                        $assignedStudentIds[] = $studentIdRow;
                    }
                }
            }

            if (empty($assignedDepartments)) {
                // Fallback to coordinator department from own profile if no explicit assignment exists
                $coordinatorDept = trim($_SESSION['coordinator_department'] ?? '');
                if ($coordinatorDept !== '') {
                    $assignedDepartments = getDepartmentsForCollege($coordinatorDept);
                    if (empty($assignedDepartments)) {
                        $assignedDepartments = [$coordinatorDept];
                    }
                }
            }

            if (empty($assignedDepartments) && empty($assignedStudentIds)) {
                $students = [];
            } else {
                // Get students from assigned departments and/or explicit student assignments
                $conditions = [];
                $params = [];
                $types = '';

                if (!empty($assignedDepartments)) {
                    $placeholders = str_repeat('?,', count($assignedDepartments) - 1) . '?';
                    $conditions[] = "s.department IN ($placeholders)";
                    $types .= str_repeat('s', count($assignedDepartments));
                    $params = array_merge($params, $assignedDepartments);
                }

                if (!empty($assignedStudentIds)) {
                    $placeholders2 = str_repeat('?,', count($assignedStudentIds) - 1) . '?';
                    $conditions[] = "s.student_id IN ($placeholders2)";
                    $types .= str_repeat('s', count($assignedStudentIds));
                    $params = array_merge($params, $assignedStudentIds);
                }

                $whereClause = implode(' OR ', $conditions);
                $sql = "SELECT s.student_id, s.name, s.department, COALESCE(s.required_ojt_hours, ?) AS required_hours,
                               COALESCE(sp.total_hours_completed, 0) AS progress_hours,
                               COALESCE(ar.total_attended_hours, 0) AS attendance_hours,
                               ar.last_clock_in, ar.last_clock_out
                        FROM students s
                        LEFT JOIN student_progress sp ON s.student_id = sp.student_id
                        LEFT JOIN (
                            SELECT student_id,
                                   SUM(hours_worked) AS total_attended_hours,
                                   MAX(clock_in) AS last_clock_in,
                                   MAX(clock_out) AS last_clock_out
                            FROM attendance_records
                            GROUP BY student_id
                        ) ar ON s.student_id = ar.student_id
                        WHERE ($whereClause)";

                // Add the default required hours parameter for COALESCE
                $types = 'i' . $types;  // 'i' for integer
                array_unshift($params, $requiredHours);

                // Apply filters
                if ($departmentFilter !== 'all') {
                    $sql .= ' AND s.department = ?';
                    $types .= 's';
                    $params[] = $departmentFilter;
                }
                if ($searchFilter !== '') {
                    $sql .= ' AND (s.name LIKE ? OR s.student_id LIKE ?)';
                    $types .= 'ss';
                    $params[] = '%' . $searchFilter . '%';
                    $params[] = '%' . $searchFilter . '%';
                }

                $sql .= ' ORDER BY s.name ASC';

                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Prepared statement failed: ' . $conn->error);
                }

                if ($types !== '') {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();

                if (method_exists($stmt, 'get_result')) {
                    $result = $stmt->get_result();
                    $students = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
                } else {
                    $studentId = $name = $department = $requiredHoursValue = $progressHours = $attendanceHours = $lastClockIn = $lastClockOut = null;
                    $stmt->bind_result($studentId, $name, $department, $requiredHoursValue, $progressHours, $attendanceHours, $lastClockIn, $lastClockOut);
                    while ($stmt->fetch()) {
                        $students[] = [
                            'student_id' => $studentId,
                            'name' => $name,
                            'department' => $department,
                            'required_hours' => floatval($requiredHoursValue),
                            'progress_hours' => floatval($progressHours),
                            'attendance_hours' => floatval($attendanceHours),
                            'last_clock_in' => $lastClockIn,
                            'last_clock_out' => $lastClockOut,
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            error_log('Coordinator reports DB query failed: ' . $e->getMessage());
            $students = [];
        }
    }

    // Set required hours from students data or coordinator department default
    if (!empty($students)) {
        $requiredValues = array_map('intval', array_column($students, 'required_hours'));
        $uniqueRequired = array_unique($requiredValues);
        if (count($uniqueRequired) === 1) {
            $requiredHours = intval($uniqueRequired[0]);
        } else {
            // If mixed departments, show max as the requirement
            $requiredHours = max($requiredValues);
        }
    } elseif (!empty($_SESSION['coordinator_department'])) {
        $deptName = trim($_SESSION['coordinator_department']);
        try {
            $deptStmt = $conn->prepare('SELECT required_hours FROM department_required_hours WHERE department = ? LIMIT 1');
            if ($deptStmt) {
                $deptStmt->bind_param('s', $deptName);
                $deptStmt->execute();
                $deptRequired = null;
                $deptStmt->bind_result($deptRequired);
                if ($deptStmt->fetch()) {
                    $requiredHours = intval($deptRequired);
                }
                $deptStmt->close();
            }
        } catch (Exception $e) {
            error_log('Error fetching coordinator department required hours: ' . $e->getMessage());
        }
    }

    foreach ($students as &$student) {
        $progressHours = round(floatval($student['progress_hours'] ?? 0), 2);
        $attendanceHours = round(floatval($student['attendance_hours'] ?? 0), 2);
        $student['completed'] = max($progressHours, $attendanceHours);
        $student['required_hours'] = floatval($student['required_hours'] ?? $requiredHours);
        $student['remaining'] = max(0, $student['required_hours'] - $student['completed']);
        $student['percent'] = $student['required_hours'] > 0 ? round(($student['completed'] / $student['required_hours']) * 100, 2) : 0;
        $student['clocked_in'] = false;
        if (!empty($student['last_clock_in'])) {
            $lastIn = strtotime($student['last_clock_in']);
            $lastOut = !empty($student['last_clock_out']) ? strtotime($student['last_clock_out']) : 0;
            $student['clocked_in'] = ($lastIn > $lastOut);
        }
        $student['status'] = $student['clocked_in'] ? 'Clocked In' : 'Timed Out';
    }
    unset($student);

    $headers = ['Student ID', 'Name', 'Department', 'Completed Hours', 'Remaining Hours', 'Progress (%)', 'Status'];
    $rows = [];
    foreach ($students as $student) {
        $rows[] = [
            $student['student_id'] ?? '',
            $student['name'] ?? '',
            $student['department'] ?? '',
            number_format($student['completed'], 2),
            number_format($student['remaining'], 2),
            $student['percent'],
            $student['status'] ?? ''
        ];
    }

    $exportFilename = 'ojt_student_progress_report_' . date('Ymd_His') . '.csv';
    $exportFilters = json_encode([
        'department' => $departmentFilter,
        'search' => $searchFilter
    ]);
    $exportRows = count($students);

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

        $historyStmt = $conn->prepare(
            'INSERT INTO csv_export_history (coordinator_id, exported_at, department, filters, filename, rows_exported, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        if ($historyStmt) {
            $exportedAt = date('Y-m-d H:i:s');
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $historyStmt->bind_param('issssis', $coordinatorId, $exportedAt, $departmentFilter, $exportFilters, $exportFilename, $exportRows, $userAgent);
            $historyStmt->execute();
            $historyStmt->close();
        }
    } catch (Exception $e) {
        error_log('CSV export history insert failed: ' . $e->getMessage());
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $exportFilename . '"');

    $output = fopen('php://output', 'w');
    if ($output !== false) {
        // UTF-8 BOM for Excel compatibility
        echo "\xEF\xBB\xBF";
        fputcsv($output, $headers);

        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
    }
    exit;
}

if (empty($_SESSION['coordinator_logged_in']) || $_SESSION['coordinator_logged_in'] !== true) {
    header('Location: /ojt1/index.php?coordinator_login_failed=1');
    exit;
}

$pageTitle = 'Coordinator Reports';
include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="../assets/css/coodinator/reports.css">
<?php
include __DIR__ . '/../dbconnection.php';

$coordinatorDepartment = trim($_SESSION['coordinator_department'] ?? '');
$coordinatorTheme = getDepartmentThemeClass($coordinatorDepartment);

// Define filter variables for HTML form
$departmentFilter = $_GET['department'] ?? 'all';
$searchFilter = trim($_GET['search'] ?? '');

// Get students data for display
$students = getCoordinatorStudents($conn, $_SESSION, $_GET);
processStudentData($students, $_SESSION);

$summaryTotal = count($students);
$summaryCompleted = array_sum(array_column($students, 'completed'));
$summaryRemaining = array_sum(array_column($students, 'remaining'));
$summaryAverage = $summaryTotal > 0 ? round(array_sum(array_column($students, 'percent')) / $summaryTotal, 2) : 0;
$summaryClockedIn = count(array_filter($students, fn($s) => $s['clocked_in']));
$summaryLowProgress = count(array_filter($students, fn($s) => $s['percent'] < 20));

function formatExportTime(int $timestamp)
{
    return date('M d, Y h:i A', $timestamp);
}

$liveFeed = [];
$recentHistory = [];
try {
    $feedStmt = $conn->prepare(
        'SELECT exported_at, department, filters, filename, rows_exported FROM csv_export_history WHERE coordinator_id = ? ORDER BY exported_at DESC LIMIT 7'
    );
    if ($feedStmt) {
        $coordinatorId = $_SESSION['coordinator_id'] ?? 0;
        $feedStmt->bind_param('i', $coordinatorId);
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

            $liveFeed[] = [
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
    error_log('Failed to load export history for reports: ' . $e->getMessage());
}

if (!empty($liveFeed)) {
    $recentHistory[] = $liveFeed[0];
}

function getCoordinatorStudents(mysqli $conn, array $session, array $get) {
    $requiredHours = 480; // Default fallback
    $coordinatorId = $session['coordinator_id'] ?? null;

    $departmentFilter = $get['department'] ?? 'all';
    $searchFilter = trim($get['search'] ?? '');

    $students = [];

    if ($coordinatorId) {
        try {
            // Get departments assigned to this coordinator
            $stmt = $conn->prepare("SELECT department FROM coordinator_department_assignments WHERE coordinator_id = ? ORDER BY department");
            if (!$stmt) {
                throw new Exception('Prepare failed');
            }
            $stmt->bind_param('i', $coordinatorId);
            $stmt->execute();

            if (method_exists($stmt, 'get_result')) {
                $result = $stmt->get_result();
                $assignedDepartments = $result ? array_column($result->fetch_all(MYSQLI_ASSOC), 'department') : [];
            } else {
                $dept = null;
                $stmt->bind_result($dept);
                $assignedDepartments = [];
                while ($stmt->fetch()) {
                    $assignedDepartments[] = $dept;
                }
            }

            // Also include direct student assignments to this coordinator
            $assignedStudentIds = [];
            $stmt2 = $conn->prepare("SELECT student_id FROM coordinator_student_assignments WHERE coordinator_id = ?");
            if ($stmt2) {
                $stmt2->bind_param('i', $coordinatorId);
                $stmt2->execute();
                if (method_exists($stmt2, 'get_result')) {
                    $result2 = $stmt2->get_result();
                    $assignedStudentIds = $result2 ? array_column($result2->fetch_all(MYSQLI_ASSOC), 'student_id') : [];
                } else {
                    $studentIdRow = null;
                    $stmt2->bind_result($studentIdRow);
                    while ($stmt2->fetch()) {
                        $assignedStudentIds[] = $studentIdRow;
                    }
                }
            }

            if (empty($assignedDepartments)) {
                // Fallback to coordinator department from own profile if no explicit assignment exists
                $coordinatorDept = trim($session['coordinator_department'] ?? '');
                if ($coordinatorDept !== '') {
                    $assignedDepartments = getDepartmentsForCollege($coordinatorDept);
                    if (empty($assignedDepartments)) {
                        $assignedDepartments = [$coordinatorDept];
                    }
                }
            }

            if (empty($assignedDepartments) && empty($assignedStudentIds)) {
                $students = [];
            } else {
                // Get students from assigned departments and/or explicit student assignments
                $conditions = [];
                $params = [];
                $types = '';

                if (!empty($assignedDepartments)) {
                    $placeholders = str_repeat('?,', count($assignedDepartments) - 1) . '?';
                    $conditions[] = "s.department IN ($placeholders)";
                    $types .= str_repeat('s', count($assignedDepartments));
                    $params = array_merge($params, $assignedDepartments);
                }

                if (!empty($assignedStudentIds)) {
                    $placeholders2 = str_repeat('?,', count($assignedStudentIds) - 1) . '?';
                    $conditions[] = "s.student_id IN ($placeholders2)";
                    $types .= str_repeat('s', count($assignedStudentIds));
                    $params = array_merge($params, $assignedStudentIds);
                }

                $whereClause = implode(' OR ', $conditions);
                $sql = "SELECT s.student_id, s.name, s.department, COALESCE(s.required_ojt_hours, ?) AS required_hours,
                               COALESCE(sp.total_hours_completed, 0) AS progress_hours,
                               COALESCE(ar.total_attended_hours, 0) AS attendance_hours,
                               ar.last_clock_in, ar.last_clock_out
                        FROM students s
                        LEFT JOIN student_progress sp ON s.student_id = sp.student_id
                        LEFT JOIN (
                            SELECT student_id,
                                   SUM(hours_worked) AS total_attended_hours,
                                   MAX(clock_in) AS last_clock_in,
                                   MAX(clock_out) AS last_clock_out
                            FROM attendance_records
                            GROUP BY student_id
                        ) ar ON s.student_id = ar.student_id
                        WHERE ($whereClause)";

                // Add the default required hours parameter for COALESCE
                $types = 'i' . $types;  // 'i' for integer
                array_unshift($params, $requiredHours);

                // Apply filters
                if ($departmentFilter !== 'all') {
                    $sql .= ' AND s.department = ?';
                    $types .= 's';
                    $params[] = $departmentFilter;
                }
                if ($searchFilter !== '') {
                    $sql .= ' AND (s.name LIKE ? OR s.student_id LIKE ?)';
                    $types .= 'ss';
                    $params[] = '%' . $searchFilter . '%';
                    $params[] = '%' . $searchFilter . '%';
                }

                $sql .= ' ORDER BY s.name ASC';

                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Prepared statement failed: ' . $conn->error);
                }

                if ($types !== '') {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();

                if (method_exists($stmt, 'get_result')) {
                    $result = $stmt->get_result();
                    $students = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
                } else {
                    $studentId = $name = $department = $requiredHoursValue = $progressHours = $attendanceHours = $lastClockIn = $lastClockOut = null;
                    $stmt->bind_result($studentId, $name, $department, $requiredHoursValue, $progressHours, $attendanceHours, $lastClockIn, $lastClockOut);
                    while ($stmt->fetch()) {
                        $students[] = [
                            'student_id' => $studentId,
                            'name' => $name,
                            'department' => $department,
                            'required_hours' => floatval($requiredHoursValue),
                            'progress_hours' => floatval($progressHours),
                            'attendance_hours' => floatval($attendanceHours),
                            'last_clock_in' => $lastClockIn,
                            'last_clock_out' => $lastClockOut,
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            error_log('Coordinator reports DB query failed: ' . $e->getMessage());
            $students = [];
        }
    }

    return $students;
}

function processStudentData(array &$students, array $session) {
    global $conn; // Access the database connection
    $requiredHours = 480; // Default fallback

    // Set required hours from students data or coordinator department default
    if (!empty($students)) {
        $requiredValues = array_map('intval', array_column($students, 'required_hours'));
        $uniqueRequired = array_unique($requiredValues);
        if (count($uniqueRequired) === 1) {
            $requiredHours = intval($uniqueRequired[0]);
        } else {
            // If mixed departments, show max as the requirement
            $requiredHours = max($requiredValues);
        }
    } elseif (!empty($session['coordinator_department'])) {
        $deptName = trim($session['coordinator_department']);
        try {
            $deptStmt = $conn->prepare('SELECT required_hours FROM department_required_hours WHERE department = ? LIMIT 1');
            if ($deptStmt) {
                $deptStmt->bind_param('s', $deptName);
                $deptStmt->execute();
                $deptRequired = null;
                $deptStmt->bind_result($deptRequired);
                if ($deptStmt->fetch()) {
                    $requiredHours = intval($deptRequired);
                }
                $deptStmt->close();
            }
        } catch (Exception $e) {
            error_log('Error fetching coordinator department required hours: ' . $e->getMessage());
        }
    }

    foreach ($students as &$student) {
        $progressHours = round(floatval($student['progress_hours'] ?? 0), 2);
        $attendanceHours = round(floatval($student['attendance_hours'] ?? 0), 2);
        $student['completed'] = max($progressHours, $attendanceHours);
        $student['required_hours'] = floatval($student['required_hours'] ?? $requiredHours);
        $student['remaining'] = max(0, $student['required_hours'] - $student['completed']);
        $student['percent'] = $student['required_hours'] > 0 ? round(($student['completed'] / $student['required_hours']) * 100, 2) : 0;
        $student['clocked_in'] = false;
        if (!empty($student['last_clock_in'])) {
            $lastIn = strtotime($student['last_clock_in']);
            $lastOut = !empty($student['last_clock_out']) ? strtotime($student['last_clock_out']) : 0;
            $student['clocked_in'] = ($lastIn > $lastOut);
        }
        $student['status'] = $student['clocked_in'] ? 'Clocked In' : 'Timed Out';
    }
    unset($student);
}

?>
<body class="hold-transition sidebar-mini layout-fixed theme-<?= htmlspecialchars($coordinatorTheme) ?>">
<div class="wrapper">

  <div class="preloader flex-column justify-content-center align-items-center" style="background: linear-gradient(to bottom, blue, yellow);">
    <img class="animation__shake" src="../assets/img/users/OIP.webp" alt="Preloader" height="150" width="150" style="border-radius: 50%;">
  </div>

  <?php include __DIR__ . '/../includes/navbar.php'; ?>
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Reports</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="coordinator.php">Home</a></li>
              <li class="breadcrumb-item active">Reports</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">

        <div class="row">
          <div class="col-md-3 col-sm-6">
            <div class="small-box bg-info">
              <div class="inner">
                <h3><?= $summaryTotal ?></h3>
                <p>Total Monitored Students</p>
              </div>
              <div class="icon"><i class="fas fa-users"></i></div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3><?= number_format($summaryCompleted, 2) ?></h3>
                <p>Cumulative Completed Hours</p>
              </div>
              <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="small-box bg-warning">
              <div class="inner">
                <h3><?= $summaryLowProgress ?></h3>
                <p>Low progress (<20%)</p>
              </div>
              <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="small-box bg-purple">
              <div class="inner">
                <h3><?= $summaryClockedIn ?></h3>
                <p>Currently clocked in</p>
              </div>
              <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-8">
            <div class="card card-outline card-primary">
              <div class="card-header bg-primary text-white">
                <h3 class="card-title">Auto-generated Student Progress Report</h3>
                <div class="card-tools d-flex align-items-center" style="gap: 0.5rem;">
                  <form method="get" class="form-inline" id="reportFilterForm">
                    <div class="input-group input-group-sm mr-1">
                      <input type="text" name="search" id="reportSearch" class="form-control" placeholder="Search name / ID" value="<?= htmlspecialchars($searchFilter) ?>">
                      <div class="input-group-append">
                        <button class="btn btn-default" type="submit"><i class="fas fa-search"></i></button>
                      </div>
                      <button type="button" class="btn btn-outline-secondary" id="clearSearchBtn" style="border-left:0;" title="Clear search"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="input-group input-group-sm mr-1">
                      <select class="form-control" name="department" id="reportDepartment" onchange="this.form.submit()">
                        <option value="all" <?= $departmentFilter === 'all' ? 'selected' : '' ?>>All Departments</option>
                        <?php
                        $departments = array_unique(array_column($students, 'department'));
                        sort($departments);
                        foreach ($departments as $dept): ?>
                          <option value="<?= htmlspecialchars($dept) ?>" <?= $departmentFilter === $dept ? 'selected' : '' ?>><?= htmlspecialchars($dept) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </form>
                  <form method="post" class="ml-2">
                    <input type="hidden" name="department" value="<?= htmlspecialchars($departmentFilter) ?>">
                    <input type="hidden" name="search" value="<?= htmlspecialchars($searchFilter) ?>">
                    <button type="submit" name="export_csv" class="btn btn-sm btn-success">Export CSV</button>
                  </form>
                </div>
              </div>
              <div class="card-body p-0">
                <div class="table-scroll-wrapper">
                  <table class="table table-hover text-nowrap" id="reportTable">
                    <thead>
                      <tr>
                        <th>Name</th>
                        <th>Completed (h)</th>
                        <th>Remaining (h)</th>
                        <th>Progress (%)</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($students as $student): ?>
                        <tr data-student-name="<?= htmlspecialchars(strtolower($student['name'])) ?>" data-student-id="<?= htmlspecialchars(strtolower($student['student_id'] ?? '')) ?>">
                          <td><?= htmlspecialchars($student['name']) ?></td>
                          <td><?= number_format($student['completed'], 2) ?></td>
                          <td><?= number_format($student['remaining'], 2) ?></td>
                          <td><?= htmlspecialchars($student['percent']) ?></td>
                          <td><span class="badge badge-<?= $student['status'] === 'Clocked In' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($student['status']) ?></span></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card card-outline export-history-card">
              <div class="card-header text-white">
                <h3 class="card-title">Export History</h3>
              </div>
              <div class="card-body">
                <div id="liveActivityTimeline" class="timeline timeline-inverse">
                  <?php if (empty($recentHistory)): ?>
                    <div>
                      <i class="fas fa-file-excel bg-gray"></i>
                      <div class="timeline-item">
                        <h3 class="timeline-header border-0 text-muted">No export history yet</h3>
                        <div class="timeline-body text-sm text-secondary">Export a report to create an entry in the history panel.</div>
                      </div>
                    </div>
                  <?php else: ?>
                    <?php foreach ($recentHistory as $event): ?>
                      <div>
                        <i class="<?= htmlspecialchars($event['icon'] ?? 'fas fa-file-excel bg-success') ?>"></i>
                        <div class="timeline-item">
                          <span class="time"><i class="far fa-clock"></i> <?= htmlspecialchars($event['time']) ?></span>
                          <h3 class="timeline-header border-0"><strong><?= htmlspecialchars($event['student']) ?></strong> <?= htmlspecialchars($event['event']) ?></h3>
                          <?php if (!empty($event['details'])): ?>
                            <div class="timeline-body text-sm text-secondary"><?= htmlspecialchars($event['details']) ?></div>
                          <?php endif; ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                  <div>
                    <i class="fas fa-clock bg-gray"></i>
                    <div class="timeline-item">
                      <span class="time"><i class="far fa-clock"></i> <?= date('h:i A') ?></span>
                      <h3 class="timeline-header border-0">History last refreshed</h3>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer text-center">
                <button type="button" class="btn btn-history btn-sm" data-toggle="modal" data-target="#allActivityModal">View full export history</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- All Export History Modal -->
  <div class="modal fade" id="allActivityModal" tabindex="-1" role="dialog" aria-labelledby="allActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" role="document" style="max-width: 860px;">
      <div class="modal-content history-modal border-secondary shadow-lg">
        <div class="modal-header">
          <h5 class="modal-title text-white" id="allActivityModalLabel">Full Export History</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" style="max-height: 520px; overflow-y: auto;">
          <div id="allActivityContent" class="list-group">
            <?php if (!empty($liveFeed)): ?>
              <?php foreach ($liveFeed as $event): ?>
                <div class="list-group-item list-group-item-action flex-column align-items-start">
                  <div class="d-flex w-100 justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                      <span class="badge badge-pill badge-light mr-2"><i class="<?= htmlspecialchars($event['icon'] ?? 'fas fa-file-excel bg-success') ?>"></i></span>
                      <div>
                        <h5 class="mb-1 mb-0"><?= htmlspecialchars($event['student']) ?></h5>
                        <small class="text-muted"><?= htmlspecialchars($event['event']) ?></small>
                      </div>
                    </div>
                    <small class="text-muted"><?= htmlspecialchars($event['time']) ?></small>
                  </div>
                  <?php if (!empty($event['details'])): ?>
                    <p class="mb-1 text-secondary"><?= htmlspecialchars($event['details']) ?></p>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="list-group-item text-muted">No export history available.</div>
            <?php endif; ?>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
  <script src="../assets/js/coodinator/reports.js"></script>
  <?php include __DIR__ . '/../includes/script.php'; ?>
</div>
</body>
</html>