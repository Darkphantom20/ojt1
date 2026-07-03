<!DOCTYPE html>
<html lang="en">
<?php
session_start();

if (empty($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header('Location: /ojt1/index.php');
    exit;
}

$pageTitle = 'Student Progress';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../dbconnection.php';

$studentId = strtoupper(trim($_SESSION['student_id'] ?? ''));
if ($studentId === '') {
    header('Location: /ojt1/index.php');
    exit;
}

$required = isset($_SESSION['student_required_ojt_hours']) ? intval($_SESSION['student_required_ojt_hours']) : 480; 
$weeklyTarget = 40; 
$totalWeeks = (int) ceil($required / $weeklyTarget);

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

$now = time();
$today = date('Y-m-d');



$allowedSite = [
  'name' => 'OJT Training Campus',
  'lat' => 14.5475,
  'lng' => 121.0430,
  'radius' => 150,
];

function isValidStudentIdFormat(string $id): bool
{
  return preg_match('/^TC-\d{2}-[A-Z]-\d{5}$/', strtoupper($id)) === 1;
}

$expectedQRCode = strtoupper(trim($studentId));
$normalizedExpectedQRCode = preg_replace('/[^A-Z0-9\-]/', '', $expectedQRCode);

$actionMessage = '';
$errorMessage = '';

$geoLat = isset($_POST['geo_lat']) ? floatval($_POST['geo_lat']) : null;
$geoLng = isset($_POST['geo_lng']) ? floatval($_POST['geo_lng']) : null;
$rawQrInput = strtoupper(trim($_POST['qr_code'] ?? ''));
$normalizedInputQR = preg_replace('/[^A-Z0-9\-]/', '', $rawQrInput);
$qrCode = '';
if ($rawQrInput !== '') {
  if (preg_match('/TC-\d{2}-[A-Z]-\d{5}/', $normalizedInputQR, $m) || preg_match('/TC-\d{2}-[A-Z]-\d{5}/', $rawQrInput, $m)) {
    $qrCode = $m[0];
  } else {
    $qrCode = $normalizedInputQR;
  }
}

function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2)
{
  $earthRadius = 6371000;
  $dLat = deg2rad($lat2 - $lat1);
  $dLon = deg2rad($lon2 - $lon1);
  $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
  $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
  return $earthRadius * $c;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $attendanceAction = $_POST['attendance_action'] ?? $_POST['attendance_action_auto'] ?? '';

  
  if (empty($attendanceAction) && !empty($qrCode) && $qrCode === $expectedQRCode) {
    $attendanceAction = empty($_SESSION['clock_in']) ? 'clock_in' : 'clock_out';
  }

  if (empty($expectedQRCode)) {
    $errorMessage = 'Student QR code is not set. Please login first.';
  } elseif (empty($qrCode)) {
    $errorMessage = 'QR code is required to check in/out.';
  } elseif (!isValidStudentIdFormat($qrCode) || preg_replace('/[^A-Z0-9\-]/', '', $qrCode) !== $normalizedExpectedQRCode) {
    $errorMessage = 'Invalid QR code scanned. Use the designated OJT QR code based on your registered ID: '.$expectedQRCode.'.';
  } else {
    
    $distanceMessage = '';
    if ($geoLat !== null && $geoLng !== null) {
      $currentDistance = haversineDistance($geoLat, $geoLng, $allowedSite['lat'], $allowedSite['lng']);
      if ($currentDistance > $allowedSite['radius']) {
        $distanceMessage = ' (WARNING: outside allowed site '.$allowedSite['name'].')';
      }
    }

    if ($attendanceAction === 'clock_in') {
      if (!empty($_SESSION['clock_in'])) {
        $errorMessage = 'You are already clocked in.';
      } else {
        $_SESSION['clock_in'] = $now;

        
        try {
          $stmt = $conn->prepare("INSERT INTO attendance_records (student_id, clock_in, location_lat, location_lng, qr_code) VALUES (?, ?, ?, ?, ?)");
          if ($stmt) {
              $clockInTime = date('Y-m-d H:i:s', $now);
              $geoLatVal = $geoLat ?? 0.0;
              $geoLngVal = $geoLng ?? 0.0;
              $stmt->bind_param('ssdds', $studentId, $clockInTime, $geoLatVal, $geoLngVal, $qrCode);
              $stmt->execute();
              $attendanceId = $conn->insert_id;
              $_SESSION['current_attendance_id'] = $attendanceId;
          } else {
              throw new Exception('Prepare failed for attendance insert');
          }
        } catch (Exception $e) {
          error_log("Error saving clock in: " . $e->getMessage());
          $errorMessage = 'Error saving attendance record.';
        }

        $studentDtr[$today]['am_in'] = $studentDtr[$today]['am_in'] ?? $now;
        $actionMessage = 'Clocked in successfully at '.date('h:i:s A', $now).' in '.$allowedSite['name'].''.$distanceMessage.'.';
      }
    } elseif ($attendanceAction === 'clock_out') {
      if (empty($_SESSION['clock_in'])) {
        $errorMessage = 'You are not currently clocked in.';
      } else {
        $clockIn = $_SESSION['clock_in'];
        $durationSeconds = max(0, $now - $clockIn);
        $hours = round($durationSeconds / 3600, 2);

        
        try {
          $attendanceId = $_SESSION['current_attendance_id'] ?? null;
          if ($attendanceId) {
            $stmt = $conn->prepare("UPDATE attendance_records SET clock_out = ?, hours_worked = ? WHERE id = ? AND student_id = ?");
            if ($stmt) {
              $clockOutTime = date('Y-m-d H:i:s', $now);
              $stmt->bind_param('sdis', $clockOutTime, $hours, $attendanceId, $studentId);
              $stmt->execute();
            } else {
              throw new Exception('Prepare failed for attendance update');
            }

            
            $stmt = $conn->prepare("INSERT INTO student_progress (student_id, total_hours_completed, last_updated) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE total_hours_completed = total_hours_completed + VALUES(total_hours_completed), last_updated = VALUES(last_updated)");
            if ($stmt) {
              $lastUpdated = date('Y-m-d H:i:s', $now);
              $stmt->bind_param('sds', $studentId, $hours, $lastUpdated);
              $stmt->execute();
            } else {
              throw new Exception('Prepare failed for student_progress update');
            }
          }
        } catch (Exception $e) {
          error_log("Error saving clock out: " . $e->getMessage());
          $errorMessage = 'Error saving attendance record.';
        }

        $entries[] = [
          'in' => $clockIn,
          'out' => $now,
          'raw_seconds' => $durationSeconds,
          'hours' => $hours,
          'minutes' => (int) floor($durationSeconds / 60),
          'seconds' => $durationSeconds % 60,
        ];

        if (date('H', $clockIn) < 12) {
          $studentDtr[$today]['am_out'] = $now;
        } else {
          $studentDtr[$today]['pm_out'] = $now;
        }

        unset($_SESSION['clock_in']);
        unset($_SESSION['current_attendance_id']);
        $actionMessage = 'Clocked out successfully at '.date('h:i:s A', $now).' after '.$hours.' hours'.$distanceMessage.'.';
      }
    }
  }
}


