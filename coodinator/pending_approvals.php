<?php
session_start();
$pageTitle = 'Student Registration Approvals';
ob_start();
require_once __DIR__ . '/../includes/header.php';
ob_end_clean();
require_once '../dbconnection.php';

if (!isset($_SESSION['coordinator_logged_in']) || $_SESSION['coordinator_logged_in'] !== true) {
    header('Location: ../index.php');
    exit;
}

$coordinatorDepartment = $_SESSION['coordinator_department'] ?? '';
$coordinatorName = $_SESSION['coordinator_name'] ?? 'Coordinator';

$studentDepartments = getDepartmentsForCollege($coordinatorDepartment);
if (empty($studentDepartments)) {
    $studentDepartments = [$coordinatorDepartment];
}
$escapedDepartments = array_map(function ($dept) use ($conn) {
    return "'" . $conn->real_escape_string($dept) . "'";
}, $studentDepartments);
$inClause = implode(',', $escapedDepartments);

$stmt = $conn->prepare("
    SELECT 
        id, 
        student_id, 
        name, 
        email, 
        department, 
        enrolled_at,
        registration_status
    FROM students 
    WHERE department IN ($inClause) AND registration_status = 'pending'
    ORDER BY enrolled_at DESC
");

if (!$stmt) {
    die('Database error: ' . $conn->error);
}

$stmt->execute();
$result = $stmt->get_result();
$pendingStudents = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmtApproved = $conn->prepare("
    SELECT COUNT(*) as approved_count 
    FROM students 
    WHERE department IN ($inClause) AND registration_status = 'approved'
");
$stmtApproved->execute();
$approvedResult = $stmtApproved->get_result();
$approvedStats = $approvedResult->fetch_assoc();
$approvedCount = $approvedStats['approved_count'] ?? 0;
$stmtApproved->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | OJT System</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../plugins/bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome (already loaded in header, but we include for safety) -->
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <!-- Toastr for notifications -->
    <link rel="stylesheet" href="../plugins/toastr/toastr.min.css">
    
    <link rel="stylesheet" href="../assets/css/coodinator/pending_approvals.css">
</head>
<body>
    <div class="content-wrapper">
        <a href="coordinator.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
        
        <div class="page-header">
            <h1><i class="fas fa-clipboard-check mr-2"></i>Student Registration Approvals</h1>
            <div class="subtitle"><?= htmlspecialchars($coordinatorDepartment) ?></div>
        </div>
        
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-number"><?= count($pendingStudents) ?></div>
                <div class="stat-label">Pending Approvals</div>
            </div>
            <div class="stat-card approved">
                <div class="stat-number"><?= $approvedCount ?></div>
                <div class="stat-label">Approved Students</div>
            </div>
        </div>
        
        <div class="students-container">
            <div class="students-header">
                <h3><i class="fas fa-hourglass-half mr-2"></i>Pending Student Registrations</h3>
                <p>Review student registration requests from <strong><?= htmlspecialchars($coordinatorDepartment) ?></strong></p>
            </div>
            <div class="students-controls">
                <div class="search-box input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input id="studentSearch" type="text" class="form-control" placeholder="Search by name, email, or student ID" aria-label="Search students" oninput="filterStudents()">
                </div>
                <div class="summary-pill">
                    <i class="fas fa-filter"></i>
                    Showing <span id="visibleCount"><?= count($pendingStudents) ?></span> of <?= count($pendingStudents) ?> pending
                </div>
            </div>
            
            <?php if (count($pendingStudents) > 0): ?>
                <div class="student-list" id="studentList">
                    <?php foreach ($pendingStudents as $student): ?>
                        <div class="student-item" data-student-id="<?= htmlspecialchars($student['student_id']) ?>">
                            <div class="student-card-left">
                                <div class="student-card-top">
                                    <div class="student-name"><?= htmlspecialchars($student['name']) ?></div>
                                    <span class="status-badge"><i class="fas fa-hourglass-half"></i> Pending</span>
                                </div>
                                <div class="student-meta">
                                    <span>
                                        <span class="meta-label">Student ID</span>
                                        <span class="meta-value"><?= htmlspecialchars($student['student_id']) ?></span>
                                    </span>
                                    <span>
                                        <span class="meta-label">Email</span>
                                        <span class="meta-value"><?= htmlspecialchars($student['email']) ?></span>
                                    </span>
                                    <span>
                                        <span class="meta-label">Department</span>
                                        <span class="meta-value"><?= htmlspecialchars($student['department']) ?></span>
                                    </span>
                                    <span>
                                        <span class="meta-label">Requested</span>
                                        <span class="meta-value"><?= date('M d, Y H:i', strtotime($student['enrolled_at'])) ?></span>
                                    </span>
                                </div>
                            </div>
                            <div class="student-card-right">
                                <div class="student-note">
                                    <i class="fas fa-info-circle mr-1"></i> Please verify student details before approval.
                                </div>
                                <div class="action-buttons">
                                    <button class="btn btn-approve" onclick="approveStudent('<?= htmlspecialchars($student['student_id']) ?>', this)">
                                        <i class="fas fa-check mr-1"></i>Approve
                                    </button>
                                    <button class="btn btn-reject" onclick="rejectStudent('<?= htmlspecialchars($student['student_id']) ?>', this)">
                                        <i class="fas fa-times mr-1"></i>Reject
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h4>No Pending Approvals</h4>
                    <p>All student registrations for your department have been reviewed. Check back later for new submissions.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="../plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Toastr JS -->
    <script src="../plugins/toastr/toastr.min.js"></script>
    <script src="../assets/js/coodinator/pending_approvals.js"></script>
</body>
</html>
