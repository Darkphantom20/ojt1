<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Admin Dashboard';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../dbconnection.php';

// Fetch recent students
$recentStudents = [];
try {
    $stmt = $conn->prepare("SELECT s.student_id, s.name, s.email, s.enrolled_at AS created_at, COALESCE(sp.total_hours_completed, 0) AS completed_hours, COALESCE(s.required_ojt_hours, 480) AS required_hours FROM students s LEFT JOIN student_progress sp ON s.student_id = sp.student_id ORDER BY s.enrolled_at DESC LIMIT 5");
    $stmt->execute();
    if (method_exists($stmt, 'get_result')) {
        $result = $stmt->get_result();
        $recentStudents = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    } else {
      $createdAt = null;
      $studentId = $name = $email = $createdAt = $completedHours = $requiredHoursValue = null;
      $stmt->bind_result($studentId, $name, $email, $createdAt, $completedHours, $requiredHoursValue);
        while ($stmt->fetch()) {
            $recentStudents[] = [
                'student_id' => $studentId,
                'name' => $name,
                'email' => $email,
                'created_at' => $createdAt,
                'completed_hours' => $completedHours,
                'required_hours' => $requiredHoursValue,
            ];
        }
    }
} catch (Exception $e) {
    error_log("Error fetching recent students: " . $e->getMessage());
}

// Fetch recent coordinators
$recentCoordinators = [];
try {
    $stmt = $conn->prepare("SELECT id, full_name, email, status, created_at FROM coordinator_accounts ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    if (method_exists($stmt, 'get_result')) {
        $result = $stmt->get_result();
        $recentCoordinators = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    } else {
      $createdAt = null;
      $id = $fullName = $coordinatorEmail = $status = $createdAt = null;
      $stmt->bind_result($id, $fullName, $coordinatorEmail, $status, $createdAt);
        while ($stmt->fetch()) {
            $recentCoordinators[] = [
                'id' => $id,
                'full_name' => $fullName,
                'email' => $coordinatorEmail,
                'status' => $status,
                'created_at' => $createdAt,
            ];
        }
    }
} catch (Exception $e) {
    error_log("Error fetching recent coordinators: " . $e->getMessage());
}

// Dashboard summary stats
$configuredDepartments = 0;
$activeCoordinators = 0;
$unusedCoordinatorCodes = 0;
$departmentRequirements = [];

