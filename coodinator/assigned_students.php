<?php
session_start();
require_once __DIR__ . '/../dbconnection.php';

if (empty($_SESSION['coordinator_logged_in']) || $_SESSION['coordinator_logged_in'] !== true) {
    header('Location: /ojt1/index.php?coordinator_login_failed=1');
    exit;
}

function abbreviateDepartment(string $department): string {
    $abbrevs = [
        'Bachelor of Science in Business Administration (Financial Management)' => 'BSBA',
        'Bachelor of Science in Information Technology' => 'BSIT',
        'Bachelor of Science in Computer Science' => 'BSCS',
        
    ];
    return $abbrevs[$department] ?? $department;
}

$pageTitle = 'Assigned Students';
include __DIR__ . '/../includes/header.php';

$coordinatorId = $_SESSION['coordinator_id'] ?? null;
$assignments = [];

try {
    $stmt = $conn->prepare(
        "SELECT coa.student_id,
                s.name AS student_name,
                s.department,
                COALESCE(s.email, '') AS email,
                coa.location_name,
                coa.location_address,
                coa.lat,
                coa.lng,
                coa.assigned_date
         FROM coordinator_office_assignments coa
         JOIN students s ON coa.student_id = s.student_id
         WHERE coa.coordinator_id = ?
         ORDER BY coa.assigned_date DESC"
    );
    $stmt->bind_param('i', $coordinatorId);
    $stmt->execute();

    if (method_exists($stmt, 'get_result')) {
        $assignments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $stmt->bind_result($student_id, $student_name, $department, $email, $location_name, $location_address, $lat, $lng, $assigned_date);
        while ($stmt->fetch()) {
            $assignments[] = [
                'student_id' => $student_id,
                'student_name' => $student_name,
                'department' => $department,
                'email' => $email,
                'location_name' => $location_name,
                'location_address' => $location_address,
                'lat' => $lat,
                'lng' => $lng,
                'assigned_date' => $assigned_date,
            ];
        }
    }
} catch (Exception $e) {
    error_log('Assigned students fetch failed: ' . $e->getMessage());
}
?>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="content-wrapper">
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0">Assigned Students</h1>
            </div>
            <div class="col-sm-6 text-right">
              <a href="office_deploy.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Back to Deployment
              </a>
            </div>
          </div>
        </div>
      </div>

      <section class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div class="card card-primary shadow-sm">
                <div class="card-header">
                  <h3 class="card-title"><i class="fas fa-table mr-2"></i>Assigned Students Table</h3>
                  <div class="card-tools">
                    <span class="badge badge-pill badge-success">Total: <?= count($assignments) ?></span>
                  </div>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                      <thead>
                        <tr>
                          <th style="width: 70px;">#</th>
                          <th>Student</th>
                          <th>Department</th>
                          <th>Email</th>
                          <th>Assigned Location</th>
                          <th>Coordinates</th>
                          <th>Assigned Date</th>
                          <th style="width: 120px;">Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if (empty($assignments)): ?>
                          <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                              <i class="fas fa-info-circle mr-2"></i>No assigned students yet.
                            </td>
                          </tr>
                        <?php else: ?>
                          <?php foreach ($assignments as $index => $assignment): ?>
                            <tr>
                              <td><?= $index + 1 ?></td>
                              <td>
                                <strong><?= htmlspecialchars($assignment['student_name']) ?></strong><br>
                                <small class="text-muted">ID: <?= htmlspecialchars($assignment['student_id']) ?></small>
                              </td>
                              <td><?= htmlspecialchars(abbreviateDepartment($assignment['department'])) ?></td>
                              <td><?= htmlspecialchars($assignment['email']) ?></td>
                              <td>
                                <strong><?= htmlspecialchars($assignment['location_name']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($assignment['location_address']) ?></small>
                              </td>
                              <td><?= htmlspecialchars(number_format((float) $assignment['lat'], 6)) ?>, <?= htmlspecialchars(number_format((float) $assignment['lng'], 6)) ?></td>
                              <td><?= htmlspecialchars($assignment['assigned_date'] ? date('M d, Y H:i', strtotime($assignment['assigned_date'])) : 'N/A') ?></td>
                              <td>
                                <button type="button" class="btn btn-sm btn-danger btn-block undeploy-btn" data-student-id="<?= htmlspecialchars($assignment['student_id']) ?>" data-student-name="<?= htmlspecialchars($assignment['student_name']) ?>">
                                  <i class="fas fa-user-slash mr-1"></i>Remove
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

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>

<?php include __DIR__ . '/../includes/script.php'; ?>
<script src="../assets/js/coodinator/assigned_students.js"></script>
</body>
</html>
