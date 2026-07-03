<!DOCTYPE html>
<html lang="en">
<?php
session_start();
$pageTitle = 'Student Records';
include __DIR__ . '/../dbconnection.php';

if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /ojt1/index.php');
    exit;
}

$search = trim($_GET['search'] ?? '');
$departmentFilter = trim($_GET['department'] ?? '');
$selectedStudentId = trim($_GET['selected_student'] ?? '');

$studentRows = [];
$departmentCounts = [];
$selectedStudent = null;

$sql = "SELECT s.id, s.student_id, s.name, s.email, s.department, s.required_ojt_hours,
               s.enrolled_at AS created_at,
               COALESCE(sp.total_hours_completed, 0) AS completed_hours
        FROM students s
        LEFT JOIN student_progress sp ON s.student_id = sp.student_id";
$conditions = [];
$params = [];
$types = '';

if ($search !== '') {
    $conditions[] = "(s.student_id LIKE ? OR s.name LIKE ? OR s.department LIKE ?)";
    $searchParam = '%' . $search . '%';
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'sss';
}

if ($departmentFilter !== '') {
    $conditions[] = "LOWER(TRIM(COALESCE(s.department, ''))) = LOWER(TRIM(?))";
    $params[] = $departmentFilter;
    $types .= 's';
}

if (!empty($conditions)) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

$sql .= ' ORDER BY s.department, s.name';

$stmt = $conn->prepare($sql);
if ($stmt) {
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = method_exists($stmt, 'get_result') ? $stmt->get_result() : null;
    if ($result) {
        $studentRows = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $id = $studentId = $name = $email = $department = $requiredOjtHours = $createdAt = $completedHours = null;
        $stmt->bind_result($id, $studentId, $name, $email, $department, $requiredOjtHours, $createdAt, $completedHours);
        while ($stmt->fetch()) {
            $studentRows[] = [
                'id' => $id,
                'student_id' => $studentId,
                'name' => $name,
                'email' => $email,
                'department' => $department,
                'required_ojt_hours' => $requiredOjtHours,
                'created_at' => $createdAt,
                'completed_hours' => $completedHours,
            ];
        }
    }
}

$stmt = $conn->prepare('SELECT COALESCE(department, "Unassigned") AS department, COUNT(*) AS total FROM students GROUP BY department ORDER BY department');
if ($stmt) {
    $stmt->execute();
    $result = method_exists($stmt, 'get_result') ? $stmt->get_result() : null;
    if ($result) {
        $departmentCounts = $result->fetch_all(MYSQLI_ASSOC);
    }
}