try {
    $configuredResult = $conn->query("SELECT COUNT(*) as cnt FROM department_required_hours WHERE required_hours > 0");
    if ($configuredResult) {
        $row = $configuredResult->fetch_assoc();
        $configuredDepartments = intval($row['cnt'] ?? 0);
    }

    $activeResult = $conn->query("SELECT COUNT(*) as cnt FROM coordinator_accounts WHERE status = 'active'");
    if ($activeResult) {
        $row = $activeResult->fetch_assoc();
        $activeCoordinators = intval($row['cnt'] ?? 0);
    }

    $unusedResult = $conn->query("SELECT COUNT(*) as cnt FROM coordinator_accounts WHERE status = 'unused'");
    if ($unusedResult) {
        $row = $unusedResult->fetch_assoc();
        $unusedCoordinatorCodes = intval($row['cnt'] ?? 0);
    }

    $deptResult = $conn->query("SELECT department, required_hours, updated_at FROM department_required_hours ORDER BY department ASC LIMIT 5");
    if ($deptResult) {
        while ($row = $deptResult->fetch_assoc()) {
            $departmentRequirements[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Error fetching dashboard stats: " . $e->getMessage());
}

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
            <h1 class="m-0">Admin Control Panel</h1>
          </div><div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="admin.php">Dashboard</a></li>
              <li class="breadcrumb-item active">Admin Control Panel</li>
            </ol>
          </div></div></div></div>
    <section class="content">
      <div class="container-fluid">
        
        <div class="row">
          <div class="col-lg-3 col-6">
            <div class="enhanced-small-box bg-dept-default">
              <div class="inner">
                <h3><?= number_format($configuredDepartments) ?></h3>
                <p>Departments Configured</p>
              </div>
              <div class="icon">
                <i class="fas fa-building"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="enhanced-small-box bg-dept-default">
              <div class="inner">
                <h3><?= number_format($activeCoordinators) ?></h3>
                <p>Active Coordinators</p>
              </div>
              <div class="icon">
                <i class="fas fa-chalkboard-teacher"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="enhanced-small-box bg-dept-default">
              <div class="inner">
                <h3><?= number_format($unusedCoordinatorCodes) ?></h3>
                <p>Unused Coord Codes</p>
              </div>
              <div class="icon">
                <i class="fas fa-key"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="enhanced-small-box bg-dept-default">
              <div class="inner">
                <h3>System</h3>
                <p>Secure Mode On</p>
              </div>
              <div class="icon">
                <i class="fas fa-shield-alt"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="card card-success">
              <div class="card-header bg-success text-white">
                <h3 class="card-title"><i class="fas fa-table"></i> Coordinator Accounts</h3>
              </div>
              <div class="card-body d-flex flex-column p-0" style="max-height: 360px;">
                <div class="table-responsive" style="overflow-y: auto; max-height: 280px;">
                  <table class="table table-head-fixed text-nowrap mb-0">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($recentCoordinators)): ?>
                      <tr>
                        <td colspan="4" class="text-center text-muted">No recent coordinators found.</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($recentCoordinators as $coord): ?>
                        <tr class="align-middle">
                          <td><?= htmlspecialchars($coord['id']) ?></td>
                          <td><?= htmlspecialchars($coord['full_name']) ?></td>
                          <td><?= htmlspecialchars($coord['email']) ?></td>
                          <td class="text-center">
                            <?php
                              $statusClass = 'badge-secondary';
                              $statusText = htmlspecialchars($coord['status']);
                              if ($coord['status'] === 'active') {
                                  $statusClass = 'badge-success';
                                  $statusText = 'Active';
                              } elseif ($coord['status'] === 'unused') {
                                  $statusClass = 'badge-warning';
                                  $statusText = 'Unused';
                              }
                            ?>
                            <span class="enhanced-badge <?= $statusClass ?>" style="font-size:0.85rem; padding:0.45rem 0.65rem;">
                              <?= $statusText ?>
                            </span>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
                </div>
                <div class="text-center p-2 border-top bg-light">
                  <a href="faculty.php" class="btn btn-primary btn-sm">View All Coordinators</a>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card card-info">
              <div class="card-header bg-info text-white">
                <h3 class="card-title"><i class="fas fa-clock"></i> Department Hours</h3>
              </div>
              <div class="card-body d-flex flex-column p-0" style="max-height: 360px;">
                <div class="table-responsive" style="overflow-y: auto; max-height: 280px;">
                  <table class="table table-head-fixed text-nowrap mb-0">
                    <thead>
                      <tr>
                        <th>Department</th>
                        <th>Hours</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (empty($departmentRequirements)): ?>
                        <tr>
                          <td colspan="3" class="text-center text-muted">No department requirements configured.</td>
                        </tr>
                      <?php else: ?>
                        <?php foreach ($departmentRequirements as $dept): ?>
                          <?php $isConfigured = intval($dept['required_hours']) > 0; ?>
                          <tr class="align-middle">
                            <td><?= htmlspecialchars($dept['department']) ?></td>
                            <td><?= number_format(intval($dept['required_hours'])) ?> hrs</td>
                            <td class="text-center">
                              <span class="badge <?= $isConfigured ? 'badge-success' : 'badge-secondary' ?>"  style="font-size:0.85rem;  padding:0.45rem 0.65rem;">
                                <?= $isConfigured ? 'Configured' : 'Not configured' ?>
                              </span>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
                <div class="text-center p-2 border-top bg-light">
                  <a href="department_hours.php" class="btn btn-info btn-sm">Manage Department Hours</a>
                </div>
              </div>
            </div>
          </div>
        </div>
        </div></section>
    </div>
  <?php include __DIR__ . '/../includes/footer.php'; ?>

  <aside class="control-sidebar control-sidebar-dark">
  </aside>
  </div>
<?php include __DIR__ . '/../includes/script.php'; ?>

<script>
  $(document).ready(function() {
    $('#generateCodeBtn').click(function() {
      // Simulate generating a random code for UI purposes
      // In reality, this should be done via a backend PHP script to save it to the database
      const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
      let part1 = '';
      let part2 = '';
      for (let i = 0; i < 4; i++) {
        part1 += chars.charAt(Math.floor(Math.random() * chars.length));
        part2 += chars.charAt(Math.floor(Math.random() * chars.length));
      }
      const newCode = `COORD-${part1}-${part2}`;
      
      $('#displayNewCode').text(newCode).hide().fadeIn('fast');
    });
  });
</script>
</body>
</html>