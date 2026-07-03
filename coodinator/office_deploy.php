<?php
session_start();
require_once __DIR__ . '/../dbconnection.php';

if (empty($_SESSION['coordinator_logged_in']) || $_SESSION['coordinator_logged_in'] !== true) {
    header('Location: /ojt1/index.php?coordinator_login_failed=1');
    exit;
}

function abbreviateDepartment(string $department) {
    $abbrevs = [
        'Bachelor of Science in Business Administration (Financial Management)' => 'BSBA',
        'Bachelor of Science in Information Technology' => 'BSIT',
        'Bachelor of Science in Computer Science' => 'BSCS',
    ];
    return $abbrevs[$department] ?? $department;
}

$pageTitle = 'Office Deployment';
include __DIR__ . '/../includes/header.php';

$coordinatorId = $_SESSION['coordinator_id'] ?? null;
$coordinatorDepartment = trim($_SESSION['coordinator_department'] ?? '');
$modalThemeClass = getDepartmentThemeClass($coordinatorDepartment);
$students = []; 

try {
    $studentDepartments = getDepartmentsForCollege($coordinatorDepartment);
    if (empty($studentDepartments)) {
        $studentDepartments = [$coordinatorDepartment];
    }
    $escapedDepartments = array_map(function ($dept) use ($conn) {
        return "'" . $conn->real_escape_string($dept) . "'";
    }, $studentDepartments);
    $inClause = implode(',', $escapedDepartments);

    $stmt = $conn->prepare("SELECT student_id, name, department, '' as location FROM students WHERE department IN ($inClause) ORDER BY name ASC");
    $stmt->execute();

    if (method_exists($stmt, 'get_result')) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
    } else {
        $stmt->bind_result($s_student_id, $s_name, $s_department, $s_location);
        while ($stmt->fetch()) {
            $students[] = [
                'student_id' => $s_student_id,
                'name' => $s_name,
                'department' => $s_department,
                'location' => $s_location,
            ];
        }
    }
} catch (Exception $e) {
    error_log('Office deploy fetch students failed: ' . $e->getMessage());
}