$today = date('Y-m-d', $now);
$todayEntries = array_filter($_SESSION['attendance_entries'][$studentId], function ($entry) use ($today) {
  return isset($entry['in']) && date('Y-m-d', $entry['in']) === $today;
});


if (!empty($_SESSION['clock_in']) && date('Y-m-d', $_SESSION['clock_in']) === $today) {
  $todayEntries[] = [
    'in' => $_SESSION['clock_in'],
    'out' => null,
  ];
}

$entries = &$_SESSION['attendance_entries'][$studentId];
$studentDtr = &$_SESSION['dtr'][$studentId];

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

$clockStatus = empty($_SESSION['clock_in']) ? 'Not clocked in' : 'Clocked in';

$entries = &$_SESSION['attendance_entries'][$studentId];


if (!empty($entries)) {
  $firstEntry = $entries[0]['in'];
  $weekStart = strtotime('monday this week', $firstEntry);
} else {
  $weekStart = strtotime('monday this week', $now);
}

$weeklyHours = array_fill(0, $totalWeeks, 0);
foreach ($entries as $entry) {
  $entryOut = $entry['out'];
  $entryHours = $entry['hours'];
  $weekIndex = (int) floor(($entryOut - $weekStart) / (7 * 24 * 60 * 60));
  if ($weekIndex < 0) {
    $weekIndex = 0;
  }
  if ($weekIndex >= $totalWeeks) {
    continue;
  }
  $weeklyHours[$weekIndex] += $entryHours;
}