if ($selectedStudentId !== '') {
    $stmt = $conn->prepare("SELECT s.*, s.enrolled_at AS created_at, COALESCE(sp.total_hours_completed, 0) AS completed_hours
                            FROM students s
                            LEFT JOIN student_progress sp ON s.student_id = sp.student_id
                            WHERE s.student_id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('s', $selectedStudentId);
        $stmt->execute();
        $result = method_exists($stmt, 'get_result') ? $stmt->get_result() : null;
        if ($result) {
            $selectedStudent = $result->fetch_assoc();
        } else {
            $id = $studentId = $name = $email = $department = $requiredOjtHours = $avatar = $createdAt = $completedHours = null;
            $stmt->bind_result($id, $studentId, $name, $email, $department, $requiredOjtHours, $avatar, $createdAt, $completedHours);
            if ($stmt->fetch()) {
                $selectedStudent = [
                    'id' => $id,
                    'student_id' => $studentId,
                    'name' => $name,
                    'email' => $email,
                    'department' => $department,
                    'required_ojt_hours' => $requiredOjtHours,
                    'avatar' => $avatar,
                    'created_at' => $createdAt,
                    'completed_hours' => $completedHours,
                ];
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
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
            <h1 class="m-0">Student Records</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="admin.php">Home</a></li>
              <li class="breadcrumb-item active">Student Records</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <style>
          #studentRecordsTable tbody tr {
            transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
          }
          #studentRecordsTable tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.08);
            background-color: rgba(255, 255, 255, 0.95);
          }
          #studentRecordsTable tbody tr.table-primary {
            animation: glow 1.4s ease-in-out infinite alternate;
          }
          @keyframes glow {
            from { box-shadow: 0 0 0 rgba(0, 123, 255, 0.3); }
            to { box-shadow: 0 0 20px rgba(0, 123, 255, 0.4); }
          }
          .dataTables_wrapper .dataTables_paginate .paginate_button {
            border: none;
            border-radius: 4px;
            background: #f4f6f9;
          }
          .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #007bff;
            color: #fff !important;
          }
        </style>
        <div class="card card-primary">
          <div class="card-header bg-primary text-white">
            <h3 class="card-title">Active Students</h3>
          </div>
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <p class="mb-0">Use this page to manage student records (search, export, modify).</p>
              <div class="d-flex align-items-center">
                <form method="get" class="form-inline mr-2">
                  <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Search student ID, name, department" value="<?= htmlspecialchars($search) ?>">
                    <div class="input-group-append">
                      <button class="btn btn-info" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                  </div>
                </form>
                <form method="get" class="form-inline">
                  <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                  <div class="input-group input-group-sm">
                    <select name="department" class="form-control" onchange="this.form.submit()">
                      <option value="">All Departments</option>
                      <?php foreach ($departmentCounts as $deptRow): ?>
                        <option value="<?= htmlspecialchars($deptRow['department']) ?>" <?= ($departmentFilter === $deptRow['department']) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($deptRow['department'] ?: 'Unassigned') ?> (<?= $deptRow['total'] ?>)
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <div class="input-group-append">
                      <button class="btn btn-primary" type="submit">Filter</button>
                      <a href="admin/students.php" class="btn btn-secondary">Clear</a>
                    </div>
                  </div>
                </form>
              </div>
            </div>
            <div class="table-responsive">
              <table id="studentRecordsTable" class="table table-striped table-hover table-bordered table-sm" style="min-width: 900px;">
                <thead class="thead-light">
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Completed</th>
                    <th>Required</th>
                    <th>Created At</th>
                  </tr>
                </thead>
                <tbody>

                  <?php if (empty($studentRows)): ?>
                    <tr>
                      <td colspan="7" class="text-center text-muted">No student records found.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($studentRows as $student): ?>
                      <tr <?= ($selectedStudent && $selectedStudent['student_id'] === $student['student_id']) ? 'class="table-primary"' : '' ?> >
                        <td><?= htmlspecialchars($student['student_id']) ?></td>
                        <td><?= htmlspecialchars($student['name']) ?></td>
                        <td><?= htmlspecialchars($student['email']) ?></td>
                        <td><?= htmlspecialchars($student['department'] ?: 'Unassigned') ?></td>
                        <td><?= number_format($student['completed_hours'], 1) ?></td>
                        <td><?= intval($student['required_ojt_hours']) ?></td>
                        <td><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($student['created_at']))) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <?php if ($selectedStudent): ?>
          <div class="card card-success">
            <div class="card-header bg-success text-white">
              <h3 class="card-title">Selected Student Details</h3>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-4">
                  <strong>Student ID</strong>
                  <p><?= htmlspecialchars($selectedStudent['student_id']) ?></p>
                </div>
                <div class="col-md-4">
                  <strong>Name</strong>
                  <p><?= htmlspecialchars($selectedStudent['name']) ?></p>
                </div>
                <div class="col-md-4">
                  <strong>Email</strong>
                  <p><?= htmlspecialchars($selectedStudent['email']) ?></p>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <strong>Department</strong>
                  <p><?= htmlspecialchars($selectedStudent['department'] ?: 'Unassigned') ?></p>
                </div>
                <div class="col-md-4">
                  <strong>Hours Completed</strong>
                  <p><?= number_format($selectedStudent['completed_hours'], 1) ?>h</p>
                </div>
                <div class="col-md-4">
                  <strong>Required Hours</strong>
                  <p><?= intval($selectedStudent['required_ojt_hours']) ?>h</p>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>
        </div>
      </div>
    </section>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
  <?php include __DIR__ . '/../includes/script.php'; ?>
  <script>
    $(document).ready(function () {
      if ($.fn.DataTable) {
        $('#studentRecordsTable').DataTable({
          pageLength: 12,
          lengthChange: true,
          responsive: true,
          autoWidth: false,
          order: [[3, 'asc']],
          columnDefs: [
            { targets: [0,4,5,6], className: 'text-center' },
            { targets: 6, type: 'date' }
          ],
          language: {
            search: 'Quick Search:',
            paginate: {
              next: '<i class="fas fa-chevron-right"></i>',
              previous: '<i class="fas fa-chevron-left"></i>'
            }
          },
          drawCallback: function () {
            $('#studentRecordsTable tbody tr').css('opacity', 0).animate({ opacity: 1 }, 300);
          }
        });
      }
    });
  </script>
</div>
</body>
</html>