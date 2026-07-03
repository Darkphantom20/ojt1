<?php
// documentary.php - Images at top, details at bottom
session_start();
ob_start();
$pageTitle = 'Daily Documentary';

// Database connection
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'ojthub';

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

require_once __DIR__ . '/report_template.php';

// Check if student is logged in
if (empty($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true || empty($_SESSION['student_id'])) {
    $_SESSION['student_id'] = '37TC-23-A-00490';
    $_SESSION['student_name'] = 'Monerah Cambia';
    $_SESSION['student_department'] = 'Business Administration';
    $_SESSION['student_logged_in'] = true;
}

$studentId = strtoupper(trim($_SESSION['student_id']));
$today = date('Y-m-d');
$selectedDate = isset($_GET['day']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['day']) ? $_GET['day'] : $today;
$entryDate = $selectedDate;
$uploadMessage = '';

if (isset($_SESSION['documentary_message'])) {
    $uploadMessage = $_SESSION['documentary_message'];
    unset($_SESSION['documentary_message']);
}

function hasForbiddenUploadExtension(string $fileName, array $forbiddenExtensions): bool
{
    $parts = array_filter(array_map('strtolower', explode('.', $fileName)), 'strlen');
    foreach ($parts as $part) {
        if (in_array($part, $forbiddenExtensions, true)) {
            return true;
        }
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entryDate = isset($_POST['entry_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['entry_date']) ? $_POST['entry_date'] : $today;
    $entryTime = time();

    if (isset($_POST['delete_id'])) {
        $deleteId = (int)$_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM student_documentary WHERE id = ? AND student_id = ?");
        $stmt->bind_param('is', $deleteId, $studentId);
        $stmt->execute();
        $stmt->close();

        header('Location: documentary.php?day=' . $entryDate);
        exit;
    }

    $savedImage = null;
    $savedImagePathsJson = null;
    $savedDoc = null;
    $note = null;
    $uploadErrors = [];

    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $forbiddenExtensions = ['php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'js', 'sh', 'bat', 'cmd', 'com', 'jar', 'vbs', 'jsp', 'asp', 'aspx'];

    $isDailyReport = isset($_POST['action']) && $_POST['action'] === 'daily_report_submit';
    if ($isDailyReport) {
        $note = trim($_POST['report_note'] ?? '');
        $reportImages = $_FILES['report_images'] ?? null;
        $imageCount = 0;
        if ($reportImages && isset($reportImages['name']) && is_array($reportImages['name'])) {
            $imageCount = count(array_filter($reportImages['name']));
        }

        if ($imageCount < 1 || $imageCount > 3) {
            $uploadErrors[] = 'Please upload between 1 and 3 pictures.';
        }

        if ($note === '') {
            $uploadErrors[] = 'Enter the daily report details.';
        }

        if ($imageCount > 0 && empty($uploadErrors)) {
            $savedPaths = [];
            $allowedImageExtensions = ['jpg', 'jpeg', 'png'];
            $allowedImageMimeTypes = ['image/jpeg', 'image/png'];

            for ($i = 0; $i < $imageCount; $i++) {
                if ($reportImages['error'][$i] !== UPLOAD_ERR_OK) {
                    $uploadErrors[] = 'One of the selected pictures could not be uploaded.';
                    continue;
                }

                $fileName = basename($reportImages['name'][$i]);
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if (hasForbiddenUploadExtension($fileName, $forbiddenExtensions)) {
                    $uploadErrors[] = 'Unsupported image type detected. Only JPG and PNG are allowed.';
                    continue;
                }

                if (!in_array($ext, $allowedImageExtensions, true)) {
                    $uploadErrors[] = 'Unsupported image type detected. Only JPG and PNG are allowed.';
                    continue;
                }

                $tmpName = $reportImages['tmp_name'][$i];
                $imageInfo = getimagesize($tmpName);
                $mimeType = $imageInfo['mime'] ?? mime_content_type($tmpName);
                if (!in_array($mimeType, $allowedImageMimeTypes, true)) {
                    $uploadErrors[] = 'Unsupported image type detected. Only JPG and PNG are allowed.';
                    continue;
                }

                $destName = $entryTime . '_report_img_' . $i . '_' . preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $fileName);
                $destPath = $uploadDir . '/' . $destName;
                if (move_uploaded_file($tmpName, $destPath)) {
                    $savedPaths[] = 'students/uploads/' . $destName;
                } else {
                    $uploadErrors[] = 'Failed to save one of the uploaded pictures.';
                }
            }

            if (!empty($savedPaths)) {
                $savedImage = $savedPaths[0];
                $savedImagePathsJson = json_encode($savedPaths);
            }
        }
    }

    if ($isDailyReport && empty($uploadErrors) && !$savedImagePathsJson) {
        $uploadErrors[] = 'Unable to save any report pictures.';
    }

    if (empty($uploadErrors) && $isDailyReport) {
        $stmt = $conn->prepare("INSERT INTO student_documentary (student_id, entry_date, image_path, image_paths, document_path, note) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssss', $studentId, $entryDate, $savedImage, $savedImagePathsJson, $savedDoc, $note);
        $stmt->execute();
        $stmt->close();

        $_SESSION['documentary_message'] = 'Entry saved successfully.';
        header('Location: documentary.php?day=' . $entryDate);
        exit;
    }

    $uploadMessage = implode(' ', $uploadErrors);
}

$dateCounts = [];
$stmt = $conn->prepare("SELECT entry_date, COUNT(*) AS num FROM student_documentary WHERE student_id = ? AND note IS NOT NULL AND note <> '' GROUP BY entry_date ORDER BY entry_date DESC");
$stmt->bind_param('s', $studentId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $dateCounts[$row['entry_date']] = (int)$row['num'];
}
$stmt->close();

$selectedEntries = [];
if ($selectedDate) {
    $stmt = $conn->prepare("SELECT id, entry_date, note, image_path, image_paths, document_path, created_at FROM student_documentary WHERE student_id = ? AND entry_date = ? AND note IS NOT NULL AND note <> '' ORDER BY created_at DESC");
    $stmt->bind_param('ss', $studentId, $selectedDate);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $selectedEntries[] = $row;
    }
    $stmt->close();
}

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

$previewThemeColor = getDepartmentThemeColor($_SESSION['student_department'] ?? '');

// Handle download report - use the shared report template
if ($selectedDate && isset($_GET['download_report']) && $_GET['download_report'] === '1') {
    $studentDepartment = trim($_SESSION['student_department'] ?? '');
    $studentName = trim($_SESSION['student_name'] ?? $studentId);
    $studentEmail = trim($_SESSION['student_email'] ?? '');
    $themeColor = getDepartmentThemeColor($studentDepartment);
    $student = [
        'student_id' => $studentId,
        'name' => $studentName,
        'department' => $studentDepartment,
        'email' => $studentEmail,
        'program' => $_SESSION['student_program'] ?? $studentDepartment,
        'organization' => $_SESSION['student_organization'] ?? '',
        'org_address' => $_SESSION['student_org_address'] ?? '',
        'supervisor' => $_SESSION['student_supervisor'] ?? '',
        'supervisor_title' => $_SESSION['student_supervisor_title'] ?? '',
        'training_period' => $_SESSION['student_training_period'] ?? '',
        'hours_rendered' => $_SESSION['student_hours_rendered'] ?? '',
        'total_hours' => $_SESSION['student_total_hours'] ?? '',
    ];

    $filename = sprintf('Narrative_Report_%s_%s.html', preg_replace('/[^A-Za-z0-9_-]/', '_', $studentId), $selectedDate);
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    echo renderReportTemplate($student, $selectedDate, $selectedEntries, $themeColor);
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

<link rel="stylesheet" href="../assets/css/student-documentary.css">

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Daily Documentary</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
            <li class="breadcrumb-item active">Daily Documentary</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
          <?php if ($uploadMessage): ?>
            <div class="alert alert-info"><?= htmlspecialchars($uploadMessage) ?></div>
          <?php endif; ?>

          <div class="mb-3 d-flex flex-wrap align-items-center justify-content-between">
            <button type="button" id="open-daily-report-modal" class="btn btn-success mb-2">
              <i class="fas fa-plus-circle mr-1"></i> Add Daily Report
            </button>
            <div class="btn-group">
              <a href="?day=<?= date('Y-m-d') ?>" class="btn btn-outline-secondary btn-sm <?= ($selectedDate == date('Y-m-d')) ? 'active' : '' ?>">Today</a>
              <a href="?day=<?= date('Y-m-d', strtotime('-1 day')) ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-chevron-left"></i></a>
              <a href="?day=<?= date('Y-m-d', strtotime('+1 day')) ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-chevron-right"></i></a>
              <span class="btn btn-light btn-sm disabled"><?= htmlspecialchars($selectedDate) ?></span>
            </div>
          </div>

          <div class="card card-info mb-3">
            <div class="card-header"><h3 class="card-title">Daily Documentary</h3></div>
            <div class="card-body">
              <h5>Daily Reports</h5>

              <div class="table-responsive">
                <table class="table table-hover table-striped">
                  <thead class="thead-dark">
                    <tr>
                      <th style="width: 20%;"><i class="fas fa-clock"></i> Time</th>
                      <th style="width: 60%;"><i class="fas fa-file-alt"></i> Daily Report</th>
                      <th style="width: 20%;"><i class="fas fa-tools"></i> Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($selectedEntries)): ?>
                      <tr>
                        <td colspan="3" class="text-center text-muted py-4"><i class="fas fa-inbox"></i> No daily reports for this date.</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($selectedEntries as $entry): ?>
                        <tr>
                          <td>
                            <small class="text-muted server-time" data-timestamp="<?= $entry['created_at'] ?>"></small>
                          </td>
                          <td>
                            <?php if (!empty($entry['document_path'])): ?>
                              <a href="/ojt1/<?= ltrim($entry['document_path'], '/') ?>" target="_blank" class="text-primary font-weight-bold">
                                <i class="fas fa-file-alt mr-1"></i><?= htmlspecialchars(basename($entry['document_path'])) ?>
                              </a>
                            <?php else: ?>
                              <span class="badge badge-info"><i class="fas fa-sticky-note mr-1"></i> Daily Report</span>
                              <span class="text-muted ml-2"><?= htmlspecialchars(substr($entry['note'] ?? '', 0, 80)) ?>...</span>
                            <?php endif; ?>
                          </td>
                          <td class="text-center">
                            <a href="?day=<?= htmlspecialchars($selectedDate) ?>&download_report=1" class="btn btn-outline-secondary btn-sm mb-1" title="Download Narrative Report">
                              <i class="fas fa-file-download"></i>
                            </a>
                            <button type="button" class="btn btn-outline-danger btn-sm delete-entry-btn" data-id="<?= $entry['id'] ?>" title="Delete report">
                              <i class="fas fa-trash"></i>
                            </button>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Modal -->
<div class="modal fade" id="dailyReportModal" tabindex="-1" role="dialog" aria-labelledby="dailyReportModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="dailyReportModalLabel">Add Daily Report</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="dailyReportForm" method="post" enctype="multipart/form-data">
        <div class="modal-body">
          <input type="hidden" name="action" value="daily_report_submit">
          <input type="hidden" name="entry_date" value="<?= htmlspecialchars($selectedDate) ?>">
          <div id="dailyReportStep1">
            <div class="form-group">
              <label for="report_images"><strong>Step 1: Choose 1-3 pictures</strong></label>
              <input type="file" name="report_images[]" id="report_images" class="form-control-file" accept=".jpg,.jpeg,.png" multiple>
              <small class="form-text text-muted">Upload between 1 and 3 images. JPG, JPEG, PNG only.</small>
              <div class="invalid-feedback d-none" id="reportImagesError"></div>
            </div>
          </div>
          <div id="dailyReportStep2" style="display:none;">
            <div class="form-group">
              <label for="report_note"><strong>Step 2: Report Details</strong></label>
              <textarea name="report_note" id="report_note" class="form-control" rows="5" placeholder="Describe your activities and progress for the day..."></textarea>
              <small class="form-text text-muted">Provide details for the selected images and what you did today.</small>
              <div class="invalid-feedback d-none" id="reportNoteError"></div>
            </div>
          </div>
          <div id="dailyReportStep3" style="display:none;">
            <div id="previewDocumentLayout" class="doc-preview" style="--doc-preview-color: <?= htmlspecialchars($previewThemeColor ?: '#2c3e50') ?>;">
              <div class="report-header">
                <h5 class="mb-2">Daily Report Preview</h5>
                <div class="report-meta">
                  <span id="previewHeaderDate"></span>
                  <span id="previewHeaderStudent"></span>
                  <span id="previewHeaderDepartment"></span>
                </div>
              </div>
              <div class="report-entry">
                <h3>Report Entry Preview</h3>
                <div class="entry-image" id="previewImages"></div>
                <div class="entry-details" id="previewNote"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-light" id="dailyReportBack" style="display:none;">Back</button>
          <div>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="dailyReportNext">Next</button>
            <button type="button" class="btn btn-info" id="dailyReportPreview" style="display:none;">Preview Report</button>
            <button type="submit" class="btn btn-success" id="dailyReportSubmit" style="display:none;">Save Report</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php include __DIR__ . '/../includes/script.php'; ?>

<script>
  window.studentDocumentaryConfig = {
    selectedDate: <?= json_encode((string) $selectedDate) ?>,
    today: <?= json_encode((string) $today) ?>,
    studentId: <?= json_encode((string) $studentId) ?>,
    studentDepartment: <?= json_encode((string) ($_SESSION['student_department'] ?? 'Not set')) ?>
  };
</script>
<script src="../assets/js/student-documentary.js"></script>
</div>
</body>
</html>