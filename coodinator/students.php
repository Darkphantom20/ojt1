<!DOCTYPE html>
<html lang="en">
<?php
session_start();

// Check if coordinator is logged in
if (empty($_SESSION['coordinator_logged_in']) || $_SESSION['coordinator_logged_in'] !== true) {
    header('Location: /ojt1/index.php');
    exit;
}

$coordinatorId = $_SESSION['coordinator_id'] ?? null;
if (!$coordinatorId) {
    header('Location: /ojt1/index.php');
    exit;
}

$pageTitle = 'Coordinator Student Tracker';
include __DIR__ . '/../dbconnection.php';
include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="../assets/css/coodinator/students.css">
<?php

$coordinatorDepartment = trim($_SESSION['coordinator_department'] ?? '');
$coordinatorTheme = 'default';
if (function_exists('getDepartmentThemeClass')) {
    $coordinatorTheme = getDepartmentThemeClass($coordinatorDepartment);
}

// Get departments assigned to this coordinator
try {
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
            $stmt2->bind_result($studentIdRow);
            while ($stmt2->fetch()) {
                $assignedStudentIds[] = $studentIdRow;
            }
        }
    }

    if (empty($assignedDepartments)) {
        $coordinatorDept = trim($_SESSION['coordinator_department'] ?? '');
        if ($coordinatorDept !== '') {
            $assignedDepartments = getDepartmentsForCollege($coordinatorDept);
            if (empty($assignedDepartments)) {
                $assignedDepartments = [$coordinatorDept];
            }
            $autoAssigned = true;
        }
    }

    if (empty($assignedDepartments) && empty($assignedStudentIds)) {
        $students = [];
        $departmentGroups = [];
    } else {
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
        
        // ============================================
        // FIX: Only show students with 'approved' status
        // ============================================
        $stmt = $conn->prepare("SELECT s.student_id, s.name, s.department, s.required_ojt_hours, s.avatar,
                   COALESCE(sp.total_hours_completed, 0) as completed_hours,
                   COALESCE(ar.total_attended_hours, 0) as attendance_hours
            FROM students s
            LEFT JOIN student_progress sp ON s.student_id = sp.student_id
            LEFT JOIN (
                SELECT student_id, SUM(hours_worked) AS total_attended_hours
                FROM attendance_records
                GROUP BY student_id
            ) ar ON s.student_id = ar.student_id
            WHERE ($whereClause) AND s.registration_status = 'approved'
            ORDER BY s.department, s.name");
        if (!$stmt) {
            throw new Exception('Prepare failed for students query');
        }

        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();

        if (method_exists($stmt, 'get_result')) {
            $result = $stmt->get_result();
            $students = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        } else {
            $stmt->bind_result($student_id, $name, $department, $required_ojt_hours, $avatar, $completed_hours, $attendance_hours);
            $students = [];
            while ($stmt->fetch()) {
                $students[] = [
                    'student_id' => $student_id,
                    'name' => $name,
                    'department' => $department,
                    'required_ojt_hours' => $required_ojt_hours,
                    'avatar' => $avatar,
                    'completed_hours' => $completed_hours,
                    'attendance_hours' => $attendance_hours,
                ];
            }
        }

        // Calculate progress for each student
        foreach ($students as &$student) {
            $requiredHours = intval($student['required_ojt_hours']) ?: 480;
            $completed = round(floatval($student['completed_hours']), 2);
            $attendance = round(floatval($student['attendance_hours'] ?? 0), 2);
            $totalCompleted = max($completed, $attendance);
            $student['remaining'] = max(0, $requiredHours - $totalCompleted);
            $student['percent'] = $requiredHours > 0 ? round(($totalCompleted / $requiredHours) * 100, 2) : 0;

            if ($student['percent'] >= 80) {
                $student['status'] = 'On Track';
                $student['label'] = 'success';
            } elseif ($student['percent'] >= 50) {
                $student['status'] = 'At Risk';
                $student['label'] = 'warning';
            } elseif ($student['percent'] > 0) {
                $student['status'] = 'In Progress';
                $student['label'] = 'info';
            } else {
                $student['status'] = 'Just Started';
                $student['label'] = 'secondary';
            }
        }
        unset($student);

        // Group students by department
        $departmentGroups = [];
        foreach ($students as $student) {
            $dept = $student['department'] ?: 'Unassigned';
            if (!isset($departmentGroups[$dept])) {
                $departmentGroups[$dept] = [];
            }
            $departmentGroups[$dept][] = $student;
        }
    }

} catch (Exception $e) {
    $students = [];
    $departmentGroups = [];
    error_log("Error fetching students: " . $e->getMessage());
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
            <h1 class="m-0">Student Tracker</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="coordinator.php">Home</a></li>
              <li class="breadcrumb-item active">Student Tracker</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <div class="card card-secondary">
          <div class="card-header">
            <h3 class="card-title">Department Student Tracker</h3>
            <div class="card-tools">
              <button id="refresh-student-progress" class="btn btn-tool" title="Refresh table">
                <i class="fas fa-sync-alt"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="student-progress-table" class="table table-hover table-sm">
                <thead>
                  <tr class="text-center align-middle" style="background-color: #f8f9fa;">
                    <th class="align-middle py-3"><i class="fas fa-user-graduate fa-lg"></i> Student</th>
                    <th class="align-middle py-3"><i class="fas fa-check-circle fa-lg text-success"></i> Completed</th>
                    <th class="align-middle py-3"><i class="fas fa-hourglass-half fa-lg text-warning"></i> Remaining</th>
                    <th class="align-middle py-3"><i class="fas fa-chart-line fa-lg text-info"></i> Progress</th>
                    <th class="align-middle py-3"><i class="fas fa-flag-checkered fa-lg text-primary"></i> Status</th>
                  </tr>
                </thead>
                <tbody>
                <?php if (empty($departmentGroups)): ?>
                <tr>
                  <td colspan="5" class="text-center text-muted py-5">
                    <i class="fas fa-info-circle fa-2x mb-2"></i>
                    <p class="mb-1">No departments assigned yet.</p>
                    <small>Contact administrator to assign departments to your account.</small>
                  </td>
                </tr>
                <?php else: ?>
                <?php foreach ($departmentGroups as $department => $deptStudents): ?>
                <tr class="table-primary" style="background-color: #e3f2fd !important;">
                  <td colspan="5" style="border-top: 2px solid #007bff;">
                    <strong><i class="fas fa-building fa-lg mr-2"></i><?= htmlspecialchars($department) ?></strong>
                    <span class="badge badge-primary ml-2"><?= count($deptStudents) ?> students</span>
                  </td>
                </tr>
                <?php foreach ($deptStudents as $student): ?>
                <?php 
                  $displayCompleted = round(max(floatval($student['completed_hours']), floatval($student['attendance_hours'] ?? 0)), 2);
                  $requiredHours = intval($student['required_ojt_hours']) ?: 480;
                  $barWidth = max(0, min(100, $student['percent']));
                  $displayWidth = $barWidth > 0 ? max($barWidth, 3) : 0;
                  $daysRemaining = $displayCompleted > 0 ? ceil($student['remaining'] / 8) : 0;
                ?>
                <?php
                  $avatarUrl = '';
                  if (!empty($student['avatar'])) {
                      $avatarPath = ltrim($student['avatar'], '/');
                      if (file_exists(__DIR__ . '/../' . $avatarPath)) {
                          $avatarUrl = '/ojt1/' . $avatarPath;
                      }
                  }
                ?>
                <tr class="align-middle">
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="mr-2">
                        <?php if ($avatarUrl): ?>
                          <img src="<?= htmlspecialchars($avatarUrl) ?>" class="img-circle elevation-2" alt="Student Avatar" style="width:48px; height:48px; object-fit:cover;">
                        <?php else: ?>
                          <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:48px; height:48px; font-size:1rem;">
                            <?= htmlspecialchars(substr($student['name'], 0, 1)) ?>
                          </div>
                        <?php endif; ?>
                      </div>
                      <div>
                        <strong><?= htmlspecialchars($student['name']) ?></strong><br>
                        <small class="text-muted"><?= htmlspecialchars($student['student_id']) ?></small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="text-center">
                      <span class="h5 text-success font-weight-bold"><?= number_format($displayCompleted, 2) ?></span>
                      <small class="text-muted d-block">hours</small>
                    </div>
                  </td>
                  <td>
                    <div class="text-center">
                      <span class="h5 text-warning font-weight-bold"><?= number_format($student['remaining'], 0) ?></span>
                      <small class="text-muted d-block">hours left</small>
                    </div>
                  </td>
                  <td style="min-width: 180px;">
                    <div class="progress mb-1" style="height: 24px; border-radius: 12px; background-color: #e9ecef;">
                      <div class="progress-bar bg-<?= $student['label'] ?>" role="progressbar" 
                           style="width: <?= $displayWidth ?>%; border-radius: 12px;" 
                           aria-valuenow="<?= $barWidth ?>" aria-valuemin="0" aria-valuemax="100">
                        <span class="font-weight-bold" style="font-size: 0.75rem;"><?= $barWidth > 0 ? $barWidth . '%' : '0%' ?></span>
                      </div>
                    </div>
                    <div class="d-flex justify-content-between small text-muted">
                      <span><?= number_format($displayCompleted, 0) ?>h</span>
                      <span><?= $requiredHours ?>h goal</span>
                    </div>
                  </td>
                  <td class="text-center">
                    <?php if ($student['status'] === 'On Track'): ?>
                      <span class="enhanced-badge badge-success badge-pill px-3 py-2">
                        <i class="fas fa-check-circle mr-1"></i> On Track
                      </span>
                    <?php elseif ($student['status'] === 'At Risk'): ?>
                      <span class="enhanced-badge badge-warning badge-pill px-3 py-2">
                        <i class="fas fa-exclamation-triangle mr-1"></i> At Risk
                      </span>
                    <?php elseif ($student['status'] === 'In Progress'): ?>
                      <span class="enhanced-badge badge-info badge-pill px-3 py-2">
                        <i class="fas fa-spinner mr-1"></i> In Progress
                      </span>
                    <?php else: ?>
                      <span class="enhanced-badge badge-secondary badge-pill px-3 py-2">
                        <i class="fas fa-clock mr-1"></i> Just Started
                      </span>
                    <?php endif; ?>
                    <?php if ($daysRemaining > 0 && $displayCompleted > 0): ?>
                      <small class="text-muted d-block mt-1">~<?= $daysRemaining ?> days left</small>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
  <?php include __DIR__ . '/../includes/script.php'; ?>
</div>
<script src="../assets/js/coodinator/students.js"></script>
</body>
</html>