<?php
$rootPath = $rootPath ?? '/ojt1/';

$currentPath = $_SERVER['PHP_SELF'] ?? '';
$currentPage = basename($currentPath);
$portalType = 'default';

if (strpos($currentPath, '/students/') !== false) {
    $portalType = 'student';
} elseif (strpos($currentPath, '/coodinator/') !== false || strpos($currentPath, '/coordinator/') !== false) {
    $portalType = 'coordinator';
} elseif (strpos($currentPath, '/admin/') !== false) {
    $portalType = 'admin';
}

$departmentTheme = function_exists('getDepartmentThemeClass') ? getDepartmentThemeClass() : 'default';
$navbarThemeClass = 'navbar-admin-realistic';
if ($portalType === 'student') {
    $navbarThemeClass = 'navbar-' . $departmentTheme . '-realistic';
} elseif ($portalType === 'coordinator') {
    $navbarThemeClass = 'navbar-coordinator-realistic';
}

$notificationItems = [];
$notificationCount = 0;
$notificationPage = $rootPath . 'admin/admin.php';

if (!isset($conn)) {
    @include_once __DIR__ . '/../dbconnection.php';
}
$dbReady = isset($conn) && $conn instanceof mysqli;

if ($portalType === 'coordinator') {
    $notificationPage = $rootPath . 'coodinator/pending_approvals.php';
    if ($dbReady) {
        $pendingCount = 0;
        $coordinatorDepartment = trim($_SESSION['coordinator_department'] ?? '');
        if ($coordinatorDepartment !== '') {
            $studentDepartments = function_exists('getDepartmentsForCollege')
                ? getDepartmentsForCollege($coordinatorDepartment)
                : [$coordinatorDepartment];
            if (empty($studentDepartments)) {
                $studentDepartments = [$coordinatorDepartment];
            }

            $escapedDepartments = array_map(static function ($department) use ($conn) {
                return "'" . $conn->real_escape_string($department) . "'";
            }, $studentDepartments);

            $inClause = implode(',', $escapedDepartments);
            $pendingStmt = $conn->prepare("SELECT COUNT(*) FROM students WHERE department IN ($inClause) AND registration_status = 'pending'");
            if ($pendingStmt) {
                $pendingStmt->execute();
                $pendingStmt->bind_result($pendingCount);
                $pendingStmt->fetch();
                $pendingStmt->close();
            }
        }

        if ($pendingCount > 0) {
            $notificationItems[] = [
                'href' => $notificationPage,
                'icon' => 'fas fa-user-clock text-warning',
                'title' => $pendingCount . ' student registration(s) pending approval',
                'subtitle' => 'Review new student signups for your department.',
                'time' => 'Just now',
            ];
            $notificationCount = $pendingCount;
        }
    }
      }
$userName = 'Guest';
$userEmail = 'guest@example.com';
$userAvatar = '';
$profileUrl = $rootPath . 'admin/profile.php';
$settingsUrl = $rootPath . 'admin/settings.php';
$accountLabel = 'Admin Account';

