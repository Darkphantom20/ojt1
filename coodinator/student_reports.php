<?php
/**
 * Coordinator View Student Daily Reports
 * Allows coordinators to view and download student daily reports
 */

session_start();
$pageTitle = 'Student Daily Reports';

include __DIR__ . '/../dbconnection.php';


if (empty($_SESSION['coordinator_logged_in']) || $_SESSION['coordinator_logged_in'] !== true) {
    header('Location: ../index.php');
    exit;
}

$coordinatorId = $_SESSION['coordinator_id'] ?? null;
$coordinatorName = $_SESSION['coordinator_name'] ?? 'Coordinator';
$coordinatorDepartment = $_SESSION['coordinator_department'] ?? '';


$assignedDepartments = [];
$stmt = $conn->prepare("SELECT department FROM coordinator_department_assignments WHERE coordinator_id = ? ORDER BY department");
if ($stmt) {
    $stmt->bind_param('i', $coordinatorId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $assignedDepartments[] = $row['department'];
    }
    $stmt->close();
}


if (empty($assignedDepartments) && !empty($coordinatorDepartment)) {
    $assignedDepartments = getDepartmentsForCollege($coordinatorDepartment);
    if (empty($assignedDepartments)) {
        $assignedDepartments = [$coordinatorDepartment];
    }
}


$students = [];
if (!empty($assignedDepartments)) {
    $placeholders = str_repeat('s,', count($assignedDepartments) - 1) . 's';
    $sql = "SELECT student_id, name, department FROM students WHERE department IN ($placeholders) ORDER BY department, name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('s', count($assignedDepartments)), ...$assignedDepartments);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
}

    $selectedStudent = $_GET['student'] ?? '';
    $selectedDate = $_GET['date'] ?? date('Y-m-d');

    if ($selectedStudent) {
        $assignedStudentIds = array_column($students, 'student_id');
        if (!in_array($selectedStudent, $assignedStudentIds, true)) {
            header('Location: ../coodinator/student_reports.php');
            exit;
        }
    }

$reportEntries = [];
if ($selectedStudent && $selectedDate) {
    $stmt = $conn->prepare("SELECT id, entry_date, note, image_path, image_paths, created_at FROM student_documentary WHERE student_id = ? AND entry_date = ? AND note IS NOT NULL AND note <> '' ORDER BY created_at DESC");
    $stmt->bind_param('ss', $selectedStudent, $selectedDate);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $reportEntries[] = $row;
    }
    $stmt->close();
}


$studentInfo = null;
if ($selectedStudent) {
    $stmt = $conn->prepare("SELECT student_id, name, email, department FROM students WHERE student_id = ?");
    $stmt->bind_param('s', $selectedStudent);
    $stmt->execute();
    $result = $stmt->get_result();
    $studentInfo = $result->fetch_assoc();
    $stmt->close();
}


function getEntryImagePaths(array $entry) {
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

function getImageDataUri(string $relativePath) {
    $localPath = __DIR__ . '/../' . ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $relativePath), DIRECTORY_SEPARATOR);
    if (!file_exists($localPath)) {
        return null;
    }
    $data = file_get_contents($localPath);
    $mime = mime_content_type($localPath);
    return 'data:' . $mime . ';base64,' . base64_encode($data);
}

