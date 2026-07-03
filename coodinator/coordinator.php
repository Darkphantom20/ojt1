<?php
session_start();

if (empty($_SESSION['coordinator_logged_in']) || $_SESSION['coordinator_logged_in'] !== true) {
    header('Location: /ojt1/index.php?coordinator_login_failed=1');
    exit;
}

$pageTitle = 'Coordinator Dashboard';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../dbconnection.php';

$requiredHours = 480;
$coordinatorId = $_SESSION['coordinator_id'] ?? null;
$coordinatorDepartment = trim($_SESSION['coordinator_department'] ?? '');
$coordinatorTheme = getDepartmentThemeClass($coordinatorDepartment);

$departmentDefaultHours = 480;
$departmentRequiredHours = [];
if (!empty($coordinatorDepartment)) {
    $collegeDepts = getDepartmentsForCollege($coordinatorDepartment);
    if (empty($collegeDepts)) {
        $collegeDepts = [$coordinatorDepartment];
    }
    $escapedDepts = array_map(function ($dept) use ($conn) {
        return "'" . $conn->real_escape_string($dept) . "'";
    }, $collegeDepts);
    $deptInClause = implode(',', $escapedDepts);
    $deptResult = $conn->query("SELECT department, required_hours FROM department_required_hours WHERE department IN ($deptInClause)");
    if ($deptResult) {
        while ($row = $deptResult->fetch_assoc()) {
            $departmentRequiredHours[$row['department']] = intval($row['required_hours']);
        }
    }
    if (!empty($departmentRequiredHours)) {
        $departmentDefaultHours = max($departmentRequiredHours);
    }
}

$studentDepartments = getDepartmentsForCollege($coordinatorDepartment);
if (empty($studentDepartments)) {
    $studentDepartments = [$coordinatorDepartment];
}
$escapedDepartments = array_map(function ($dept) use ($conn) {
    return "'" . $conn->real_escape_string($dept) . "'";
}, $studentDepartments);
$inClause = implode(',', $escapedDepartments);

$students = [];
$pendingApprovals = 0;
$pendingRegistrations = 0;

if ($coordinatorId) {
    try {
        $stmtPending = $conn->prepare(
            "SELECT COUNT(*) as cnt FROM students WHERE department IN ($inClause) AND registration_status = 'pending'"
        );
        if ($stmtPending) {
            $stmtPending->execute();
            $stmtPending->bind_result($pendingCount);
            if ($stmtPending->fetch()) {
                $pendingRegistrations = intval($pendingCount);
            }
            $stmtPending->close();
        }
        
        $stmt = $conn->prepare(
            "SELECT s.student_id,
                    s.name,
                    s.department,
                    COALESCE(s.required_ojt_hours, drh.required_hours, ?) AS required_hours,
                    COALESCE(sp.total_hours_completed, 0) AS total_hours_completed,
                    COALESCE(ar.total_attended_hours, 0) AS total_attended_hours,
                    COALESCE(s.email, '') AS email,
                    ar.last_clock_in,
                    ar.last_clock_out
             FROM coordinator_student_assignments csa
             JOIN students s ON csa.student_id = s.student_id
             LEFT JOIN student_progress sp ON s.student_id = sp.student_id
             LEFT JOIN department_required_hours drh ON s.department = drh.department
             LEFT JOIN (
                 SELECT student_id,
                        SUM(hours_worked) AS total_attended_hours,
                        MAX(clock_in) AS last_clock_in,
                        MAX(clock_out) AS last_clock_out
                 FROM attendance_records
                 GROUP BY student_id
             ) ar ON s.student_id = ar.student_id
             WHERE csa.coordinator_id = ?
             ORDER BY s.name ASC"
        );
        $stmt->bind_param('ii', $departmentDefaultHours, $coordinatorId);
        $stmt->execute();
        if (method_exists($stmt, 'get_result')) {
            $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $studentId = $studentName = $studentDept = $requiredHoursDB = $progressHours = $attendedHours = $email = $lastClockIn = $lastClockOut = null;
            $stmt->bind_result($studentId, $studentName, $studentDept, $requiredHoursDB, $progressHours, $attendedHours, $email, $lastClockIn, $lastClockOut);
            while ($stmt->fetch()) {
                $students[] = [
                    'student_id' => $studentId,
                    'name' => $studentName,
                    'department' => $studentDept,
                    'required_hours' => intval($requiredHoursDB),
                    'total_hours_completed' => floatval($progressHours),
                    'attendance_hours' => floatval($attendedHours),
                    'email' => $email,
                    'last_clock_in' => $lastClockIn,
                    'last_clock_out' => $lastClockOut,
                ];
            }
        }

        $stmt2 = $conn->prepare("SELECT COUNT(*) as cnt FROM coordinator_student_assignments WHERE coordinator_id = ?");
        if ($stmt2) {
            $stmt2->bind_param('i', $coordinatorId);
            $stmt2->execute();
            $stmt2->bind_result($countResult);
            if ($stmt2->fetch()) {
                $pendingApprovals = intval($countResult);
            }
            $stmt2->close();
        }
    } catch (Exception $e) {
        error_log('Coordinator portal DB query failed: ' . $e->getMessage());
        $students = [];
    }
}