$cumulative = [];
$total = 0;
foreach ($weeklyHours as $hours) {
  $total += $hours;
  $cumulative[] = round($total, 2);
}

if (!empty($_SESSION['clock_in'])) {
  $elapsedHours = (time() - $_SESSION['clock_in']) / 3600;
  $total += $elapsedHours;
}

$completed = $total;
$remaining = max(0, $required - $completed);
$percent = $required > 0 ? round(($completed / $required) * 100, 2) : 0;

$labels = [];
for ($w = 1; $w <= $totalWeeks; $w++) {
  $labels[] = 'Week ' . $w;
}


$chartLabels = $labels;
$chartData = $cumulative;


if (count(array_filter($chartData, fn($h) => $h > 0)) === 0) {
  
  
}


?>
<body class="hold-transition sidebar-mini layout-fixed student-progress-page">
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
          <div class="col-sm-6"><h1 class="m-0">Progress</h1></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Progress</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <div class="card card-success realistic-progress-panel">
          <div class="card-header"><h3 class="card-title">OJT Hours Progress (<?= $required ?>h total)</h3></div>
          <div class="card-body">
            <div class="row mb-3">
              <div class="col-md-3"><strong>Total Required:</strong> <?= $required ?>h</div>
              <div class="col-md-3"><strong>Completed:</strong> <?= $completed ?>h</div>
              <div class="col-md-3"><strong>Remaining:</strong> <?= $remaining ?>h</div>
              <div class="col-md-3"><strong>Progress:</strong> <?= $percent ?>%</div>
            </div>
            <div class="progress mb-1">
              <div class="progress-bar" role="progressbar" style="width: <?= max(0, min(100, $percent)) ?>%;" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="progress-text"><?= $percent ?>% Complete • <?= $remaining ?>h Remaining</div>
            <div class="row mb-3">
              <div class="col-md-12">
                <span class="badge badge-info"><?= htmlspecialchars($clockStatus) ?></span>
              </div>
            </div>

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

            <div class="row mb-3">
              <div class="col-md-6">
                <div class="card card-outline card-primary">
                  <div class="card-header p-2"><strong>AM In / Out</strong></div>
                  <div class="card-body p-2">
                    <p class="mb-1">In: <?= $amIn ?></p>
                    <p class="mb-1">Out: <?= $amOut ?></p>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card card-outline card-secondary">
                  <div class="card-header p-2"><strong>PM In / Out</strong></div>
                  <div class="card-body p-2">
                    <p class="mb-1">In: <?= $pmIn ?></p>
                    <p class="mb-1">Out: <?= $pmOut ?></p>
                  </div>
                </div>
              </div>
            </div>

            <h5>Progress Chart</h5>
            <div class="chart">
              <canvas id="hourLineChart" style="min-height: 250px; height: 250px; max-height: 250px; width: 100%;"></canvas>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
  <?php include __DIR__ . '/../includes/script.php'; ?>
</div>
<script>
  window.studentAttendanceConfig = {
    chartLabels: <?= json_encode($chartLabels) ?>,
    chartData: <?= json_encode($chartData) ?>,
    required: <?= $required ?>
  };
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAZki5E1oy6-azjVf93OHexQd8cIGVzX2o"></script>
<script src="../assets/js/student-attendance.js"></script>
<script src="../assets/js/attendance-map.js"></script>
<script src="../assets/js/attendance-chart.js"></script>
</body>
</html>
