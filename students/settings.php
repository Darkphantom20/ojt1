<!DOCTYPE html>
<html lang="en">
<?php
session_start();

if (empty($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header('Location: /ojt1/index.php');
    exit;
}

$pageTitle = 'Student Settings';
include __DIR__ . '/../includes/header.php';

$errors = [];
$success = '';
$current = '';
$new = '';
$confirm = '';

function getStudentStorePath(): string
{
    return __DIR__ . '/students_users.json';
}

function loadStudentData(): array
{
    $file = getStudentStorePath();
    if (!file_exists($file)) {
        return [];
    }
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function saveStudentData(array $data): bool
{
    $file = getStudentStorePath();
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT)) !== false;
}

$students = loadStudentData();
$studentId = $_SESSION['student_id'] ?? null;
$studentPasswordHash = $studentId && isset($students[$studentId]) ? $students[$studentId]['password_hash'] : password_hash('student123', PASSWORD_DEFAULT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (trim($current) === '') {
        $errors[] = 'Current password is required.';
    }
    if (trim($new) === '') {
        $errors[] = 'New password is required.';
    }
    if ($new !== $confirm) {
        $errors[] = 'New password and confirmation must match.';
    }
    if ($new !== '' && strlen($new) < 8) {
        $errors[] = 'New password must be at least 8 characters long.';
    }

    if (empty($errors)) {
        if (!password_verify($current, $studentPasswordHash)) {
            $errors[] = 'Current password is incorrect.';
        } else {
            $studentPasswordHash = password_hash($new, PASSWORD_DEFAULT);
            $_SESSION['student_password_hash'] = $studentPasswordHash;
            if ($studentId && isset($students[$studentId])) {
                $students[$studentId]['password_hash'] = $studentPasswordHash;
                saveStudentData($students);
            }
            $success = 'Password updated successfully.';
            $current = $new = $confirm = '';
        }
    }
}

$displayName = $_SESSION['student_name'] ?? $_SESSION['user_name'] ?? 'Student Guest';
$displayEmail = $_SESSION['student_email'] ?? $_SESSION['user_email'] ?? 'student@example.com';
$studentDepartment = trim($_SESSION['student_department'] ?? 'Unassigned');
$departmentColorMap = [
    'Bachelor of Science in Information Systems' => ['bg' => '#00296b', 'color' => '#ffffff'],
    'Bachelor of Science in Computer Science' => ['bg' => '#1d4ed8', 'color' => '#ffffff'],
    'Bachelor of Science in Business Administration (BSBA) - Major in Financial Management' => ['bg' => '#f59e0b', 'color' => '#212121'],
    'Bachelor of Science in Hospitality Management (BSHM)' => ['bg' => '#f59e0b', 'color' => '#212121'],
    'Bachelor of Science in Agribusiness (BSAB)' => ['bg' => '#d97706', 'color' => '#ffffff'],
    'Bachelor of Science in Criminology (BSCrim)' => ['bg' => '#dc2626', 'color' => '#ffffff'],
    'Bachelor of Elementary Education (BEEd)' => ['bg' => '#0ea5e9', 'color' => '#ffffff'],
    'Bachelor of Physical Education (BPEd)' => ['bg' => '#0ea5e9', 'color' => '#ffffff'],
    'Bachelor of Secondary Education (BSEd) - Major in English' => ['bg' => '#0ea5e9', 'color' => '#ffffff'],
    'Bachelor of Secondary Education (BSEd) - Major in Mathematics' => ['bg' => '#0ea5e9', 'color' => '#ffffff'],
    'Bachelor of Secondary Education (BSEd) - Major in Filipino' => ['bg' => '#0ea5e9', 'color' => '#ffffff'],
    'Bachelor of Secondary Education (BSEd) - Major in Social Studies' => ['bg' => '#0ea5e9', 'color' => '#ffffff'],
    'Bachelor of Arts in English Language Studies (BAELS)' => ['bg' => '#0ea5e9', 'color' => '#ffffff'],
    'Bachelor of Science in Agriculture (BSA) - Major in Animal Science' => ['bg' => '#16a34a', 'color' => '#ffffff'],
    'Bachelor of Science in Agriculture (BSA) - Major in Crop Science' => ['bg' => '#16a34a', 'color' => '#ffffff'],
    'Bachelor of Science in Agriculture (BSA) - Major in Plant Pathology' => ['bg' => '#16a34a', 'color' => '#ffffff'],
    'Bachelor of Science in Agriculture (BSA) - Major in Soil Science' => ['bg' => '#16a34a', 'color' => '#ffffff'],
    'Bachelor of Science in Forestry (BSF)' => ['bg' => '#16a34a', 'color' => '#ffffff'],
];
$departmentHeaderBg = '#6c757d';
$departmentHeaderText = '#ffffff';
if (isset($departmentColorMap[$studentDepartment])) {
    $departmentHeaderBg = $departmentColorMap[$studentDepartment]['bg'];
    $departmentHeaderText = $departmentColorMap[$studentDepartment]['color'];
}

