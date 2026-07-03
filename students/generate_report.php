<?php
/**
 * Daily Report PDF Generator
 * Generates PDF reports from student daily report entries
 * 
 * Features:
 * - Student name & ID
 * - Date
 * - Uploaded photos (scaled to fit)
 * - Details text (formatted neatly)
 * - Auto-save to /reports/student_id/date.pdf
 */


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../dbconnection.php';
require_once __DIR__ . '/report_template.php';


define('REPORTS_DIR', __DIR__ . '/../reports');
define('MAX_IMAGE_WIDTH', 170);
define('MAX_IMAGE_HEIGHT', 120);

/**
 * Generate PDF report from daily report entries
 */
function generateDailyReportPDF(mysqli $conn, string $studentId, string $entryDate, array $entries): array {
    $stmt = $conn->prepare("SELECT student_id, name, email, department FROM students WHERE student_id = ?");
    $stmt->bind_param('s', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $stmt->close();
    
    if (!$student) {
        return ['success' => false, 'error' => 'Student not found'];
    }
    
    $studentReportsDir = REPORTS_DIR . '/' . $studentId;
    if (!is_dir($studentReportsDir)) {
        mkdir($studentReportsDir, 0755, true);
    }
    
    $pdfFilename = $entryDate . '.html';
    $pdfPath = $studentReportsDir . '/' . $pdfFilename;
    
    $themeColor = getDepartmentThemeColor($student['department'] ?? '');
    $html = buildReportHTML($student, $entryDate, $entries, $themeColor);
    
    if (file_put_contents($pdfPath, $html)) {
        return [
            'success' => true,
            'pdf_path' => 'reports/' . $studentId . '/' . $pdfFilename,
            'pdf_full_path' => $pdfPath
        ];
    }
    
    return ['success' => false, 'error' => 'Failed to save PDF'];
}

/**
 * Get department theme color
 */
if (!function_exists('getDepartmentThemeColor')) {
    function getDepartmentThemeColor(string $department): string {
        $department = strtolower(trim($department));
        
        if (strpos($department, 'business administration') !== false
            || strpos($department, 'agribusiness') !== false
            || strpos($department, 'bsab') !== false
            || strpos($department, 'hospitality management') !== false
            || strpos($department, 'bshm') !== false
            || strpos($department, 'financial management') !== false
            || strpos($department, 'agriculture business') !== false) {
            return '#d39e00';
        }
        if (strpos($department, 'computer science') !== false
            || strpos($department, 'information systems') !== false
            || strpos($department, 'bscs') !== false
            || strpos($department, 'bsis') !== false) {
            return '#6f42c1';
        }
        if (strpos($department, 'criminology') !== false
            || strpos($department, 'criminal justice') !== false
            || strpos($department, 'bscrim') !== false
            || strpos($department, 'baels') !== false
            || strpos($department, 'english language studies') !== false) {
            return '#c82333';
        }
        if (strpos($department, 'agriculture business') !== false) {
            return '#d39e00';
        }
        if (strpos($department, 'bsa') !== false
            || strpos($department, 'animal science') !== false
            || strpos($department, 'crop science') !== false
            || strpos($department, 'plant pathology') !== false
            || strpos($department, 'soil science') !== false
            || strpos($department, 'bsf') !== false
            || strpos($department, 'forestry') !== false
            || strpos($department, 'agriculture') !== false) {
            return '#28a745';
        }
        if (strpos($department, 'beed') !== false
            || strpos($department, 'bped') !== false
            || strpos($department, 'bsed') !== false
            || strpos($department, 'elementary education') !== false
            || strpos($department, 'secondary education') !== false
            || strpos($department, 'physical education') !== false
            || strpos($department, 'education') !== false) {
            return '#007bff';
        }
        
        return '#2c3e50';
    }
}

/**
 * Build report HTML
 */
function buildReportHTML(array $student, string $entryDate, array $entries, string $themeColor): string {
    return renderReportTemplate($student, $entryDate, $entries, $themeColor);
}

/**
 * Get image paths from entry
 */
function getEntryImagePaths(array $entry): array {
    $paths = [];
    
    if (!empty($entry['image_paths'])) {
        $decoded = json_decode($entry['image_paths'], true);
        if (is_array($decoded)) {
            foreach ($decoded as $path) {
                if ($path) {
                    $paths[] = $path;
                }
            }
        }
    }
    
    if (empty($paths) && !empty($entry['image_path'])) {
        $paths[] = $entry['image_path'];
    }
    
    return $paths;
}

/**
 * Get image as data URI
 */
function getImageDataUri(string $relativePath): ?string {
    $localPath = __DIR__ . '/../' . ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $relativePath), DIRECTORY_SEPARATOR);
    
    if (!file_exists($localPath)) {
        return null;
    }
    
    $data = file_get_contents($localPath);
    $mime = mime_content_type($localPath);
    
    return 'data:' . $mime . ';base64,' . base64_encode($data);
}

/**
 * Generate downloadable PDF file
 */
function generateDownloadablePDF(mysqli $conn, string $studentId, string $entryDate): array {
    $stmt = $conn->prepare("SELECT id, entry_date, note, image_path, image_paths, created_at FROM student_documentary WHERE student_id = ? AND entry_date = ? AND note IS NOT NULL AND note <> '' ORDER BY created_at DESC");
    $stmt->bind_param('ss', $studentId, $entryDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $entries = [];
    while ($row = $result->fetch_assoc()) {
        $entries[] = $row;
    }
    $stmt->close();
    
    if (empty($entries)) {
        return ['success' => false, 'error' => 'No entries found for this date'];
    }
    
    return generateDailyReportPDF($conn, $studentId, $entryDate, $entries);
}


if (php_sapi_name() !== 'cli' && isset($_GET['student_id']) && isset($_GET['date'])) {
    $studentId = $_GET['student_id'];
    $entryDate = $_GET['date'];
    
    $result = generateDownloadablePDF($conn, $studentId, $entryDate);
    
    if ($result['success']) {
        $pdfPath = __DIR__ . '/../' . $result['pdf_path'];
        if (file_exists($pdfPath)) {
            header('Content-Type: text/html');
            header('Content-Disposition: inline; filename="' . basename($pdfPath) . '"');
            readfile($pdfPath);
            exit;
        }
    }
}