if ($portalType === 'admin') {
    if (function_exists('loadAdminUserData')) {
        $adminData = loadAdminUserData() ?? [];
        $userName = $adminData['name'] ?? $_SESSION['admin_name'] ?? 'Admin Guest';
        $userEmail = $adminData['email'] ?? $_SESSION['admin_email'] ?? 'admin@example.com';
        $userAvatar = $adminData['avatar'] ?? $_SESSION['admin_avatar'] ?? '';
    } else {
        $userName = $_SESSION['admin_name'] ?? 'Admin Guest';
        $userEmail = $_SESSION['admin_email'] ?? 'admin@example.com';
        $userAvatar = $_SESSION['admin_avatar'] ?? '';
    }
    $profileUrl = $rootPath . 'admin/profile.php';
    $settingsUrl = $rootPath . 'admin/settings.php';
    $accountLabel = 'Admin Account';
} elseif ($portalType === 'coordinator') {
    $userName = $_SESSION['coordinator_name'] ?? 'Coordinator Guest';
    $userEmail = $_SESSION['coordinator_email'] ?? 'coordinator@example.com';
    $userAvatar = $_SESSION['coordinator_avatar'] ?? '';
    $profileUrl = $rootPath . 'coodinator/profile.php';
    $settingsUrl = $rootPath . 'coodinator/settings.php';
    $accountLabel = 'Coordinator Account';
} elseif ($portalType === 'student') {
    if (function_exists('loadStudentUserData')) {
        $studentData = loadStudentUserData() ?? [];
        $userName = $studentData['name'] ?? $_SESSION['student_name'] ?? 'Student Guest';
        $userEmail = $studentData['email'] ?? $_SESSION['student_email'] ?? 'student@example.com';
        $userAvatar = $studentData['avatar'] ?? $_SESSION['student_avatar'] ?? '';
    } else {
        $userName = $_SESSION['student_name'] ?? 'Student Guest';
        $userEmail = $_SESSION['student_email'] ?? 'student@example.com';
        $userAvatar = $_SESSION['student_avatar'] ?? '';
    }
    $profileUrl = $rootPath . 'students/profile.php';
    $settingsUrl = $rootPath . 'students/settings.php';
    $accountLabel = 'Student Account';
}