?>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <div class="preloader flex-column justify-content-center align-items-center" style="background: linear-gradient(to bottom, blue, yellow);">
    <img class="animation__shake" src="../assets/img/users/OIP.webp" alt="Preloader" height="150" width="150" style="border-radius: 50%;">
  </div>

  <?php include __DIR__ . '/../includes/navbar.php'; ?>
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="content-wrapper" style="background: #f5f9ff;">
    <section class="content d-flex align-items-center" style="min-height: calc(100vh - 140px);">
      <div class="container-fluid">
        <div class="row justify-content-center">
          <div class="col-md-6">
            <div class="card card-secondary shadow-sm rounded-lg">
              <div class="card-header"><h3 class="card-title"><i class="fas fa-cogs"></i> Settings</h3></div>
              <div class="card-body">
                <p>Manage your account security and preferences.</p>

                <?php if (!empty($success)): ?>
                  <div class="alert alert-success" role="alert"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                  <div class="alert alert-danger" role="alert"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>

                <form action="#" method="POST" autocomplete="off">
                  <div class="form-group">
                    <label>Display Name</label>
                    <input class="form-control" readonly value="<?= htmlspecialchars($displayName) ?>">
                  </div>
                  <div class="form-group">
                    <label>Email</label>
                    <input class="form-control" readonly value="<?= htmlspecialchars($displayEmail) ?>">
                  </div>
                  <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <input id="currentPassword" type="password" name="current_password" class="form-control" value="<?= htmlspecialchars($current) ?>" required>
                  </div>
                  <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input id="newPassword" type="password" name="new_password" class="form-control" value="<?= htmlspecialchars($new) ?>" required>
                  </div>
                  <div class="form-group">
                    <label for="confirmPassword">Confirm New Password</label>
                    <input id="confirmPassword" type="password" name="confirm_password" class="form-control" value="<?= htmlspecialchars($confirm) ?>" required>
                  </div>
                  <button type="submit" class="btn btn-primary">Update Password</button>
                  <button type="button" id="showQrBtn" class="btn btn-outline-info ml-2">View / Download QR Code</button>
                </form>

                <div class="modal fade" id="qrModal" tabindex="-1" role="dialog" aria-labelledby="qrModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                      <div class="modal-header" style="background-color: <?= htmlspecialchars($departmentHeaderBg) ?>; color: <?= htmlspecialchars($departmentHeaderText) ?>;">
                        <h5 class="modal-title" id="qrModalLabel">Your Student QR Code</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: <?= htmlspecialchars($departmentHeaderText) ?>; opacity: 1;">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body text-center">
                        <div id="qrCodeHolder" class="d-inline-block"></div>
                        <p class="mt-2"><strong id="qrStudentId"></strong></p>
                        <p class="small text-muted">Use this code for attendance check-in/out.</p>
                      </div>
                      <div class="modal-footer">
                        <button type="button" id="downloadQrBtn" class="btn btn-success">Download QR</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>

  <?php include __DIR__ . '/../includes/script.php'; ?>
  <script>
    window.studentSettingsConfig = {
      studentId: <?= json_encode((string) $studentId) ?>
    };
  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script src="../assets/js/student-settings.js"></script>
</div>
</body>
</html>