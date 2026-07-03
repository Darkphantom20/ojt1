<?php
// Session checks must come BEFORE any output (including includes)
session_start();
require_once __DIR__ . '/../dbconnection.php';

$requiredHours = isset($_SESSION['student_required_ojt_hours']) ? intval($_SESSION['student_required_ojt_hours']) : 480;
if (empty($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header('Location: /ojt1/index.php');
    exit;
}

$pageTitle = 'Student Dashboard';

ob_start();
include __DIR__ . '/../includes/header.php';
$headContents = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?= $headContents ?>
</head>
<?php

$studentId = strtoupper(trim($_SESSION['student_id'] ?? ''));
if ($studentId === '') {
    header('Location: /ojt1/index.php');
    exit;
}

// Load attendance data from database
try {
    $stmt = $conn->prepare("SELECT clock_in, clock_out, hours_worked FROM attendance_records WHERE student_id = ? ORDER BY clock_in");
    if (!$stmt) {
        throw new Exception('Prepare failed');
    }
    $stmt->bind_param('s', $studentId);
    $stmt->execute();

    if (method_exists($stmt, 'get_result')) {
        $result = $stmt->get_result();
        $dbEntries = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    } else {
        $stmt->bind_result($clockIn, $clockOut, $hoursWorked);
        $dbEntries = [];
        while ($stmt->fetch()) {
            $dbEntries[] = ['clock_in' => $clockIn, 'clock_out' => $clockOut, 'hours_worked' => $hoursWorked];
        }
    }

    $entries = [];
    $studentDtr = [];
    
    foreach ($dbEntries as $dbEntry) {
        $inTime = strtotime($dbEntry['clock_in']);
        $outTime = $dbEntry['clock_out'] ? strtotime($dbEntry['clock_out']) : null;
        $date = date('Y-m-d', $inTime);

        // Create record for each entry (full or in-progress)
        if ($outTime) {
            $durationSeconds = $outTime - $inTime;
            $entries[] = [
                'in' => $inTime,
                'out' => $outTime,
                'raw_seconds' => $durationSeconds,
                'hours' => round($durationSeconds / 3600, 2),
                'minutes' => (int) floor($durationSeconds / 60),
                'seconds' => $durationSeconds % 60,
            ];

            if (!isset($studentDtr[$date])) {
                $studentDtr[$date] = [];
            }
            $studentDtr[$date]['in'] = $inTime;
            $studentDtr[$date]['out'] = $outTime;
        } else {
            // In-progress clock-in with no clock-out yet.
            if (!isset($studentDtr[$date])) {
                $studentDtr[$date] = [];
            }
            $studentDtr[$date]['in'] = $inTime;
        }
    }
    
    // Store in session for backward compatibility
    $_SESSION['attendance_entries'][$studentId] = $entries;
    $_SESSION['dtr'][$studentId] = $studentDtr;
    
} catch (Exception $e) {
    // Fallback to session if DB fails
    if (!isset($_SESSION['attendance_entries']) || !is_array($_SESSION['attendance_entries'])) {
        $_SESSION['attendance_entries'] = [];
    }
    if (!isset($_SESSION['dtr']) || !is_array($_SESSION['dtr'])) {
        $_SESSION['dtr'] = [];
    }
    if (!isset($_SESSION['attendance_entries'][$studentId])) {
        $_SESSION['attendance_entries'][$studentId] = [];
    }
    if (!isset($_SESSION['dtr'][$studentId])) {
        $_SESSION['dtr'][$studentId] = [];
    }
    $entries = &$_SESSION['attendance_entries'][$studentId];
    $studentDtr = &$_SESSION['dtr'][$studentId];
}

$today = date('Y-m-d');
$now = time();

// Daily Time Record QR + location enforcement
$allowedSite = [
    'name' => 'OJT Training Campus',
    'lat' => 14.5475,
    'lng' => 121.0430,
    'radius' => 150,
];
// Set to true to enforce geofence, false to disable and avoid this message.
$enforceGeofence = false;
$skipGeolocationCheck = true;  // Skip geolocation requirement
$expectedQRCode = !empty($_SESSION['student_id']) ? strtoupper($_SESSION['student_id']) : 'BSIS-OJT-QR-2026';
$actionMessage = '';
$errorMessage = '';

$submittedQR = strtoupper(trim($_POST['qr_code'] ?? ''));
$geoLat = isset($_POST['geo_lat']) ? floatval($_POST['geo_lat']) : null;
$geoLng = isset($_POST['geo_lng']) ? floatval($_POST['geo_lng']) : null;

function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2)
{
    $earthRadius = 6371000;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadius * $c;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['dtr_action'])) {
    $dtrAction = $_POST['dtr_action'];

    if (empty($submittedQR)) {
        $errorMessage = 'Please scan the QR code before clocking in/out.';
    } elseif ($submittedQR !== $expectedQRCode) {
        $errorMessage = 'Invalid QR code scanned. Use the designated OJT QR code.';
    } elseif (!$skipGeolocationCheck && ($geoLat === null || $geoLng === null)) {
        $errorMessage = 'Location not detected. Enable geolocation and retry.';
    } else {
        // Skip geofence checks when enforcement is not active.
        if ($enforceGeofence) {
            $distance = haversineDistance($geoLat, $geoLng, $allowedSite['lat'], $allowedSite['lng']);
            if ($distance > $allowedSite['radius']) {
                $errorMessage = "Outside permitted site radius ({$allowedSite['name']}). Move closer and retry.";
            }
        }

        if (empty($errorMessage)) {
            $actionTime = $now;

            if ($dtrAction === 'log_in') {
                if (!empty($studentDtr[$today]['in']) && empty($studentDtr[$today]['out'])) {
                    $errorMessage = 'Already logged in for today.';
                } else {
                    // mark in-progress clock-in
                    $studentDtr[$today]['in'] = $actionTime;

                    try {
                        $stmt = $conn->prepare("INSERT INTO attendance_records (student_id, clock_in, clock_out, hours_worked, location_lat, location_lng, qr_code) VALUES (?, ?, NULL, 0, ?, ?, ?)");
                        if ($stmt) {
                            $clockInTime = date('Y-m-d H:i:s', $actionTime);
                            $stmt->bind_param('ssdds', $studentId, $clockInTime, $geoLat, $geoLng, $submittedQR);
                            $stmt->execute();
                        }
                    } catch (Exception $e) {
                        error_log("Failed to create in-progress attendance record: " . $e->getMessage());
                    }

                    $actionMessage = 'Logged in successfully.';
                }
            } elseif ($dtrAction === 'log_out') {
                if (empty($studentDtr[$today]['in']) || !empty($studentDtr[$today]['out'])) {
                    $errorMessage = 'Cannot log out without logging in first.';
                } else {
                    $studentDtr[$today]['out'] = $actionTime;
                    $start = $studentDtr[$today]['in'];
                    $durationSeconds = $actionTime - $start;
                    $hours = round($durationSeconds / 3600, 2);

                    // Update the existing in-progress record if exists
                    try {
                        $stmt = $conn->prepare("SELECT id FROM attendance_records WHERE student_id = ? AND clock_out IS NULL ORDER BY clock_in DESC LIMIT 1");
                        if ($stmt) {
                            $stmt->bind_param('s', $studentId);
                            $stmt->execute();
                            $stmt->bind_result($openId);
                            if ($stmt->fetch()) {
                                $stmt->close();
                                $update = $conn->prepare("UPDATE attendance_records SET clock_out = ?, hours_worked = ?, location_lat = ?, location_lng = ?, qr_code = ? WHERE id = ?");
                                if ($update) {
                                    $clockOutTime = date('Y-m-d H:i:s', $actionTime);
                                    $update->bind_param('sddssi', $clockOutTime, $hours, $geoLat, $geoLng, $submittedQR, $openId);
                                    $update->execute();
                                    $update->close();
                                }
                            } else {
                                $stmt->close();
                                // fallback to insert if no open record
                                $insert = $conn->prepare("INSERT INTO attendance_records (student_id, clock_in, clock_out, hours_worked, location_lat, location_lng, qr_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
                                if ($insert) {
                                    $startTime = date('Y-m-d H:i:s', $start);
                                    $endTime = date('Y-m-d H:i:s', $actionTime);
                                    $insert->bind_param('sssddds', $studentId, $startTime, $endTime, $hours, $geoLat, $geoLng, $submittedQR);
                                    $insert->execute();
                                    $insert->close();
                                }
                            }
                        }
                    } catch (Exception $e) {
                        error_log("Failed to update attendance record: " . $e->getMessage());
                    }

                    // Add to local entries as well
                    $entry = [
                        'in' => $start,
                        'out' => $actionTime,
                        'raw_seconds' => $durationSeconds,
                        'hours' => $hours,
                        'minutes' => (int) floor($durationSeconds / 60),
                        'seconds' => $durationSeconds % 60,
                    ];
                    $entries[] = $entry;

                    // Update student progress (MySQL-ready)
                    try {
                        $stmt = $conn->prepare("INSERT INTO student_progress (student_id, total_hours_completed, last_updated) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE total_hours_completed = total_hours_completed + VALUES(total_hours_completed), last_updated = VALUES(last_updated)");
                        if ($stmt) {
                            $updatedTime = date('Y-m-d H:i:s');
                            $stmt->bind_param('sds', $studentId, $hours, $updatedTime);
                            $stmt->execute();
                            $stmt->close();
                        }
                    } catch (Exception $e) {
                        error_log("Failed to update student progress: " . $e->getMessage());
                    }

                    $actionMessage = 'Logged out successfully.';
                }
            }
        }
    }
}

// persist updated DTR and entries to session for current student
$_SESSION['attendance_entries'][$studentId] = $entries;
$_SESSION['dtr'][$studentId] = $studentDtr;

$clockedIn = false;
$clockStatus = 'Logged Out';

if (!empty($studentDtr[$today]['in']) && empty($studentDtr[$today]['out'])) {
    $clockedIn = true;
    $clockStatus = 'Logged In';
}


// Total completed hours from database
$completedHours = 0;
    $assignment = null;
    try {
        $assignmentStmt = $conn->prepare(
            "SELECT coa.location_name, coa.location_address, coa.lat, coa.lng, coa.assigned_date, ca.full_name AS coordinator_name
             FROM coordinator_office_assignments coa
             LEFT JOIN coordinator_accounts ca ON coa.coordinator_id = ca.id
             WHERE coa.student_id = ?
             ORDER BY coa.assigned_date DESC
             LIMIT 1"
        );
        if ($assignmentStmt) {
            $assignmentStmt->bind_param('s', $studentId);
            $assignmentStmt->execute();
            if (method_exists($assignmentStmt, 'get_result')) {
                $assignment = $assignmentStmt->get_result()->fetch_assoc();
            } else {
                $assignmentStmt->bind_result($locationName, $locationAddress, $lat, $lng, $assignedDate, $coordinatorName);
                if ($assignmentStmt->fetch()) {
                    $assignment = [
                        'location_name' => $locationName,
                        'location_address' => $locationAddress,
                        'lat' => $lat,
                        'lng' => $lng,
                        'assigned_date' => $assignedDate,
                        'coordinator_name' => $coordinatorName,
                    ];
                }
            }
        }
    } catch (Exception $e) {
        error_log('Student assignment fetch failed: ' . $e->getMessage());
    }

    try {
        $stmt = $conn->prepare("SELECT total_hours_completed FROM student_progress WHERE student_id = ?");
    if (!$stmt) {
        throw new Exception('Prepare failed');
    }
    $stmt->bind_param('s', $studentId);
    $stmt->execute();

    if (method_exists($stmt, 'get_result')) {
        $result = $stmt->get_result();
        $progress = $result ? $result->fetch_assoc() : null;
        if ($progress && isset($progress['total_hours_completed'])) {
            $completedHours = floatval($progress['total_hours_completed']);
        } else {
            // Fallback to calculating from entries when progress row is missing
            $completedSeconds = 0;
            foreach ($entries as $entry) {
                if (!empty($entry['raw_seconds'])) {
                    $completedSeconds += intval($entry['raw_seconds']);
                } elseif (!empty($entry['hours'])) {
                    $completedSeconds += intval(round(floatval($entry['hours']) * 3600));
                }
            }
            $completedHours = round($completedSeconds / 3600, 2);
        }
    } else {
        $stmt->bind_result($totalHours);
        if ($stmt->fetch()) {
            $completedHours = floatval($totalHours);
        } else {
            // Fallback to calculating from entries
            $completedSeconds = 0;
            foreach ($entries as $entry) {
                if (!empty($entry['raw_seconds'])) {
                    $completedSeconds += intval($entry['raw_seconds']);
                } elseif (!empty($entry['hours'])) {
                    $completedSeconds += intval(round(floatval($entry['hours']) * 3600));
                }
            }
            $completedHours = round($completedSeconds / 3600, 2);
        }
    }
} catch (Exception $e) {
    // Fallback to calculating from entries
    $completedSeconds = 0;
    foreach ($entries as $entry) {
        if (!empty($entry['raw_seconds'])) {
            $completedSeconds += intval($entry['raw_seconds']);
        } elseif (!empty($entry['hours'])) {
            $completedSeconds += intval(round(floatval($entry['hours']) * 3600));
        }
    }
    $completedHours = round($completedSeconds / 3600, 2);
}

$remainingHours = max(0, round($requiredHours - $completedHours, 2));

$todayEntries = array_filter($entries, function ($entry) use ($today) {
    return isset($entry['in']) && date('Y-m-d', $entry['in']) === $today;
});

usort($todayEntries, function ($a, $b) {
    return $a['in'] <=> $b['in'];
});

$amIn = '--:--';
$amOut = '--:--';
$pmIn = '--:--';
$pmOut = '--:--';

foreach ($todayEntries as $entry) {
    $entryIn = date('H', $entry['in']);
    $hasOut = !empty($entry['out']);
    if ($entryIn < 12) {
        if ($amIn === '--:--') {
            $amIn = date('h:i A', $entry['in']);
        }
        if ($hasOut) {
            $amOut = date('h:i A', $entry['out']);
        }
    } else {
        if ($pmIn === '--:--') {
            $pmIn = date('h:i A', $entry['in']);
        }
        if ($hasOut) {
            $pmOut = date('h:i A', $entry['out']);
        }
    }
}

$morningWindow = false;
$afternoonWindow = false;
// Assuming morning is before noon, afternoon after
$currentHour = date('H', $now);
if ($currentHour < 12) {
    $morningWindow = true;
} else {
    $afternoonWindow = true;
}

$logInTime = '--:--';
$logOutTime = '--:--';

if (!empty($studentDtr[$today]['in'])) {
    $logInTime = date('h:i:s A', $studentDtr[$today]['in']);
}
if (!empty($studentDtr[$today]['out'])) {
    $logOutTime = date('h:i:s A', $studentDtr[$today]['out']);
}

if ($clockedIn) {
    $clockStatus = 'Logged In';
} else {
    $clockStatus = 'Logged Out';
}

// Weekly progression (same 12-week target as attendance)
$weeklyTarget = 40;
$totalWeeks = (int) ceil($requiredHours / $weeklyTarget);

$weekStart = !empty($entries) ? strtotime('monday this week', $entries[0]['in']) : strtotime('monday this week', $now);
$weeklyHours = array_fill(0, $totalWeeks, 0);
foreach ($entries as $entry) {
    if (empty($entry['out'])) {
        continue;
    }
    $entrySeconds = !empty($entry['raw_seconds']) ? intval($entry['raw_seconds']) : (isset($entry['hours']) ? intval(round($entry['hours'] * 3600)) : 0);
    $entryHours = $entrySeconds / 3600;
    $weekIndex = (int) floor(($entry['out'] - $weekStart) / (7 * 24 * 60 * 60));
    if ($weekIndex < 0) {
        $weekIndex = 0;
    }
    if ($weekIndex < $totalWeeks) {
        $weeklyHours[$weekIndex] += $entryHours;
    }
}

$cumulativeWeeks = [];
$acc = 0;
foreach ($weeklyHours as $w) {
    $acc += $w;
    $cumulativeWeeks[] = round($acc, 2);
}

$weekLabels = [];
for ($i = 1; $i <= $totalWeeks; $i++) {
    $weekLabels[] = 'Week ' . $i;
}

$studentDepartment = trim($_SESSION['student_department'] ?? '');
$studentTheme = 'default';
if (function_exists('getDepartmentThemeClass')) {
    $studentTheme = getDepartmentThemeClass($studentDepartment);
}
?>
<body class="hold-transition sidebar-mini layout-fixed theme-<?= htmlspecialchars($studentTheme) ?>">
<div class="wrapper">

 

  <?php include __DIR__ . '/../includes/navbar.php'; ?>
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <link rel="stylesheet" href="../assets/css/dashboard-dtr.css">

 <div class="preloader flex-column justify-content-center align-items-center" style="background: linear-gradient(to bottom, blue, yellow);">
    <img class="animation__shake" src="../assets/img/users/OIP.webp" alt="Preloader" height="150" width="150" style="border-radius: 50%;">
  </div>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-12 col-sm-6">
            <h1 class="m-0 h4 h1-sm">Student OJT Dashboard</h1>
          </div>
          <div class="col-12 col-sm-6">
            <ol class="breadcrumb float-sm-right text-center text-sm-right">
              <li class="breadcrumb-item"><a href="/ojt1/students/dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Student Dashboard</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
    <section class="content">
      <div class="container-fluid">
        
        <!-- ===== STATUS BOXES (mobile: 2 per row, fixed height) ===== -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <div class="enhanced-small-box <?= getDepartmentStatusBoxClass($studentDepartment) ?>">
              <div class="inner">
                <h3><?= $requiredHours ?></h3>
                <p>Required OJT Hours</p>
              </div>
              <div class="icon">
                <i class="fas fa-briefcase"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="enhanced-small-box <?= getDepartmentStatusBoxClass($studentDepartment) ?>">
              <div class="inner">
                <h3><?= $completedHours ?></h3>
                <p>Hours Completed</p>
              </div>
              <div class="icon">
                <i class="fas fa-check-circle"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="enhanced-small-box <?= getDepartmentStatusBoxClass($studentDepartment) ?>">
              <div class="inner">
                <h3><?= $remainingHours ?></h3>
                <p>Hours Remaining</p>
              </div>
              <div class="icon">
                <i class="fas fa-hourglass-half"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="enhanced-small-box <?= getDepartmentStatusBoxClass($studentDepartment) ?>">
              <div class="inner">
                <h3><?= $assignment ? htmlspecialchars($assignment['location_name']) : 'Not Assigned' ?></h3>
                <p>Deployment Status</p>
              </div>
              <div class="icon">
                <i class="fas fa-<?= $assignment ? 'map-marker-alt' : 'question-circle' ?>"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="row align-items-stretch">
          <div class="col-md-6 d-flex">
            <div class="card card-outline h-100 w-100">
              <div class="card-header dynamic-card-header">
                <h3 class="card-title"><i class="fas fa-clock"></i> Daily Time Record</h3>
                <div class="card-tools">
                  <button id="dtr-guide-btn" class="btn btn-tool" title="How to use Daily Time Record">
                    <i class="fas fa-question-circle"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <?php if (!empty($actionMessage)): ?>
                  <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <?= htmlspecialchars($actionMessage) ?>
                  </div>
                <?php endif; ?>
                <?php if (!empty($errorMessage)): ?>
                  <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <?= htmlspecialchars($errorMessage) ?>
                  </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div>
                    <h5 class="font-weight-bold" style="font-size:1.08rem;">Daily Time Record</h5>
                    <span class="enhanced-badge badge-<?= $clockedIn ? 'success' : 'secondary' ?> py-1 px-2" style="font-size:0.78rem;"><?= $clockStatus ?></span>
                  </div>
                  <div class="text-right" style="font-size:0.83rem;">
                    <div><small class="text-muted">Worked today</small> <strong><?= number_format($completedHours, 2) ?>h</strong></div>
                    <div><small class="text-muted">Remaining</small> <strong><?= number_format($remainingHours, 2) ?>h</strong></div>
                  </div>
                </div>

                <div class="mb-4 p-3 rounded bg-light border" style="box-shadow: 0 4px 6px rgba(0,0,0,0.08);">
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <div class="card h-100 border-0" style="background: linear-gradient(135deg, #1d1f3f 0%, #252964 100%);">
                        <div class="card-body p-2">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-weight-bold text-white">Camera</span>
                            <span class="badge badge-light text-secondary">Live feed</span>
                          </div>
                          <div class="rounded" style="overflow:hidden; height: 250px; background:#000; border: 2px solid #2f3a8c;">
                            <video id="qrVideo" autoplay muted playsinline style="width:100%; height:100%; object-fit:cover;"></video>
                            <canvas id="qrCanvas" style="display:none"></canvas>
                          </div>
                          <div class="border-top pt-2 mt-2">
                            <h6 class="mb-1 text-white">Today session</h6>
                            <p class="mb-0 text-white"><strong>In:</strong> <?= $logInTime ?> | <strong>Out:</strong> <?= $logOutTime ?></p>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="card h-100 border-0" style="background:#f8f9fa;">
                        <div class="card-body p-3">
                          <h6 class="font-weight-bold text-secondary">QR Payload & Location</h6>
                          <div class="input-group input-group-sm mb-2">
                            <div class="input-group-prepend"><span class="input-group-text bg-white"><i class="fas fa-qrcode"></i></span></div>
                            <input type="text" id="qr_code" name="qr_code" value="<?= htmlspecialchars($submittedQR) ?>" class="form-control" readonly placeholder="Waiting for scan...">
                          </div>
                          <div class="mb-2" style="min-height: 250px;">
                            <div id="gmap" style="height: 250px; border: 1px solid #ced4da; border-radius: 6px;"></div>
                            <div id="gmapFallback" class="alert alert-warning text-center mt-3" style="display:none;">Google Maps is unavailable right now. Location will still be recorded, but the map is disabled.</div>
                          </div>
                          <div class="input-group input-group-sm mb-2">
                            <div class="input-group-prepend"><span class="input-group-text bg-white"><i class="fas fa-map-marker-alt"></i></span></div>
                            <input type="text" id="locationStatus" class="form-control" value="Waiting for geolocation..." readonly>
                          </div>
                          <div class="mb-2">
                            <small class="text-muted" id="scanHint">Tap LOG IN / LOG OUT to activate camera and scan QR.</small>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <form method="post" id="dtrForm">
                  <input type="hidden" name="qr_code" id="form_qr_code" value="<?= htmlspecialchars($submittedQR) ?>">
                  <input type="hidden" name="geo_lat" id="form_geo_lat" value="<?= htmlspecialchars($geoLat) ?>">
                  <input type="hidden" name="geo_lng" id="form_geo_lng" value="<?= htmlspecialchars($geoLng) ?>">
                  <input type="hidden" name="dtr_action" id="dtr_action" value="">
                  <div class="row">
                    <div class="col-6">
                      <button type="button" class="btn btn-block btn-success btn-lg btn-login" <?= ($clockedIn) ? 'disabled' : '' ?> onclick="startLogInLogOut(this, 'log_in')">LOG IN</button>
                    </div>
                    <div class="col-6">
                      <button type="button" class="btn btn-block btn-danger btn-lg btn-logout" <?= (!$clockedIn) ? 'disabled' : '' ?> onclick="startLogInLogOut(this, 'log_out')">LOG OUT</button>
                    </div>
                  </div>
                </form>

                <hr>
              </div>
            </div>
          </div>

          <div class="col-md-6 d-flex">
            <div class="card card-outline h-100 w-100">
              <div class="card-header dynamic-card-header">
                <h3 class="card-title"><i class="fas fa-watch-clock"></i> Live Watch</h3>
              </div>
              <div class="card-body text-center">
                <div class="mb-4">
                  <i id="bigWatchIcon" class="far fa-clock fa-7x text-success" style="transition: transform 0.2s linear;"></i>
                </div>
                <h1 id="bigCurrentTime" class="display-4">00:00:00</h1>
                <p class="lead" id="bigCurrentTimeLabel">Current local time</p>
                <p class="text-muted">Live clock synced to the browser clock. Tap time buttons while the watch is active for accurate attendance logs.</p>
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
<script src="../assets/js/student-dashboard.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAZki5E1oy6-azjVf93OHexQd8cIGVzX2o"></script>
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script src="../assets/js/dashboard-dtr.js"></script>

<!-- Daily Time Record Guide Modal -->
<div class="modal fade" id="dtrGuideModal" tabindex="-1" role="dialog" aria-labelledby="dtrGuideModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="dtrGuideModalLabel">
          <i class="fas fa-clock mr-2"></i>
          Daily Time Record (DTR) Guide
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-4">
          <p class="text-muted lead">Learn how to properly clock in and out for your OJT program</p>
          <div class="progress mb-4" style="height: 8px;">
            <div class="progress-bar bg-info" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="card border-info mb-3">
              <div class="card-header bg-light">
                <div class="d-flex align-items-center">
                  <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px; font-weight: bold;">1</div>
                  <div>
                    <h6 class="mb-0"><i class="fas fa-sign-in-alt text-info mr-1"></i> How to Clock In</h6>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <ol>
                  <li class="mb-2">Navigate to the <strong>Daily Time Record</strong> section on your dashboard</li>
                  <li class="mb-2">Click the green <strong>LOG IN</strong> button</li>
                  <li class="mb-2">A camera will appear - allow browser access to your camera</li>
                  <li class="mb-2">Position the <strong>QR code</strong> in the camera frame</li>
                  <li>Once scanned, your clock-in time is recorded automatically</li>
                </ol>
                <div class="alert alert-info py-2">
                  <small><i class="fas fa-info-circle mr-1"></i> <strong>Daily Attendance is monitored:</strong> Ensure you clock in when you start your OJT duties</small>
                </div>
              </div>
            </div>

            <div class="card border-danger mb-3">
              <div class="card-header bg-light">
                <div class="d-flex align-items-center">
                  <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px; font-weight: bold;">3</div>
                  <div>
                    <h6 class="mb-0"><i class="fas fa-chart-line text-danger mr-1"></i> Track Your Progress</h6>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <p><strong>Monitor your OJT hours:</strong></p>
                <ul class="list-unstyled">
                  <li class="mb-2"><i class="fas fa-hourglass-half text-warning mr-2"></i> <strong>Hours Worked Today:</strong> Shows accumulated hours for the current day</li>
                  <li class="mb-2"><i class="fas fa-hourglass-end text-success mr-2"></i> <strong>Hours Remaining:</strong> Shows remaining hours to complete your OJT requirement</li>
                  <li><i class="fas fa-clock text-primary mr-2"></i> <strong>Total Requirement:</strong> Usually 480 hours total</li>
                </ul>
              </div>
            </div>

            <div class="card border-secondary">
              <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-lightbulb text-warning mr-1"></i> Important Tips</h6>
              </div>
              <div class="card-body">
                <ul class="list-unstyled small mb-0">
                  <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> Clock in/out at the <strong>assigned OJT location only</strong></li>
                  <li class="mb-2"><i class="fas fa-map-marker-alt text-danger mr-2"></i> Location verification is enforced</li>
                  <li class="mb-2"><i class="fas fa-qrcode text-primary mr-2"></i> Always have access to the QR code at your office</li>
                  <li class="mb-2"><i class="fas fa-camera text-info mr-2"></i> Ensure good lighting for QR code scanning</li>
                  <li><i class="fas fa-phone text-secondary mr-2"></i> Smartphone camera usually works best</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card border-warning mb-3">
              <div class="card-header bg-light">
                <div class="d-flex align-items-center">
                  <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px; font-weight: bold;">2</div>
                  <div>
                    <h6 class="mb-0"><i class="fas fa-sign-out-alt text-warning mr-1"></i> How to Clock Out</h6>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <ol>
                  <li class="mb-2">When you finish your OJT duties, locate the <strong>LOG OUT</strong> button</li>
                  <li class="mb-2">Click the red <strong>LOG OUT</strong> button</li>
                  <li class="mb-2">Camera access will be requested again</li>
                  <li class="mb-2">Position the <strong>QR code</strong> in front of the camera</li>
                  <li>Once scanned, your clock-out time is recorded and hours are calculated</li>
                </ol>
                <div class="alert alert-warning py-2">
                  <small><i class="fas fa-exclamation-triangle mr-1"></i> <strong>Important:</strong> Always clock out to end your working session</small>
                </div>
              </div>
            </div>

            <div class="card border-success mb-3">
              <div class="card-header bg-light">
                <div class="d-flex align-items-center">
                  <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px; font-weight: bold;">4</div>
                  <div>
                    <h6 class="mb-0"><i class="fas fa-eye text-success mr-1"></i> View Your DTR Records</h6>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <p><strong>Access your Daily Time Record:</strong></p>
                <div class="bg-light p-2 rounded mb-2">
                  <small>Go to <strong>Attendance</strong> page to view:</small>
                </div>
                <ul class="list-unstyled">
                  <li class="mb-2"><i class="fas fa-list text-primary mr-2"></i> Complete history of all your clock-in/out records</li>
                  <li class="mb-2"><i class="fas fa-table text-info mr-2"></i> AM and PM time records organized by date</li>
                  <li class="mb-2"><i class="fas fa-percent text-success mr-2"></i> Daily and total hours progress</li>
                  <li><i class="fas fa-download text-warning mr-2"></i> Export or print your DTR records</li>
                </ul>
              </div>
            </div>

            <div class="card border-danger">
              <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-exclamation-circle text-danger mr-1"></i> Common Issues</h6>
              </div>
              <div class="card-body">
                <div class="mb-3">
                  <small class="text-muted"><i class="fas fa-question-circle mr-1"></i> <strong>Q: Camera not working?</strong></small>
                  <p class="mb-2"><small>A: Check browser permissions. Allow camera access when prompted. Try refreshing the page.</small></p>
                </div>
                <div class="mb-3">
                  <small class="text-muted"><i class="fas fa-question-circle mr-1"></i> <strong>Q: QR code won't scan?</strong></small>
                  <p class="mb-2"><small>A: Ensure good lighting and steady hand position. The QR code must be clearly visible in the frame.</small></p>
                </div>
                <div>
                  <small class="text-muted"><i class="fas fa-question-circle mr-1"></i> <strong>Q: Location error?</strong></small>
                  <p class="mb-0"><small>A: You must clock in/out at your assigned OJT office location. Check your office deployment details.</small></p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="text-center mt-4">
          <div class="alert alert-light border">
            <i class="fas fa-heart text-danger mr-2"></i>
            <strong>Support:</strong> If you have questions about your DTR, contact your OJT Coordinator or System Administrator.
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
          <i class="fas fa-times mr-1"></i> Close
        </button>
        <button type="button" class="btn btn-info" onclick="$('#dtrGuideModal').modal('hide'); toastr.success('Ready to track your hours!');">
          <i class="fas fa-play mr-1"></i> Got It!
        </button>
      </div>
    </div>
  </div>
</div>

</body>
</html>