if (empty($students) && !empty($coordinatorDepartment)) {
    try {
        $stmtAll = $conn->prepare(
            "SELECT s.student_id,
                    s.name,
                    s.department,
                    COALESCE(s.required_ojt_hours, drh.required_hours, ?) AS required_hours,
                    COALESCE(sp.total_hours_completed, 0) AS total_hours_completed,
                    COALESCE(ar.total_attended_hours, 0) AS total_attended_hours,
                    COALESCE(s.email, '') AS email,
                    ar.last_clock_in,
                    ar.last_clock_out
             FROM students s
             LEFT JOIN student_progress sp ON s.student_id = sp.student_id
             LEFT JOIN department_required_hours drh ON s.department = drh.department
             LEFT JOIN (
                 SELECT student_id,
                        SUM(hours_worked) AS total_attended_hours,
                        MAX(clock_in) AS last_clock_in,
                        MAX(clock_out) AS last_clock_out
                 FROM attendance_records
                 GROUP BY student_id
             ) ar ON s.student_id = ar.student_id
             WHERE s.department IN ($inClause)
             ORDER BY s.name ASC"
        );
        if ($stmtAll) {
            $stmtAll->bind_param('i', $departmentDefaultHours);
            $stmtAll->execute();
            if (method_exists($stmtAll, 'get_result')) {
                $students = $stmtAll->get_result()->fetch_all(MYSQLI_ASSOC);
            } else {
                $studentId = $studentName = $studentDept = $requiredHoursDB = $progressHours = $attendedHours = $email = $lastClockIn = $lastClockOut = null;
                $stmtAll->bind_result($studentId, $studentName, $studentDept, $requiredHoursDB, $progressHours, $attendedHours, $email, $lastClockIn, $lastClockOut);
                while ($stmtAll->fetch()) {
                    $students[] = [
                        'student_id' => $studentId,
                        'name' => $studentName,
                        'department' => $studentDept,
                        'required_hours' => intval($requiredHoursDB),
                        'total_hours_completed' => floatval($progressHours),
                        'attendance_hours' => floatval($attendedHours),
                        'email' => $email,
                        'last_clock_in' => $lastClockIn,
                        'last_clock_out' => $lastClockOut,
                    ];
                }
            }
            $stmtAll->close();
        }
    } catch (Exception $e) {
        error_log('Coordinator portal fallback all students query failed: ' . $e->getMessage());
    }
}

if (empty($students)) {
    $students = [];
}