if (!function_exists('getDepartmentThemeColor')) {
function getDepartmentThemeColor(string $department) {
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

$themeColor = getDepartmentThemeColor($coordinatorDepartment);


if (isset($_GET['download']) && $_GET['download'] === '1' && $selectedStudent && $selectedDate) {
    $studentDepartment = $studentInfo['department'] ?? '';
    $themeColor = getDepartmentThemeColor($studentDepartment);
    $filename = sprintf('Daily_Report_%s_%s.pdf', preg_replace('/[^A-Za-z0-9_-]/', '_', $selectedStudent), $selectedDate);
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    echo '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Daily Report</title>
<style>
@page { size: A4; margin: 15mm; }
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: "Segoe UI", Arial, sans-serif; font-size: 11pt; line-height: 1.5; color: #222; background: #f4f0ff; }
.report-container { width: 100%; max-width: 700px; margin: 0 auto; background: #fff; padding: 30px; }
.report-header { text-align: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 3px solid ' . $themeColor . '; }
.report-header h1 { font-size: 24pt; margin: 0 0 8px 0; color: ' . $themeColor . '; text-transform: uppercase; letter-spacing: 2px; }
.report-meta { font-size: 10pt; color: #555; line-height: 1.6; }
.report-meta span { margin: 0 12px; }
.system-box { margin: 20px auto 30px auto; background: #e9ecef; border: 2px solid ' . $themeColor . '; border-radius: 12px; padding: 20px; max-width: 400px; text-align: center; }
.system-box-title { font-size: 14pt; font-weight: bold; color: ' . $themeColor . '; margin-bottom: 6px; }
.system-box-desc { font-size: 10pt; color: #333; }
.report-section { margin-bottom: 20px; padding: 18px; background: #f8f9fa; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.04); }
.report-section h2 { font-size: 12pt; margin: 0 0 12px 0; color: ' . $themeColor . '; border-bottom: 1px solid #ddd; padding-bottom: 8px; }
.image-grid { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; margin-bottom: 15px; }
.image-grid img { max-width: 160px; max-height: 100px; border: 2px solid #bbb; border-radius: 6px; object-fit: cover; }
.report-content { font-size: 10pt; line-height: 1.6; color: #333; white-space: pre-wrap; text-align: justify; background: #fff; border-radius: 6px; padding: 14px; border: 1px solid #ddd; }
.report-footer { text-align: center; margin-top: 25px; padding-top: 15px; border-top: 2px solid ' . $themeColor . '; font-size: 9pt; color: #666; }
@media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } .report-section { break-inside: avoid; } }
</style>
</head>
<body>';

    echo '<div class="report-container">';
    echo '<div class="report-header">';
    echo '<h1>Daily Report</h1>';
    echo '<div class="report-meta">';
    echo '<span><strong>Date:</strong> ' . htmlspecialchars($selectedDate) . '</span>';
    echo '<span><strong>Student ID:</strong> ' . htmlspecialchars($selectedStudent) . '</span>';
    echo '<span><strong>Name:</strong> ' . htmlspecialchars($studentInfo['name'] ?? 'N/A') . '</span>';
    echo '<span><strong>Department:</strong> ' . htmlspecialchars($studentDepartment ?: 'Not set') . '</span>';
    echo '</div>';
    echo '</div>';

    echo '<div class="system-box">';
    echo '<div class="system-box-title">System Functions</div>';
    echo '<div class="system-box-desc">Login, Add Daily Report, Upload Images, View History, Export Report</div>';
    echo '</div>';

    if (empty($reportEntries)) {
        echo '<p>No entries found for this date.</p>';
    } else {
        foreach ($reportEntries as $entry) {
            $timeLabel = date('g:i A', strtotime($entry['created_at']));
            echo '<div class="report-section">';
            echo '<h2>Report Entry - ' . $timeLabel . '</h2>';

            $images = getEntryImagePaths($entry);
            if (!empty($images)) {
                echo '<div class="image-grid">';
                foreach ($images as $imagePath) {
                    $dataUri = getImageDataUri($imagePath);
                    if ($dataUri) {
                        echo '<img src="' . $dataUri . '" alt="Daily Report Image">';
                    }
                }
                echo '</div>';
            }

            if (!empty($entry['note'])) {
                echo '<div class="report-content">' . nl2br(htmlspecialchars($entry['note'])) . '</div>';
            }
            echo '</div>';
        }
    }
    echo '<div class="report-footer">Generated on ' . date('F j, Y g:i A') . ' by Coordinator</div>';
    echo '</div>';
    echo '</body></html>';
    exit;
}

include __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <div class="preloader flex-column justify-content-center align-items-center" style="background: linear-gradient(to bottom, blue, yellow);">
    <img class="animation__shake" src="../assets/img/users/OIP.webp" alt="Preloader" height="150" width="150" style="border-radius: 50%;">
  </div>

<?php include __DIR__ . '/../includes/navbar.php'; ?>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<link rel="stylesheet" href="../assets/css/coodinator/student_reports.css">

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Student Daily Reports</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="coordinator.php">Home</a></li>
            <li class="breadcrumb-item active">Daily Reports</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-4">
          <div class="card card-info">
            <div class="card-header">
              <h3 class="card-title">Select Student</h3>
            </div>
            <div class="card-body">
              <form method="get">
                <div class="form-group">
                  <label>Department</label>
                  <select class="form-control" id="departmentFilter" onchange="filterStudents()">
                    <option value="">All Departments</option>
                    <?php foreach (array_unique(array_column($students, 'department')) as $dept): ?>
                      <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Student</label>
                  <select class="form-control" name="student" id="studentSelect">
                    <option value="">-- Select Student --</option>
                    <?php foreach ($students as $student): ?>
                      <option value="<?= htmlspecialchars($student['student_id']) ?>" data-dept="<?= htmlspecialchars($student['department']) ?>" <?= $selectedStudent === $student['student_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($student['name']) ?> (<?= htmlspecialchars($student['student_id']) ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Date</label>
                  <input type="date" class="form-control" name="date" value="<?= htmlspecialchars($selectedDate) ?>">
                </div>
                <button type="submit" class="btn btn-primary btn-block">View Report</button>
              </form>
            </div>
          </div>
          
          <?php if (!empty($availableDates)): ?>
          <div class="card card-secondary mt-3">
            <div class="card-header">
              <h3 class="card-title">Available Dates</h3>
            </div>
            <div class="card-body p-0">
              <div class="list-group list-group-flush">
                <?php foreach ($availableDates as $date): ?>
                  <a href="?student=<?= htmlspecialchars($selectedStudent) ?>&date=<?= htmlspecialchars($date) ?>" class="list-group-item list-group-item-action <?= $date === $selectedDate ? 'active' : '' ?>">
                    <?= htmlspecialchars($date) ?>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          <?php endif; ?>
        </div>
        
        <div class="col-md-8">
          <?php if ($selectedStudent && $studentInfo): ?>
            <div class="report-preview" style="--theme-color: <?= htmlspecialchars($themeColor) ?>;">
              <div class="report-header">
                <h5 class="mb-2">Daily Report Preview</h5>
                <div class="report-meta">
                  <span><strong>Date:</strong> <?= htmlspecialchars($selectedDate) ?></span>
                  <span><strong>Student ID:</strong> <?= htmlspecialchars($selectedStudent) ?></span>
                  <span><strong>Name:</strong> <?= htmlspecialchars($studentInfo['name']) ?></span>
                  <span><strong>Department:</strong> <?= htmlspecialchars($studentInfo['department'] ?? 'N/A') ?></span>
                </div>
              </div>
              
              <?php if (empty($reportEntries)): ?>
                <div class="alert alert-warning">No daily reports found for this date.</div>
              <?php else: ?>
                <?php foreach ($reportEntries as $entry): ?>
                  <div class="report-entry">
                    <h3>Report Entry - <?= date('g:i A', strtotime($entry['created_at'])) ?></h3>
                    
                    <?php
                    $images = getEntryImagePaths($entry);
                    if (!empty($images)):
                    ?>
                      <div class="entry-image">
                        <?php foreach ($images as $imagePath): ?>
                          <?php $dataUri = getImageDataUri($imagePath); ?>
                          <?php if ($dataUri): ?>
                            <img src="<?= htmlspecialchars($dataUri) ?>" alt="Report Image">
                          <?php endif; ?>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($entry['note'])): ?>
                      <div class="entry-details"><?= nl2br(htmlspecialchars($entry['note'])) ?></div>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
              
              <div class="entry-footer">
                <?php if (!empty($reportEntries)): ?>
                  <a href="?student=<?= htmlspecialchars($selectedStudent) ?>&date=<?= htmlspecialchars($selectedDate) ?>&download=1" class="btn btn-success">
                    <i class="fas fa-file-pdf mr-1"></i> Download PDF Report
                  </a>
                <?php endif; ?>
              </div>
            </div>
          <?php else: ?>
            <div class="alert alert-info">Please select a student to view their daily reports.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script src="../assets/js/coodinator/student_reports.js"></script>
<?php include __DIR__ . '/../includes/script.php'; ?>

</body>
</html>