$userInitial = strtoupper(substr(trim($userName), 0, 1));
?>
<style>
  .notification-dropdown {
    min-width: 360px;
    border-radius: 0.85rem;
    overflow: hidden;
    box-shadow: 0 18px 48px rgba(13, 38, 76, 0.16);
  }

  .notification-dropdown .dropdown-header {
    background: linear-gradient(135deg, #2f80ed 0%, #1d4ed8 100%);
    color: #ffffff;
  }

  .notification-dropdown .dropdown-item {
    padding: 1rem;
    transition: background-color 0.2s ease;
  }

  .notification-dropdown .dropdown-item:hover {
    background-color: rgba(37, 99, 235, 0.075);
  }

  .notification-dropdown .dropdown-footer {
    font-weight: 600;
    color: #1d4ed8;
  }
</style>

<nav class="main-header navbar navbar-expand navbar-light <?= htmlspecialchars($navbarThemeClass, ENT_QUOTES) ?>">
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>

    <?php if ($portalType === 'student'): ?>
      <?php if ($currentPage === 'attendance.php'): ?>
        <li class="nav-item d-none d-sm-inline-block"><a href="<?= htmlspecialchars($rootPath . 'students/attendance.php', ENT_QUOTES) ?>" class="nav-link active"><i class="fas fa-chart-line"></i> Progress</a></li>
      <?php elseif ($currentPage === 'dashboard.php'): ?>
        <li class="nav-item d-none d-sm-inline-block"><a href="<?= htmlspecialchars($rootPath . 'students/dashboard.php', ENT_QUOTES) ?>" class="nav-link active"><i class="fas fa-home"></i> Student OJT Dashboard</a></li>
      <?php elseif ($currentPage === 'logbook.php'): ?>
        <li class="nav-item d-none d-sm-inline-block"><a href="<?= htmlspecialchars($rootPath . 'students/logbook.php', ENT_QUOTES) ?>" class="nav-link active"><i class="fas fa-book"></i> My OJT Logbook</a></li>
      <?php elseif ($currentPage === 'documentary.php'): ?>
        <li class="nav-item d-none d-sm-inline-block"><a href="<?= htmlspecialchars($rootPath . 'students/documentary.php', ENT_QUOTES) ?>" class="nav-link active"><i class="fas fa-file-alt"></i> Daily Documentary</a></li>
      <?php else: ?>
        <li class="nav-item d-none d-sm-inline-block"><a href="<?= htmlspecialchars($rootPath . 'students/dashboard.php', ENT_QUOTES) ?>" class="nav-link"><i class="fas fa-home"></i> Student OJT Dashboard</a></li>
      <?php endif; ?>
    <?php elseif ($portalType === 'coordinator'): ?>
      <?php if ($currentPage === 'coordinator.php'): ?>
        <li class="nav-item d-none d-sm-inline-block"><a href="<?= htmlspecialchars($rootPath . 'coodinator/coordinator.php', ENT_QUOTES) ?>" class="nav-link active"><i class="fas fa-home"></i> Coordinator Dashboard</a></li>
      <?php elseif ($currentPage === 'students.php'): ?>
        <li class="nav-item d-none d-sm-inline-block"><a href="<?= htmlspecialchars($rootPath . 'coodinator/students.php', ENT_QUOTES) ?>" class="nav-link active"><i class="fas fa-users"></i> Student Tracker</a></li>
      <?php elseif ($currentPage === 'reports.php'): ?>
        <li class="nav-item d-none d-sm-inline-block"><a href="<?= htmlspecialchars($rootPath . 'coodinator/reports.php', ENT_QUOTES) ?>" class="nav-link active"><i class="fas fa-chart-bar"></i> Reports</a></li>
      <?php else: ?>
        <li class="nav-item d-none d-sm-inline-block"><a href="<?= htmlspecialchars($rootPath . 'coodinator/coordinator.php', ENT_QUOTES) ?>" class="nav-link"><i class="fas fa-home"></i> Coordinator Dashboard</a></li>
      <?php endif; ?>
    <?php elseif ($portalType === 'admin'): ?>
      <li class="nav-item d-none d-sm-inline-block"><a href="<?= htmlspecialchars($rootPath . 'admin/admin.php', ENT_QUOTES) ?>" class="nav-link active"><i class="fas fa-home"></i> Admin Dashboard</a></li>
    <?php else: ?>
      <li class="nav-item d-none d-sm-inline-block"><a href="<?= htmlspecialchars($rootPath . 'students/dashboard.php', ENT_QUOTES) ?>" class="nav-link"><i class="fas fa-home"></i> Dashboard</a></li>
    <?php endif; ?>
  </ul>

  <ul class="navbar-nav ml-auto">
    <li class="nav-item">
      <div class="navbar-search-block">
        <form class="form-inline">
          <div class="input-group input-group-sm">
            <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
            <div class="input-group-append">
              <button class="btn btn-navbar" type="submit"><i class="fas fa-search"></i></button>
              <button class="btn btn-navbar" type="button" data-widget="navbar-search"><i class="fas fa-times"></i></button>
            </div>
          </div>
        </form>
      </div>
    </li>

    <?php if ($portalType === 'coordinator'): ?>
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="fas fa-bell fa-lg text-white"></i>
          <?php if ($notificationCount > 0): ?>
            <span class="badge badge-danger navbar-badge"><?= (int) $notificationCount ?></span>
          <?php endif; ?>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right notification-dropdown p-0">
          <span class="dropdown-item dropdown-header"><?= (int) $notificationCount ?> Notification<?= $notificationCount === 1 ? '' : 's' ?></span>
          <?php foreach ($notificationItems as $index => $item): ?>
            <?php if ($index > 0): ?><div class="dropdown-divider"></div><?php endif; ?>
            <a href="<?= htmlspecialchars($item['href'], ENT_QUOTES) ?>" class="dropdown-item">
              <i class="<?= htmlspecialchars($item['icon'], ENT_QUOTES) ?> mr-2"></i> <?= htmlspecialchars($item['title'], ENT_QUOTES) ?>
              <?php if (!empty($item['time'])): ?><span class="float-right text-muted text-sm"><?= htmlspecialchars($item['time'], ENT_QUOTES) ?></span><?php endif; ?>
              <div class="text-sm text-muted"><?= htmlspecialchars($item['subtitle'] ?? '', ENT_QUOTES) ?></div>
            </a>
          <?php endforeach; ?>
          <div class="dropdown-divider"></div>
          <a href="<?= htmlspecialchars($notificationPage, ENT_QUOTES) ?>" class="dropdown-item dropdown-footer">See All Notifications</a>
        </div>
      </li>
    <?php endif; ?>

    <li class="nav-item">
      <a class="nav-link" data-widget="fullscreen" href="#" role="button"><i class="fas fa-expand-arrows-alt"></i></a>
    </li>
  </ul>
</nav>