// Get deployed student IDs
$deployedStudentIds = [];
try {
    $deployedStmt = $conn->prepare("SELECT student_id FROM coordinator_office_assignments WHERE coordinator_id = ?");
    $deployedStmt->bind_param('i', $coordinatorId);
    $deployedStmt->execute();
    $deployedResult = $deployedStmt->get_result();
    while ($row = $deployedResult->fetch_assoc()) {
        $deployedStudentIds[] = $row['student_id'];
    }
    $deployedStmt->close();
} catch (Exception $e) {
    error_log('Failed to fetch deployed students: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="../assets/css/coodinator/office_deploy.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
  <div class="preloader flex-column justify-content-center align-items-center" style="background: linear-gradient(to bottom, blue, yellow);">
    <img class="animation__shake" src="../assets/img/users/OIP.webp" alt="Preloader" height="150" width="150" style="border-radius: 50%;">
  </div> 
<div class="wrapper">
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="content-wrapper">
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0">Office Deployment</h1>
            </div>
          </div>
        </div>
      </div>

      <section class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-8">
              <div class="card card-primary shadow-sm">
                <div class="card-header">
                  <h3 class="card-title">Map Search</h3>
                  <div class="card-tools">
                    <button id="deployment-guide-btn" class="btn btn-tool" title="How to deploy students">
                      <i class="fas fa-question-circle"></i>
                    </button>
                  </div>
                </div>
                <div class="card-body">
                  <div class="input-group mb-3">
                    <input id="officeSearch" type="search" class="form-control" placeholder="Search office address or location" aria-label="Search office">
                    <div class="input-group-append">
                      <button id="searchOfficeBtn" class="btn btn-info">Search</button>
                    </div>
                  </div>
                  <ul id="searchResults" class="list-group mb-3"></ul>
                  <div id="map" style="height: 440px; border: 1px solid #ced4da; border-radius: .3rem; overflow: hidden;"></div>
                  <!-- Fallback when Google Maps fails -->
                  <div id="map-fallback" class="alert alert-warning text-center" style="display:none; margin-top:1rem;">
                    <i class="fas fa-map-marked-alt fa-3x mb-2"></i>
                    <p><strong>Google Maps is currently unavailable.</strong></p>
                    <p class="mb-0">You can still search for office locations using the search bar above. Click a result to set the coordinates manually.</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-4">
              <div class="card card-secondary shadow-sm">
                <div class="card-header" style="cursor: pointer;" data-toggle="collapse" data-target="#deployStudentCollapse" aria-expanded="true">
                  <h3 class="card-title">
                    <i class="fas fa-users mr-2"></i>Deploy Students
                    <span class="badge badge-pill badge-info float-right border" id="selectedCountBadge" style="display: none;">0</span>
                  </h3>
                  <div class="card-tools">
                    <button type="button" class="btn btn-tool" title="Expand/Collapse">
                      <i class="fas fa-minus" id="deployCollapseIcon"></i>
                    </button>
                  </div>
                </div>
                <div id="deployStudentCollapse" class="collapse">
                  <div class="card-body">
                    <div class="form-group mb-3">
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                          <label class="mb-0 font-weight-bold">Select Students</label>
                          <p class="mb-0 text-muted small">Click the button below to choose students in a popup.</p>
                        </div>
                      </div>
                      <button type="button" class="btn btn-outline-primary btn-block" id="openStudentSelectionBtn">
                        <i class="fas fa-users mr-1"></i> Select Students
                      </button>
                      <small class="form-text text-muted mt-2"><i class="fas fa-info-circle mr-1"></i> Students marked [Deployed] are already assigned.</small>
                      <select id="studentSelect" class="d-none" multiple="multiple">
                        <?php foreach ($students as $student): 
                            $isDeployed = in_array($student['student_id'], $deployedStudentIds);
                        ?>
                          <option value="<?= htmlspecialchars($student['student_id']) ?>" <?= $isDeployed ? 'data-deployed="1"' : '' ?> data-student-name="<?= htmlspecialchars($student['name']) ?>">
                            <?= htmlspecialchars($student['name']) ?> (<?= htmlspecialchars(abbreviateDepartment($student['department'])) ?>)<?= $isDeployed ? ' [Deployed]' : '' ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <div class="form-group">
                      <label>Selected location</label>
                      <div class="input-group">
                        <input id="selectedLocation" type="text" class="form-control" readonly placeholder="No location selected" />
                        <div class="input-group-append">
                          <button class="btn btn-outline-danger" type="button" id="clearLocationBtn" title="Clear location">
                            <i class="fas fa-times-circle"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                    <div class="form-row">
                      <div class="form-group col-6">
                        <label>Latitude</label>
                        <input id="selectedLat" type="text" class="form-control" readonly>
                      </div>
                      <div class="form-group col-6">
                        <label>Longitude</label>
                        <input id="selectedLng" type="text" class="form-control" readonly>
                      </div>
                    </div>
                    <button id="deployStudentBtn" class="btn btn-<?= htmlspecialchars($modalThemeClass) ?> btn-block" disabled>
                      <i class="fas fa-map-marker-alt mr-1"></i> Assign to Selected Location
                    </button>
                    <div id="selectedStudentsInfo" class="mt-2 text-muted small" style="display: none;">
                      <span class="badge badge-pill badge-info mr-2" id="selectedStudentsCount" style="font-size: 0.9rem;">0</span>
                      selected student(s)
                    </div>
                  </div>
                </div>
              </div>

              <!-- Student Selection Modal – Table Layout (SCROLLABLE + STICKY HEADER) -->
              <div class="modal fade" id="studentSelectionModal" tabindex="-1" role="dialog" aria-labelledby="studentSelectionModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
                  <div class="modal-content shadow-lg border-0">
                    <div class="modal-header bg-<?= htmlspecialchars($modalThemeClass) ?> text-white">
                      <div>
                        <h5 class="modal-title" id="studentSelectionModalLabel">
                          <i class="fas fa-users mr-2"></i> Select Students to Deploy
                        </h5>
                        <p class="mb-0 small opacity-75">Select one or more students. Use the search to filter by name.</p>
                      </div>
                      <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body bg-light">
                      <div class="row justify-content-center">
                        <div class="col-12">
                          <!-- Search Bar -->
                          <div class="input-group mb-3 shadow-sm rounded">
                            <div class="input-group-prepend">
                              <span class="input-group-text bg-white border-right-0"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" class="form-control border-left-0" id="studentModalSearch" placeholder="Search students by name...">
                            <div class="input-group-append">
                              <button type="button" class="btn btn-outline-secondary" id="clearStudentSearch">Clear</button>
                            </div>
                          </div>

                          <!-- Table Container with Vertical Scroll -->
                          <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover table-striped mb-0" id="studentSelectionTable">
                              <thead style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">
                                <tr>
                                  <th style="width: 40px;"><input type="checkbox" id="selectAllStudents"></th>
                                  <th>Name</th>
                                  <th class="d-none d-sm-table-cell">Department</th>
                                  <th class="text-center" style="width: 80px;">Status</th>
                                </tr>
                              </thead>
                              <tbody>
                                <?php foreach ($students as $student): 
                                    $isDeployed = in_array($student['student_id'], $deployedStudentIds);
                                ?>
                                  <tr class="student-row" data-student-id="<?= htmlspecialchars($student['student_id']) ?>" data-student-name="<?= htmlspecialchars($student['name']) ?>" data-deployed="<?= $isDeployed ? '1' : '0' ?>">
                                    <td><input type="checkbox" class="student-checkbox" <?= $isDeployed ? 'disabled' : '' ?>></td>
                                    <td>
                                      <strong><?= htmlspecialchars($student['name']) ?></strong>
                                      <div class="d-sm-none text-muted small"><?= htmlspecialchars(abbreviateDepartment($student['department'])) ?></div>
                                    </td>
                                    <td class="d-none d-sm-table-cell"><?= htmlspecialchars(abbreviateDepartment($student['department'])) ?></td>
                                    <td class="text-center">
                                      <?php if ($isDeployed): ?>
                                        <span class="badge badge-pill badge-secondary">Deployed</span>
                                      <?php else: ?>
                                        <span class="badge badge-pill badge-success">Available</span>
                                      <?php endif; ?>
                                    </td>
                                  </tr>
                                <?php endforeach; ?>
                              </tbody>
                            </table>
                          </div>
                          <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle mr-1"></i> Deployed students are disabled and cannot be selected.</small>
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between align-items-center bg-white border-top">
                      <span id="selectedModalCount" class="text-muted">Selected: 0</span>
                      <div>
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-<?= htmlspecialchars($modalThemeClass) ?>" id="saveStudentSelectionBtn">Save Selection</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="card card-outline card-info shadow-sm">
                <div class="card-header">
                  <h3 class="card-title">
                    <i class="fas fa-map-pin mr-2"></i>Assigned Students
                    <span class="badge badge-pill badge-success float-right border" id="assignedCountBadge">0</span>
                  </h3>
                </div>
                <div id="assignedStudentsCollapse" class="collapse show">
                  <div class="card-body" style="min-height: 180px;">
                    <div id="assignedStudentsLoading" class="text-center py-3">
                      <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                      </div>
                      <span class="ml-2 text-muted">Loading assignments...</span>
                    </div>
                    <div id="assignedStudentsSummary" class="text-center py-4" style="display: none;">
                      <p class="mb-3 text-muted" id="assignedStudentsSummaryText">
                        Assigned students are displayed on the map above and grouped by location.
                      </p>
                      <a href="assigned_students.php" class="btn btn-info btn-block">
                        <i class="fas fa-eye mr-1"></i> View All Assigned Students
                      </a>
                    </div>
                  </div>
                  <div class="card-footer">
                    <div id="deployedStudentsInfo" class="mt-2"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Deployment Guide Modal -->
    <div class="modal fade" id="deploymentGuideModal" tabindex="-1" role="dialog" aria-labelledby="deploymentGuideModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="deploymentGuideModalLabel">
              <i class="fas fa-graduation-cap mr-2"></i> Student Deployment Guide
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="text-center mb-4">
              <p class="text-muted">Follow these steps to successfully deploy students to office locations</p>
              <div class="progress mb-4" style="height: 8px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="card border-primary mb-3">
                  <div class="card-header bg-light">
                    <div class="d-flex align-items-center">
                      <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px; font-weight: bold;">1</div>
                      <div>
                        <h6 class="mb-0"><i class="fas fa-search text-primary mr-1"></i> Search Office Location</h6>
                      </div>
                    </div>
                  </div>
                  <div class="card-body">
                    <p class="mb-2">Use the search box to find potential deployment sites:</p>
                    <ul class="list-unstyled">
                      <li><i class="fas fa-check text-success mr-2"></i> Business names (e.g., "ABC Corporation")</li>
                      <li><i class="fas fa-check text-success mr-2"></i> Street addresses</li>
                      <li><i class="fas fa-check text-success mr-2"></i> City or landmark names</li>
                    </ul>
                    <div class="alert alert-light border">
                      <small><i class="fas fa-lightbulb text-warning mr-1"></i> <strong>Pro tip:</strong> Search results appear instantly below the search box</small>
                    </div>
                  </div>
                </div>

                <div class="card border-success mb-3">
                  <div class="card-header bg-light">
                    <div class="d-flex align-items-center">
                      <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px; font-weight: bold;">3</div>
                      <div>
                        <h6 class="mb-0"><i class="fas fa-user-graduate text-success mr-1"></i> Select Student</h6>
                      </div>
                    </div>
                  </div>
                  <div class="card-body">
                    <p>Choose from students in your department:</p>
                    <div class="bg-light p-2 rounded mb-2">
                      <select class="form-control form-control-sm" disabled>
                        <option>-- Select student --</option>
                        <option>John Doe (BSIT)</option>
                        <option>Jane Smith (BSIT)</option>
                      </select>
                    </div>
                    <small class="text-muted"><i class="fas fa-info-circle mr-1"></i> Only students from your assigned department appear</small>
                  </div>
                </div>

                <div class="card border-info mb-3">
                  <div class="card-header bg-light">
                    <div class="d-flex align-items-center">
                      <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px; font-weight: bold;">5</div>
                      <div>
                        <h6 class="mb-0"><i class="fas fa-eye text-info mr-1"></i> View Deployments</h6>
                      </div>
                    </div>
                  </div>
                  <div class="card-body">
                    <p>Monitor deployed students on the map:</p>
                    <div class="d-flex align-items-center mb-2">
                      <div class="badge badge-primary mr-2" style="font-size: 14px;">2</div>
                      <small>Numbered markers show student count</small>
                    </div>
                    <small class="text-muted"><i class="fas fa-mouse-pointer mr-1"></i> Click markers to zoom and view details</small>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="card border-warning mb-3">
                  <div class="card-header bg-light">
                    <div class="d-flex align-items-center">
                      <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px; font-weight: bold;">2</div>
                      <div>
                        <h6 class="mb-0"><i class="fas fa-map-marker-alt text-warning mr-1"></i> Choose Location</h6>
                      </div>
                    </div>
                  </div>
                  <div class="card-body">
                    <p>Select the exact deployment location:</p>
                    <div class="bg-light p-2 rounded mb-2 text-center">
                      <i class="fas fa-map fa-2x text-muted mb-2"></i>
                      <br>
                      <small class="text-muted">Click anywhere on the map</small>
                    </div>
                    <ul class="list-unstyled small">
                      <li><i class="fas fa-arrows-alt text-warning mr-2"></i> Drag marker to adjust position</li>
                      <li><i class="fas fa-search-plus text-warning mr-2"></i> Satellite view for precision</li>
                    </ul>
                  </div>
                </div>

                <div class="card border-danger mb-3">
                  <div class="card-header bg-light">
                    <div class="d-flex align-items-center">
                      <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px; font-weight: bold;">4</div>
                      <div>
                        <h6 class="mb-0"><i class="fas fa-check-circle text-danger mr-1"></i> Confirm Assignment</h6>
                      </div>
                    </div>
                  </div>
                  <div class="card-body">
                    <p>Complete the deployment:</p>
                    <button class="btn btn-success btn-sm mb-2" disabled>
                      <i class="fas fa-plus mr-1"></i> Assign to Selected Office
                    </button>
                    <div class="alert alert-success py-2">
                      <small><i class="fas fa-check-circle mr-1"></i> Assignment saved successfully!</small>
                    </div>
                    <small class="text-muted"><i class="fas fa-save mr-1"></i> Assignments are automatically saved</small>
                  </div>
                </div>

                <div class="card border-secondary">
                  <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-lightbulb text-secondary mr-1"></i> Quick Tips</h6>
                  </div>
                  <div class="card-body">
                    <ul class="list-unstyled small mb-0">
                      <li><i class="fas fa-users text-primary mr-2"></i> Multiple students can share one location</li>
                      <li><i class="fas fa-undo text-warning mr-2"></i> Assignments can be updated anytime</li>
                      <li><i class="fas fa-mobile-alt text-success mr-2"></i> Works on mobile devices</li>
                      <li><i class="fas fa-clock text-info mr-2"></i> Real-time updates across sessions</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <div class="text-center mt-4">
              <div class="alert alert-light border">
                <i class="fas fa-question-circle text-primary mr-2"></i>
                <strong>Need help?</strong> Contact your system administrator or refer to the user manual.
              </div>
            </div>
          </div>
          <div class="modal-footer bg-light">
            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
              <i class="fas fa-times mr-1"></i> Close Guide
            </button>
            <button type="button" class="btn btn-primary" onclick="$('#deploymentGuideModal').modal('hide'); toastr.info('Ready to deploy students!');">
              <i class="fas fa-play mr-1"></i> Let's Get Started
            </button>
          </div>
        </div>
      </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>

<?php include __DIR__ . '/../includes/script.php'; ?>
<!-- Select2 CSS -->
<link rel="stylesheet" href="../plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="../plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCK4VGQ3eouzbKMrw8ty3ylsV-ezgEzDAU"></script>
<script src="../plugins/select2/js/select2.full.min.js"></script>
<script src="../assets/js/coodinator/office_deploy.js"></script>
</body>
</html>