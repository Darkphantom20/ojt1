<!DOCTYPE html>
<html lang="en">
<?php
session_start();
$pageTitle = 'My OJT Logbook';
include __DIR__ . '/../includes/header.php';
?>
<?php
require_once __DIR__ . '/../dbconnection.php';

$studentId = strtoupper(trim($_SESSION['student_id'] ?? ''));
$entries = [];
if ($studentId !== '' && !empty($_SESSION['attendance_entries'][$studentId]) && is_array($_SESSION['attendance_entries'][$studentId])) {
  $entries = $_SESSION['attendance_entries'][$studentId];
}

if (empty($entries) && $studentId !== '') {
  $stmt = $conn->prepare('SELECT clock_in, clock_out, hours_worked, location_lat, location_lng, qr_code FROM attendance_records WHERE student_id = ? ORDER BY clock_in ASC');
  if ($stmt) {
    $stmt->bind_param('s', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      $entryIn = strtotime($row['clock_in']);
      $entryOut = !empty($row['clock_out']) ? strtotime($row['clock_out']) : null;
      $entries[] = [
        'in' => $entryIn,
        'out' => $entryOut,
        'raw_seconds' => $entryOut ? ($entryOut - $entryIn) : null,
        'hours' => $row['hours_worked'] !== null ? floatval($row['hours_worked']) : ($entryOut ? round(($entryOut - $entryIn) / 3600, 2) : null),
        'location_lat' => $row['location_lat'],
        'location_lng' => $row['location_lng'],
        'qr_code' => $row['qr_code'],
      ];
    }
    $stmt->close();
  }
}

$today = date('Y-m-d');

$selectedDate = isset($_GET['day']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['day']) ? $_GET['day'] : $today;

$calendarMonth = isset($_GET['month']) ? intval($_GET['month']) : intval(date('n', strtotime($selectedDate)));
$calendarYear = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y', strtotime($selectedDate)));
if ($calendarMonth < 1 || $calendarMonth > 12) { $calendarMonth = intval(date('n', strtotime($selectedDate))); }
if ($calendarYear < 2000 || $calendarYear > 2099) { $calendarYear = intval(date('Y', strtotime($selectedDate))); }

$entriesByDate = [];
foreach ($entries as $entry) {
  if (empty($entry['in'])) {
    continue;
  }

  $inTime = intval($entry['in']);
  $outTime = isset($entry['out']) && $entry['out'] ? intval($entry['out']) : null;
  
  $day = date('Y-m-d', $inTime);

  if (!isset($entriesByDate[$day])) {
    $entriesByDate[$day] = [
      'entries' => [],
      'seconds' => 0,
      'hours' => 0,
    ];
  }

  $rawSeconds = 0;
  $hours = 0;
  if ($outTime) {
    $rawSeconds = isset($entry['raw_seconds']) ? intval($entry['raw_seconds']) : max(0, $outTime - $inTime);
    $hours = isset($entry['hours']) ? floatval($entry['hours']) : round($rawSeconds / 3600, 2);
  } else {
    
    $rawSeconds = time() - $inTime;
    $hours = round($rawSeconds / 3600, 2);
    $entry['raw_seconds'] = $rawSeconds;
    $entry['hours'] = $hours;
  }

  $entry['_in_progress'] = !$outTime;

  $entriesByDate[$day]['entries'][] = $entry;
  $entriesByDate[$day]['seconds'] += $rawSeconds;
  $entriesByDate[$day]['hours'] += $hours;
}


$studentId = strtoupper(trim($_SESSION['student_id'] ?? ''));
if ($studentId !== '' && isset($_SESSION['dtr'][$studentId][$today]['in']) && empty($_SESSION['dtr'][$studentId][$today]['out'])) {
  $inTime = intval($_SESSION['dtr'][$studentId][$today]['in']);
  $now = time();
  if ($now > $inTime) {
    $activeSeconds = $now - $inTime;
    $activeHours = round($activeSeconds / 3600, 2);
    if (!isset($entriesByDate[$today])) {
      $entriesByDate[$today] = [ 'entries' => [], 'seconds' => 0, 'hours' => 0 ];
    }
    $entriesByDate[$today]['seconds'] += $activeSeconds;
    $entriesByDate[$today]['hours'] += $activeHours;
    $entriesByDate[$today]['entries'][] = [
      'in' => $inTime,
      'out' => null,
      'raw_seconds' => $activeSeconds,
      'hours' => $activeHours,
    ];
  }
}


$dailySeconds = isset($entriesByDate[$selectedDate]) ? $entriesByDate[$selectedDate]['seconds'] : 0;
$dailyHours = isset($entriesByDate[$selectedDate]) ? round($entriesByDate[$selectedDate]['hours'], 2) : 0;
$dailyH = intdiv($dailySeconds, 3600);
$dailyM = intdiv($dailySeconds % 3600, 60);
$dailyS = $dailySeconds % 60;