foreach ($students as &$student) {
    $progressHours = round(floatval($student['total_hours_completed'] ?? 0), 2);
    $attendanceHours = round(floatval($student['attendance_hours'] ?? 0), 2);
    $completed = max($progressHours, $attendanceHours);
    $student['completed'] = $completed;
    $studentRequired = intval($student['required_hours'] ?? $requiredHours);
    $student['remaining'] = max(0, $studentRequired - $completed);
    $student['percent'] = $studentRequired > 0 ? round(($completed / $studentRequired) * 100, 2) : 0;

    $lastIn = !empty($student['last_clock_in']) ? strtotime($student['last_clock_in']) : 0;
    $lastOut = !empty($student['last_clock_out']) ? strtotime($student['last_clock_out']) : 0;
    $student['clocked_in'] = ($lastIn > 0 && $lastIn > $lastOut) ? 1 : 0;
}
unset($student);

$liveFeed = [];

function formatTimeAgo(int $timestamp) {
    $ago = time() - $timestamp;
    if ($ago < 60) return $ago . 's ago';
    if ($ago < 3600) return floor($ago / 60) . 'm ago';
    if ($ago < 86400) return floor($ago / 3600) . 'h ago';
    return floor($ago / 86400) . 'd ago';
}

if ($coordinatorId) {
    try {
        $feedStmt = $conn->prepare(
            "SELECT ar.student_id, s.name AS student_name, ar.clock_in, ar.clock_out, ar.hours_worked
             FROM attendance_records ar
             JOIN coordinator_student_assignments csa ON ar.student_id = csa.student_id
             JOIN students s ON ar.student_id = s.student_id
             WHERE csa.coordinator_id = ?
             ORDER BY GREATEST(COALESCE(ar.clock_out, 0), COALESCE(ar.clock_in, 0)) DESC
             LIMIT 7"
        );

        if ($feedStmt) {
            $feedStmt->bind_param('i', $coordinatorId);
            $feedStmt->execute();
            if (method_exists($feedStmt, 'get_result')) {
                $result = $feedStmt->get_result();
                while ($entry = $result->fetch_assoc()) {
                    $time = 0;
                    $event = '';

                    if (!empty($entry['clock_out'])) {
                        $time = strtotime($entry['clock_out']);
                        $event = 'clocked out';
                        if (!empty($entry['hours_worked'])) {
                            $event .= ' (' . number_format(floatval($entry['hours_worked']), 2) . 'h)';
                        }
                    } elseif (!empty($entry['clock_in'])) {
                        $time = strtotime($entry['clock_in']);
                        $event = 'clocked in';
                    }

                    if ($time > 0 && $event !== '') {
                        $liveFeed[] = [
                            'student' => $entry['student_name'],
                            'event' => $event,
                            'time' => formatTimeAgo($time),
                            'icon' => !empty($entry['clock_out']) ? 'fas fa-sign-out-alt bg-danger' : 'fas fa-sign-in-alt bg-primary'
                        ];
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log('Live feed query failed: ' . $e->getMessage());
    }
}

if (empty($liveFeed)) {
    $liveFeed = [];
}

if (!empty($students)) {
    $requiredValues = array_map('intval', array_column($students, 'required_hours'));
    $uniqueRequired = array_unique($requiredValues);
    if (count($uniqueRequired) === 1) {
        $requiredHours = intval($uniqueRequired[0]);
        $requiredHoursLabel = $requiredHours;
    } else {
        $minRequired = min($requiredValues);
        $maxRequired = max($requiredValues);
        $requiredHoursLabel = $minRequired === $maxRequired ? intval($minRequired) : sprintf('%d - %d', $minRequired, $maxRequired);
        $requiredHours = $maxRequired;
    }
} elseif (!empty($departmentRequiredHours)) {
    $uniqueRequired = array_unique(array_values($departmentRequiredHours));
    if (count($uniqueRequired) === 1) {
        $requiredHours = intval($uniqueRequired[0]);
        $requiredHoursLabel = $requiredHours;
    } else {
        $minRequired = min($uniqueRequired);
        $maxRequired = max($uniqueRequired);
        $requiredHoursLabel = $minRequired === $maxRequired ? intval($minRequired) : sprintf('%d - %d', $minRequired, $maxRequired);
        $requiredHours = $maxRequired;
    }
} elseif (!empty($_SESSION['coordinator_department'])) {
    $deptName = trim($_SESSION['coordinator_department']);
    try {
        $deptStmt = $conn->prepare('SELECT required_hours FROM department_required_hours WHERE department = ? LIMIT 1');
        if ($deptStmt) {
            $deptStmt->bind_param('s', $deptName);
            $deptStmt->execute();
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

if (!isset($requiredHoursLabel)) {
    $requiredHoursLabel = $requiredHours;
}

$groupCompleted = 0;
$groupRemaining = 0;
$clockedIn = 0;
$lowProgress = 0;
foreach ($students as &$student) {
    $progressHours = round(floatval($student['total_hours_completed'] ?? 0), 2);
    $attendanceHours = round(floatval($student['attendance_hours'] ?? 0), 2);
    $completed = max($progressHours, $attendanceHours);
    
    $student['completed'] = $completed;
    $studentRequired = intval($student['required_hours'] ?? $requiredHours);
    $student['remaining'] = max(0, $studentRequired - $completed);
    $student['percent'] = $studentRequired > 0 ? round(($completed / $studentRequired) * 100, 2) : 0;
    $groupCompleted += $completed;
    $groupRemaining += $student['remaining'];

    if (!empty($student['clocked_in'])) {
        $clockedIn++;
    }

    if ($student['percent'] < 20) {
        $lowProgress++;
    }
}
unset($student);
$groupTotal = count($students);
$groupRequiredTotal = 0;
foreach ($students as $student) {
    $studentRequired = intval($student['required_hours'] ?? $requiredHours);
    $groupRequiredTotal += $studentRequired;
}
$groupRequiredTotal = max(1, $groupRequiredTotal);
$groupPercent = ($groupTotal > 0 && $groupRequiredTotal > 0)
    ? round(($groupCompleted / $groupRequiredTotal) * 100, 2)
    : 0;

$monthlyAttendance = [];
$monthlyProgressRate = [];
$currentMonth = new DateTime('first day of this month');
for ($m = 5; $m >= 0; $m--) {
    $month = (clone $currentMonth)->modify("-{$m} months");
    $label = $month->format('M Y');
    $monthlyAttendance[$label] = 0;
    $monthlyProgressRate[$label] = 0;
}

$totalRequiredHours = max(1, $groupRequiredTotal);
try {
    $monthStmt = $conn->prepare(
        "SELECT DATE_FORMAT(ar.clock_in, '%b %Y') AS month_label,
                SUM(COALESCE(ar.hours_worked, TIMESTAMPDIFF(SECOND, ar.clock_in, ar.clock_out) / 3600)) AS total_hours
         FROM attendance_records ar
         JOIN coordinator_student_assignments csa ON ar.student_id = csa.student_id
         WHERE csa.coordinator_id = ?
           AND ar.clock_in >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
           AND ar.clock_out IS NOT NULL
         GROUP BY YEAR(ar.clock_in), MONTH(ar.clock_in)
         ORDER BY YEAR(ar.clock_in), MONTH(ar.clock_in) ASC"
    );
    if ($monthStmt) {
        $monthStmt->bind_param('i', $coordinatorId);
        $monthStmt->execute();
        if (method_exists($monthStmt, 'get_result')) {
            $result = $monthStmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $label = $row['month_label'];
                if (isset($monthlyAttendance[$label])) {
                    $totalHours = floatval($row['total_hours']);
                    $monthlyAttendance[$label] = $totalHours;
                    $monthlyProgressRate[$label] = round(min(100, ($totalHours / $totalRequiredHours) * 100), 2);
                }
            }
        }
        $monthStmt->close();
    }
} catch (Exception $e) {
    error_log('Monthly attendance query failed: ' . $e->getMessage());
}

$totalStudents = $groupTotal;
$activeStudents = $clockedIn;
$pendingApprovals = $pendingRegistrations;
$averageCompletion = $groupPercent;
$monthlyLabels = array_keys($monthlyAttendance);
$monthlyData = array_values($monthlyAttendance);
$monthlyCumulative = [];
$runningHours = 0;
foreach ($monthlyData as $hours) {
    $runningHours += floatval($hours);
    $monthlyCumulative[] = round($runningHours, 2);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="../assets/css/coodinator/coordinator.css">
</head>
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
            <h1 class="m-0">Coordinator Oversight Portal</h1>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        
        <!-- ===== STATUS BOXES ===== -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <div class="enhanced-small-box <?= getDepartmentStatusBoxClass($coordinatorDepartment) ?>">
              <div class="inner">
                <h3><?= htmlspecialchars($requiredHoursLabel) ?></h3>
                <p>Required Hours (Each)</p>
              </div>
              <div class="icon">
                <i class="fas fa-briefcase"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="enhanced-small-box <?= getDepartmentStatusBoxClass($coordinatorDepartment) ?>">
              <div class="inner">
                <h3><?= number_format($groupCompleted, 2) ?></h3>
                <p>Group Completed Hours</p>
              </div>
              <div class="icon">
                <i class="fas fa-chart-line"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="enhanced-small-box <?= getDepartmentStatusBoxClass($coordinatorDepartment) ?>">
              <div class="inner">
                <h3><?= number_format($groupRemaining, 2) ?></h3>
                <p>Group Remaining Hours</p>
              </div>
              <div class="icon">
                <i class="fas fa-hourglass-half"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="enhanced-small-box <?= getDepartmentStatusBoxClass($coordinatorDepartment) ?>">
              <div class="inner">
                <h3><?= $lowProgress ?></h3>
                <p>Low Progress (&lt;20%) students</p>
              </div>
              <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Pending Student Registrations -->
        <?php if ($pendingRegistrations > 0): ?>
        <div class="row">
          <div class="col-lg-12">
            <div class="alert alert-info alert-dismissible fade show" role="alert">
              <i class="fas fa-clipboard-check mr-2"></i>
              <strong><?= $pendingRegistrations ?> Pending Student Registration<?= $pendingRegistrations !== 1 ? 's' : '' ?></strong> awaiting your approval.
              <a href="pending_approvals.php" class="alert-link ml-2">Review & Approve</a>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <div class="row">
          <div class="col-md-8">
            <div class="card card-outline card-primary">
              <div class="card-header bg-primary text-white">
                <h3 class="card-title">Student OJT Progress Tracker</h3>
                <div class="card-tools">
                   <div class="input-group input-group-sm" style="width: 150px;">
                    <input type="text" name="table_search" class="form-control float-right" placeholder="Search Student">
                  </div>
                </div>
              </div>
              <div class="card-body d-flex flex-column p-0">
                <div class="table-responsive" style="overflow-y: auto; max-height: 300px;">
                  <table class="table table-hover text-nowrap mb-0">
                    <thead>
                      <tr>
                        <th class="d-none d-sm-table-cell">Department</th>
                        <th>Student Name</th>
                        <th class="d-none d-sm-table-cell">Today's Status</th>
                        <th>Progress</th>
                        <th>Hours Rendered</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (empty($students)): ?>
                        <tr>
                          <td colspan="6" class="text-center text-muted">No students assigned yet.</td>
                        </tr>
                      <?php else: ?>
                        <?php
                          usort($students, function($a, $b) {
                            $aDept = strtolower(trim($a['department'] ?? 'Unassigned'));
                            $bDept = strtolower(trim($b['department'] ?? 'Unassigned'));
                            return $aDept <=> $bDept ?: strcasecmp($a['name'], $b['name']);
                          });
                          $currentDept = null;
                        ?>
                        <?php foreach ($students as $student): ?>
                          <?php
                            if (($student['department'] ?? '') !== $currentDept) {
                              $currentDept = $student['department'] ?? 'Unassigned';
                              echo '<tr class="table-primary" style="background-color: #e3f2fd !important;"><td colspan="6" class="font-weight-bold"><i class="fas fa-building mr-2"></i>' . htmlspecialchars($currentDept) . '</td></tr>';
                            }
                            $barWidth = max($student['percent'], 3);
                            $barClass = $student['percent'] >= 80 ? 'bg-success' : ($student['percent'] >= 50 ? 'bg-warning' : 'bg-info');
                            $statusClass = !empty($student['clocked_in']) ? 'badge-success' : 'badge-secondary';
                            $statusText = !empty($student['clocked_in']) ? 'Clocked In' : 'Timed Out';
                            $daysRemaining = $student['completed'] > 0 ? ceil($student['remaining'] / 8) : 0;
                          ?>
                          <tr>
                            <td class="d-none d-sm-table-cell"><small class="text-muted"><?= htmlspecialchars($student['department'] ?? 'Unassigned') ?></small></td>
                            <td>
                              <strong><?= htmlspecialchars($student['name']) ?></strong><br>
                              <small class="text-muted"><?= htmlspecialchars($student['student_id']) ?></small>
                            </td>
                            <td class="d-none d-sm-table-cell"><span class="badge <?= $statusClass ?>"><?= $statusText ?></span></td>
                            <td>
                              <div class="progress mb-1" style="height: 18px; border-radius: 9px; background-color: #e9ecef;">
                                <div class="progress-bar <?= $barClass ?>" role="progressbar" style="width: <?= $barWidth ?>%; border-radius: 9px;">
                                  <span class="small font-weight-bold" style="font-size: 0.65rem;"><?= $student['percent'] ?>%</span>
                                </div>
                              </div>
                              <small class="text-muted"><?= number_format($student['completed'], 0) ?>h / <?= $requiredHours ?>h</small>
                            </td>
                            <td>
                              <span class="text-success font-weight-bold"><?= number_format($student['completed'], 2) ?></span>
                              <small class="text-muted">/ <?= $requiredHours ?>h</small>
                              <?php if ($daysRemaining > 0): ?>
                                <br><small class="text-info">~<?= $daysRemaining ?> days left</small>
                              <?php endif; ?>
                            </td>
                            <td><a href="students.php?student_id=<?= urlencode($student['student_id']) ?>" class="btn btn-xs btn-outline-info">View</a></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
                <div class="text-center p-2 border-top bg-light">
                  <a href="/ojt1/coodinator/reports.php" class="btn btn-sm btn-primary">View All Activity</a>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card card-outline card-secondary">
              <div class="card-header bg-navy text-white">
                <h3 class="card-title">Student Progress Trend</h3>
              </div>
              <div class="card-body">
                <div class="chart-responsive" style="min-height: 200px;">
                  <canvas id="coordinatorOverviewChart" style="width:100%; height:260px; display:block;"></canvas>
                </div>
              </div>
              <div class="card-footer text-center">
                <small class="text-muted">Monthly progress and hours logged for your student group.</small>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>
  </div>

  <!-- All Activity Modal -->
  <div class="modal fade" id="allActivityModal" tabindex="-1" role="dialog" aria-labelledby="allActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" role="document" style="max-width: 860px;">
      <div class="modal-content border-secondary shadow-lg">
        <div class="modal-header bg-warning">
          <h5 class="modal-title text-dark" id="allActivityModalLabel">All Live Activity</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" style="max-height: 520px; overflow-y: auto;">
          <div id="allActivityContent" class="list-group"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>

<?php include __DIR__ . '/../includes/script.php'; ?>
<script>
  // Pass PHP data to external script
  window.coordinatorMonthlyLabels = <?= json_encode($monthlyLabels) ?>;
  window.coordinatorMonthlyData = <?= json_encode($monthlyData) ?>;
  window.coordinatorMonthlyCumulative = <?= json_encode($monthlyCumulative) ?>;
</script>
<script src="../assets/js/coodinator/coordinator.js"></script>
</body>
</html>