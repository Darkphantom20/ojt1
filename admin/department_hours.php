<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Department Required Hours';
include __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../csrf_protection.php';
require_once __DIR__ . '/../dbconnection.php';

$departmentOptions = [
  // College of Education
  'Bachelor of Elementary Education (BEEd)',
  'Bachelor of Physical Education (BPEd)',
  'Bachelor of Secondary Education (BSEd) - Major in English',
  'Bachelor of Secondary Education (BSEd) - Major in Filipino',
  'Bachelor of Secondary Education (BSEd) - Major in Mathematics',
  'Bachelor of Secondary Education (BSEd) - Major in Social Studies',

  // College of Arts
  'Bachelor of Arts in English Language Studies (BAELS)',

  // College of Agriculture & Forestry
  'Bachelor of Science in Agriculture (BSA) - Major in Animal Science',
  'Bachelor of Science in Agriculture (BSA) - Major in Crop Science',
  'Bachelor of Science in Agriculture (BSA) - Major in Plant Pathology',
  'Bachelor of Science in Agriculture (BSA) - Major in Soil Science',
  'Bachelor of Science in Forestry (BSF)',

  // College of Business & Management
  'Bachelor of Science in Agribusiness (BSAB)',
  'Bachelor of Science in Business Administration (BSBA) - Major in Financial Management',
  'Bachelor of Science in Hospitality Management (BSHM)',

  // College of Computing Studies
  'Bachelor of Science in Computer Science (BSCS)',
  'Bachelor of Science in Information Systems (BSIS)',

  // College of Criminology
  'Bachelor of Science in Criminology (BSCrim)'
];

$createHoursTable = "CREATE TABLE IF NOT EXISTS department_required_hours (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    department TEXT NOT NULL UNIQUE,
    required_hours INTEGER NOT NULL DEFAULT 0,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
)";

$feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($csrfToken)) {
        $feedback = '<div class="alert alert-danger">Security validation failed.</div>';
    } else {
        $dept = trim($_POST['department'] ?? '');
        $hours = intval($_POST['required_hours'] ?? 0);
        if ($dept === '' || $hours < 0) {
            $feedback = '<div class="alert alert-danger">Please provide valid department and hours (>= 0).</div>';
        } else {
            // Use MySQL/MariaDB upsert syntax
            $stmt = $conn->prepare('INSERT INTO department_required_hours (department, required_hours) VALUES (?, ?) ON DUPLICATE KEY UPDATE required_hours = VALUES(required_hours)');
            if ($stmt->execute([$dept, $hours])) {
                // Keep student required hours in sync for selected department
                $updateStudentHours = $conn->prepare('UPDATE students SET required_ojt_hours = ? WHERE department = ?');
                $updateStudentHours->execute([$hours, $dept]);

                $feedback = '<div class="alert alert-success">Saved required hours for ' . htmlspecialchars($dept, ENT_QUOTES) . ' and updated matching students.</div>';
            } else {
                $error = $stmt->error;
                $feedback = '<div class="alert alert-danger">Unable to save department hours. ' . htmlspecialchars($error, ENT_QUOTES) . '</div>';
            }
        }
    }
}

$deptData = [];
$result = $conn->query('SELECT id, department, required_hours, updated_at FROM department_required_hours ORDER BY department ASC');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $deptData[$row['department']] = $row;
    }
}

// Fill all configured department options so admin sees the full list.
foreach ($departmentOptions as $deptOption) {
    if (!isset($deptData[$deptOption])) {
        $deptData[$deptOption] = [
            'id' => null,
            'department' => $deptOption,
            'required_hours' => 0,
            'updated_at' => ''
        ];
    }
}

// Add any existing extra departments not in option list to the display as well.
// Already present in $deptData by query result.

// Keep the current order by department name ascending.
ksort($deptData);
?>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <div class="preloader flex-column justify-content-center align-items-center" style="background: linear-gradient(to bottom, blue, yellow);">
    <img class="animation__shake" src="../assets/img/users/OIP.webp" alt="Preloader" height="150" width="150" style="border-radius: 50%;">
  </div>

  <?php include __DIR__ . '/../includes/navbar_admin.php'; ?>
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Department Required Hours</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="admin.php">Home</a></li>
              <li class="breadcrumb-item active">Department Hours</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <?php if ($feedback) echo $feedback; ?>

        <div class="card card-info card-outline">
          <div class="card-header bg-gradient-primary">
            <h3 class="card-title">Current Department Requirements</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse"><i class="fas fa-minus"></i></button>
            </div>
          </div>
          <div class="card-body">
            <p class="text-muted">Adjust per-department total hour requirement directly and save immediately.</p>
            <div class="table-responsive">
            <table class="table table-bordered table-sm table-head-fixed text-nowrap">
              <thead class="bg-gradient-info text-white">
                <tr>
                  <th style="width:35%;">Department</th>
                  <th style="width:20%;">Required Hours</th>
                  <th style="width:25%;">Last Updated</th>
                  <th style="width:20%;" class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
              <?php if (!empty($deptData)): ?>
                <?php foreach ($deptData as $row): ?>
                  <?php $isBlank = intval($row['required_hours']) <= 0; ?>
                  <tr class="<?= $isBlank ? 'table-warning' : 'table-success' ?>">
                    <td class="font-weight-bold text-dark"><?= htmlspecialchars($row['department'], ENT_QUOTES) ?></td>
                    <td>
                      <form method="POST" class="form-inline align-items-center">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                        <input type="hidden" name="department" value="<?= htmlspecialchars($row['department'], ENT_QUOTES) ?>">
                        <div class="input-group input-group-sm" style="max-width:130px;">
                          <input type="number" name="required_hours" value="<?= intval($row['required_hours']) ?>" min="0" class="form-control" required>
                          <div class="input-group-append"><span class="input-group-text">hrs</span></div>
                        </div>
                    </td>
                    <td>
                      <small class="font-weight-bold <?= $isBlank ? 'text-danger' : 'text-success' ?> "><?= $isBlank ? 'Not configured' : 'Configured' ?></small>
                      <div><small class="text-muted"><?= htmlspecialchars($row['updated_at'] ?: 'Never', ENT_QUOTES) ?></small></div>
                    </td>
                    <td class="text-center">
                        <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-save"></i> Save</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary ml-1 btn-reset" data-dept="<?= htmlspecialchars($row['department'], ENT_QUOTES) ?>"><i class="fas fa-undo"></i> Reset</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="4" class="text-center">No department requirements found.</td></tr>
              <?php endif; ?>
              </tbody>
            </table>
            </div>
          </div>
          <div class="card-footer bg-white">
            <small class="text-muted">Use the inline save buttons to store new values for each department.</small>
          </div>
        </div>

      </div>
    </section>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
  <?php include __DIR__ . '/../includes/script.php'; ?>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-reset').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var row = this.closest('tr');
        if (!row) return;
        var hoursInput = row.querySelector('input[name="required_hours"]');
        if (hoursInput) {
          hoursInput.value = 0;
        }
      });
    });
  });
</script>
</body>
</html>