if (isset($_GET['api']) && $_GET['api'] === 'logbook_calendar') {
  header('Content-Type: application/json');
  echo json_encode([
    'today' => $today,
    'selectedDate' => $selectedDate,
    'calendarMonth' => $calendarMonth,
    'calendarYear' => $calendarYear,
    'entriesByDate' => array_map(function ($dayData) {
      return [
        'entries' => $dayData['entries'],
        'seconds' => $dayData['seconds'],
        'hours' => round(floatval($dayData['hours']), 2),
      ];
    }, $entriesByDate),
  ]);
  exit;
}

?>
<body class="hold-transition sidebar-mini layout-fixed student-logbook-page dept-<?php echo strtolower(str_replace([' ', '(', ')'], ['-', '', ''], $_SESSION['student_department'] ?? 'default')); ?>">
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
            <h1 class="m-0">My OJT Logbook</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Logbook</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <div class="card card-outline card-secondary mb-3" style="border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.1);">
          <div class="card-header dynamic-card-header" style="color: white; border-radius: 15px 15px 0 0; padding: 20px;">
            <div class="d-flex flex-column header-top">
              <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center" style="gap: 1rem;">
                <h3 class="card-title mb-3 mb-md-0" style="font-weight: 600; font-size: 1.5rem;">📅 Real-time Logbook Calendar</h3>
                <span id="lb-month-year" class="font-weight-bold text-center" style="font-size: 1.2rem; min-width: 150px; transition: var(--button-transition);"></span>
              </div>
              <div class="header-controls d-flex flex-column flex-sm-row justify-content-center align-items-center">
                <div class="button-group">
                  <button type="button" id="lb-prev-year" class="btn btn-light btn-sm lb-btn-circle" onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';"><<</button>
                  <button type="button" id="lb-prev-month" class="btn btn-light btn-sm lb-btn-circle" onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';"><</button>
                  <button type="button" id="lb-next-month" class="btn btn-light btn-sm lb-btn-circle" onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">></button>
                  <button type="button" id="lb-next-year" class="btn btn-light btn-sm lb-btn-circle" onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">>></button>
                </div>
                <div class="button-group">
                  <button type="button" id="lb-today" class="btn btn-warning btn-sm lb-btn-action" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 4px 12px rgba(255,193,7,0.4)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">Today</button>
                  <button type="button" id="lb-refresh" class="btn btn-success btn-sm lb-btn-action" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 4px 12px rgba(40,167,69,0.4)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">🔄 Refresh</button>
                </div>
              </div>
            </div>
          </div>
          <div class="card-body p-4" id="logbook-calendar-card" style="background: var(--calendar-bg);">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <div id="lb-now" class="font-weight-bold text-primary" style="font-size: 1.1rem;"></div>
              <div id="lb-today-info" class="small text-secondary bg-white px-3 py-1 rounded-pill" style="border: 1px solid #e9ecef;"></div>
            </div>
            <div id="logbook-calendar-grid" class="table-responsive"></div>
            <div class="text-center mt-4">
              <div class="d-flex justify-content-center flex-wrap gap-3">
                <span class="badge badge-light px-3 py-2" style="border-radius: 20px;"><span style="color: #007bff;">●</span> Today</span>
                <span class="badge badge-light px-3 py-2" style="border-radius: 20px;"><span style="color: #28a745;">●</span> Has Entries</span>
                <span class="badge badge-light px-3 py-2" style="border-radius: 20px;"><span style="color: #ffc107;">●</span> Weekend</span>
                <span class="badge badge-light px-3 py-2" style="border-radius: 20px;"><span style="color: #6c757d;">●</span> Other Month</span>
              </div>
              <p class="text-muted mt-3 mb-0" style="font-size: 0.9rem;">Click any date to view detailed logbook entries</p>
            </div>
          </div>
        </div>

        <!-- Logbook Details Modal -->
        <div class="modal fade" id="logbookModal" tabindex="-1" role="dialog" aria-labelledby="logbookModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="logbookModalLabel">Logbook Entries for <span id="modal-date"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body" id="modal-body">
                <!-- Content will be populated by JavaScript -->
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
  <?php include __DIR__ . '/../includes/script.php'; ?>
  <script>
    window.studentLogbookConfig = {
      calendarMonth: <?= (int) $calendarMonth ?>,
      calendarYear: <?= (int) $calendarYear ?>,
      selectedDate: <?= json_encode((string) $selectedDate) ?>,
      entriesByDate: <?= json_encode(array_map(function($dayData){ return ['entries' => $dayData['entries'], 'seconds' => $dayData['seconds'], 'hours' => round(floatval($dayData['hours']), 2)]; }, $entriesByDate)) ?>,
      studentDepartment: <?= json_encode((string) ($_SESSION['student_department'] ?? '')) ?>
    };
  </script>
  <script src="../assets/js/student-logbook.js"></script>
</div>
</body>
</html>