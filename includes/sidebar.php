<?php
if (!function_exists('getDepartmentThemeClass')) {
    function getDepartmentThemeClass($department = null) {
        $department = strtolower(trim($department ?? $_SESSION['student_department'] ?? $_SESSION['coordinator_department'] ?? ''));
        if ($department === '') {
            return 'default';
        }

        if (strpos($department, 'business administration') !== false
            || strpos($department, 'agribusiness') !== false
            || strpos($department, 'bsab') !== false
            || strpos($department, 'hospitality management') !== false
            || strpos($department, 'bshm') !== false
            || strpos($department, 'financial management') !== false
            || strpos($department, 'agriculture business') !== false) {
            return 'yellow';
        }

        if (strpos($department, 'computer science') !== false
            || strpos($department, 'information systems') !== false
            || strpos($department, 'bscs') !== false
            || strpos($department, 'bsis') !== false) {
            return 'purple';
        }

        if (strpos($department, 'criminology') !== false
            || strpos($department, 'criminal justice') !== false
            || strpos($department, 'bscrim') !== false
            || strpos($department, 'baels') !== false
            || strpos($department, 'english language studies') !== false) {
            return 'red';
        }

        if (strpos($department, 'agriculture business') !== false) {
            return 'yellow';
        }

        if (strpos($department, 'bsa') !== false
            || strpos($department, 'animal science') !== false
            || strpos($department, 'crop science') !== false
            || strpos($department, 'plant pathology') !== false
            || strpos($department, 'soil science') !== false
            || strpos($department, 'bsf') !== false
            || strpos($department, 'forestry') !== false
            || strpos($department, 'agriculture') !== false) {
            return 'green';
        }

        if (strpos($department, 'beed') !== false
            || strpos($department, 'bped') !== false
            || strpos($department, 'bsed') !== false
            || strpos($department, 'elementary education') !== false
            || strpos($department, 'secondary education') !== false
            || strpos($department, 'physical education') !== false
            || strpos($department, 'education') !== false) {
            return 'blue';
        }

        return 'default';
    }
}
$departmentThemeClass = getDepartmentThemeClass();
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4 theme-<?= htmlspecialchars($departmentThemeClass) ?>">
    <!-- Brand Logo -->

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <?php
      if (!function_exists('loadAdminUserData')) {
          function loadAdminUserData() {
              // First priority: load from database if admin is logged in
              if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
                  // Fallback to JSON when session admin ID is unavailable
                  $path = __DIR__ . '/../admin/admin_users.json';
                  if (!file_exists($path)) {
                      return null;
                  }
                  $data = json_decode(file_get_contents($path), true);
                  return $data['admin'] ?? null;
              }

              global $conn;
              if (empty($conn) || !($conn instanceof mysqli)) {
                  @require_once __DIR__ . '/../dbconnection.php';
              }
              if (empty($conn) || !($conn instanceof mysqli)) {
                  return null;
              }

              try {
                  $stmt = $conn->prepare("SELECT name, email, avatar FROM admin_users WHERE id = ? LIMIT 1");
                  if (!$stmt) {
                      return null;
                  }
                  $stmt->bind_param('i', $_SESSION['admin_id']);
                  $stmt->execute();
                  if (method_exists($stmt, 'get_result')) {
                      $result = $stmt->get_result();
                      return $result ? $result->fetch_assoc() : null;
                  }
                  $stmt->bind_result($name, $email, $avatar);
                  if ($stmt->fetch()) {
                      return ['name' => $name, 'email' => $email, 'avatar' => $avatar];
                  }
                  return null;
              } catch (Exception $e) {
                  // In case of DB fail, fallback to JSON
                  $path = __DIR__ . '/../admin/admin_users.json';
                  if (!file_exists($path)) {
                      return null;
                  }
                  $data = json_decode(file_get_contents($path), true);
                  return $data['admin'] ?? null;
              }
          }
      }

      if (!function_exists('loadCoordinatorDepartmentData')) {
          function loadCoordinatorDepartmentData() {
              if (!isset($_SESSION['coordinator_id'])) {
                  return [];
              }
              global $conn;
              if (empty($conn)) {
                  @require_once __DIR__ . '/../dbconnection.php';
              }
              if (empty($conn) || !($conn instanceof mysqli)) {
                  return [];
              }
              try {
                  $stmt = $conn->prepare("SELECT department FROM coordinator_department_assignments WHERE coordinator_id = ? ORDER BY department");
                  if (!$stmt) {
                      return [];
                  }
                  $stmt->bind_param('i', $_SESSION['coordinator_id']);
                  $stmt->execute();
                  if (method_exists($stmt, 'get_result')) {
                      $result = $stmt->get_result();
                      return $result ? array_column($result->fetch_all(MYSQLI_ASSOC), 'department') : [];
                  }
                  $stmt->bind_result($department);
                  $departments = [];
                  while ($stmt->fetch()) {
                      $departments[] = $department;
                  }
                  return $departments;
              } catch (Exception $e) {
                  return [];
              }
          }
      }

      if (!function_exists('loadStudentUserData')) {
          function loadStudentUserData() {
              if (!isset($_SESSION['student_id'])) {
                  return null;
              }
              global $conn;
              if (empty($conn)) {
                  @require_once __DIR__ . '/../dbconnection.php';
              }
              if (empty($conn) || !($conn instanceof mysqli)) {
                  return null;
              }
              try {
                  $stmt = $conn->prepare("SELECT name, email, avatar FROM students WHERE student_id = ? LIMIT 1");
                  if (!$stmt) {
                      return null;
                  }
                  $stmt->bind_param('s', $_SESSION['student_id']);
                  $stmt->execute();
                  if (method_exists($stmt, 'get_result')) {
                      $result = $stmt->get_result();
                      return $result ? $result->fetch_assoc() : null;
                  }
                  $stmt->bind_result($name, $email, $avatar);
                  if ($stmt->fetch()) {
                      return ['name' => $name, 'email' => $email, 'avatar' => $avatar];
                  }
                  return null;
              } catch (Exception $e) {
                  return null;
              }
          }
      }

      $currentPath = $_SERVER['PHP_SELF'] ?? '';
      $portalType = 'default';
      if (strpos($currentPath, '/students/') !== false) {
        $portalType = 'student';
      } elseif (strpos($currentPath, '/coodinator/') !== false || strpos($currentPath, '/coordinator/') !== false) {
        $portalType = 'coordinator';
      } elseif (strpos($currentPath, '/admin/') !== false) {
        $portalType = 'admin';
      }

      if ($portalType === 'admin') {
        $adminData = loadAdminUserData();
        $userName = $adminData['name'] ?? $_SESSION['admin_name'] ?? $_SESSION['user_name'] ?? 'Admin Guest';
        $userEmail = $adminData['email'] ?? $_SESSION['admin_email'] ?? $_SESSION['user_email'] ?? 'admin@example.com';
        $userAvatar = $adminData['avatar'] ?? $_SESSION['admin_avatar'] ?? $_SESSION['user_avatar'] ?? '';
      } elseif ($portalType === 'coordinator') {
        $userName = $_SESSION['coordinator_name'] ?? $_SESSION['user_name'] ?? 'Coordinator Guest';
        $userEmail = $_SESSION['coordinator_email'] ?? $_SESSION['user_email'] ?? 'coordinator@example.com';
        $userAvatar = $_SESSION['coordinator_avatar'] ?? $_SESSION['user_avatar'] ?? '';
        $coordinatorDepartments = loadCoordinatorDepartmentData();
      } elseif ($portalType === 'student') {
        $studentData = loadStudentUserData();
        $userName = $studentData['name'] ?? $_SESSION['student_name'] ?? $_SESSION['user_name'] ?? 'Student Guest';
        $userEmail = $studentData['email'] ?? $_SESSION['student_email'] ?? $_SESSION['user_email'] ?? 'student@example.com';
        $userAvatar = $studentData['avatar'] ?? $_SESSION['student_avatar'] ?? $_SESSION['user_avatar'] ?? '';
      } else {
        $userName = $_SESSION['user_name'] ?? 'Guest';
        $userEmail = $_SESSION['user_email'] ?? 'guest@example.com';
        $userAvatar = $_SESSION['user_avatar'] ?? '';
      }

      $userInitial = strtoupper(substr(trim($userName), 0, 1));
      ?>
      <style>
      .user-panel {
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.22);
        border-radius: 0.6rem;
        padding: 0.65rem;
        align-items: center;
        justify-content: center;
      }
      .user-panel .image {
        min-width: 42px;
        margin-right: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      .user-panel .info {
        display: flex;
        flex-direction: column;
        justify-content: center;
        flex: 1;
        text-align: left;
      }
      .user-panel .info a {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }
      .user-panel .info small {
        display: block;
        margin-top: 2px;
      }
      .user-panel .department-badges {
        margin-top: 4px;
        display: flex;
        flex-wrap: wrap;
        gap: 2px;
      }
      .user-panel .department-badge {
        font-size: 0.65rem;
        padding: 1px 4px;
        border-radius: 3px;
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.3);
      }
      .user-panel .quick-links {
        margin-top: 6px;
        opacity: 0;
        max-height: 0;
        overflow: hidden;
        transition: opacity 0.25s ease, max-height 0.25s ease;
      }
      .user-panel .quick-links.visible {
        opacity: 1;
        max-height: 80px;
      }
      .user-panel .quick-links a {
        margin-right: 4px;
      }

      /* Mini sidebar (collapsed) style improvements */
      body.sidebar-collapse .user-panel {
        padding: 0.35rem;
        justify-content: center;
        text-align: center;
      }
      body.sidebar-collapse .user-panel .image {
        margin-right: 0;
      }
      body.sidebar-collapse .user-panel .image img,
      body.sidebar-collapse .user-panel .image .img-circle {
        width: 32px !important;
        height: 32px !important;
        border-radius: 50%;
      }
      body.sidebar-collapse .user-panel .info {
        display: none !important;
      }
      body.sidebar-collapse .user-panel .quick-links {
        display: none !important;
        max-height: 0 !important;
        opacity: 0 !important;
      }
      body.sidebar-collapse .user-panel::after {
        content: attr(data-user-tooltip);
        position: absolute;
        left: 100%;
        top: 50%;
        transform: translate(10px, -50%);
        white-space: nowrap;
        background: rgba(0,0,0,0.75);
        color: #fff;
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 0.72rem;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.15s ease;
      }
      body.sidebar-collapse .user-panel:hover::after {
        opacity: 1;
      }

      .theme-purple .main-sidebar {
        background: linear-gradient(160deg, #5a2f8d, #8a42c9, #bb7fe8) !important;
        border-right: 3px solid #7f3fb4;
      }
      .theme-yellow .main-sidebar {
        background: linear-gradient(160deg, #c9920f, #f1c835, #ffe370) !important;
        border-right: 3px solid #d8a90f;
      }
      .theme-red .main-sidebar {
        background: linear-gradient(160deg, #ae1f1f, #e34141, #ff7373) !important;
        border-right: 3px solid #cc2b2b;
      }
      .theme-orange .main-sidebar {
        background: linear-gradient(160deg, #d36f1f, #fba534, #ffc67a) !important;
        border-right: 3px solid #de7b1e;
      }
      .theme-blue .main-sidebar {
        background: linear-gradient(160deg, #1555a8, #2b6fd5, #71a7ff) !important;
        border-right: 3px solid #1f5dbe;
      }
      .theme-green .main-sidebar {
        background: linear-gradient(160deg, #138049, #2cae6d, #74d1a4) !important;
        border-right: 3px solid #219c61;
      }

      .theme-purple .main-sidebar .nav-sidebar .nav-link.active,
      .theme-purple .main-sidebar .nav-sidebar .nav-link:hover {
        background: rgba(255, 255, 255, 0.25);
        color: #fff;
      }
      .theme-yellow .main-sidebar .nav-sidebar .nav-link.active,
      .theme-yellow .main-sidebar .nav-sidebar .nav-link:hover {
        background: rgba(0,0,0,0.13);
        color: #fff;
      }
      .theme-red .main-sidebar .nav-sidebar .nav-link.active,
      .theme-red .main-sidebar .nav-sidebar .nav-link:hover {
        background: rgba(0,0,0,0.13);
        color: #fff;
      }
      .theme-orange .main-sidebar .nav-sidebar .nav-link.active,
      .theme-orange .main-sidebar .nav-sidebar .nav-link:hover {
        background: rgba(0,0,0,0.12);
        color: #fff;
      }
      .theme-blue .main-sidebar .nav-sidebar .nav-link.active,
      .theme-blue .main-sidebar .nav-sidebar .nav-link:hover {
        background: rgba(0,0,0,0.12);
        color: #fff;
      }
      .theme-green .main-sidebar .nav-sidebar .nav-link.active,
      .theme-green .main-sidebar .nav-sidebar .nav-link:hover {
        background: rgba(255,255,255,0.22);
        color: #fff;
      }
      .theme-yellow .main-sidebar .nav-sidebar .nav-link.active,
      .theme-yellow .main-sidebar .nav-sidebar .nav-link:hover {
        background: rgba(0, 0, 0, 0.18);
        color: #fff;
      }
      .theme-red .main-sidebar .nav-sidebar .nav-link.active,
      .theme-red .main-sidebar .nav-sidebar .nav-link:hover {
        background: rgba(0, 0, 0, 0.18);
        color: #fff;
      }
      .theme-orange .main-sidebar .nav-sidebar .nav-link.active,
      .theme-orange .main-sidebar .nav-sidebar .nav-link:hover {
        background: rgba(0, 0, 0, 0.18);
        color: #fff;
      }
      .theme-blue .main-sidebar .nav-sidebar .nav-link.active,
      .theme-blue .main-sidebar .nav-sidebar .nav-link:hover {
        background: rgba(0, 0, 0, 0.18);
        color: #fff;
      }
      .theme-green .main-sidebar .nav-sidebar .nav-link.active,
      .theme-green .main-sidebar .nav-sidebar .nav-link:hover {
        background: rgba(255, 255, 255, 0.3);
        color: #fff;
      }

      .theme-purple .user-panel, .theme-yellow .user-panel, .theme-red .user-panel, .theme-orange .user-panel, .theme-blue .user-panel, .theme-green .user-panel {
        background: rgba(255, 255, 255, 0.12);
        border-color: rgba(255, 255, 255, 0.25);
      }

      </style>
      <script>
        (function() {
          var theme = '<?= htmlspecialchars($departmentThemeClass) ?>';
          if (theme && theme !== 'default') {
            document.documentElement.classList.add('theme-' + theme);
            document.body.classList.add('theme-' + theme);
            var nav = document.querySelector('.main-header.navbar');
            if (nav) {
              nav.classList.remove('navbar-admin-realistic', 'navbar-coordinator-realistic', 'navbar-secondary-realistic');
              nav.classList.add('navbar-' + theme + '-realistic');
            }
            var topFooter = document.querySelector('.main-footer');
            if (topFooter) {
              topFooter.classList.add('theme-' + theme);
            }
          }
        })();
      </script>
      <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center" data-user-tooltip="<?= htmlspecialchars($userName) ?>">
        <div class="image">
          <?php if (!empty($userAvatar) && file_exists(__DIR__ . '/../' . ltrim($userAvatar, '/'))): ?>
            <img src="/ojt1/<?= htmlspecialchars(ltrim($userAvatar, '/')) ?>" class="img-circle elevation-2" alt="User Image" style="width: 42px; height: 42px; object-fit: cover;">
          <?php else: ?>
            <div class="img-circle elevation-2 d-flex justify-content-center align-items-center" style="width: 42px; height: 42px; background: #6c757d; color: #fff; font-weight: 700;">
              <?= htmlspecialchars($userInitial) ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="info">
          <a href="#" id="sidebarUserName" class="d-block" style="font-weight: 700;"><?= htmlspecialchars($userName) ?></a>
          <small class="text-light" style="font-size: 0.82rem;">
            <?php if ($portalType === 'coordinator'): ?>
              Coordinator
            <?php else: ?>
              <?= ucfirst($portalType) ?> Account
            <?php endif; ?>
          </small>
          <?php if ($portalType === 'coordinator' && !empty($coordinatorDepartments)): ?>
            <div class="department-badges">
              <?php foreach ($coordinatorDepartments as $dept): ?>
                <span class="department-badge">
                  <i class="fas fa-building fa-xs"></i> <?= htmlspecialchars($dept) ?>
                </span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <div class="quick-links" id="sidebarQuickLinks">
            <?php if ($portalType === 'admin'): ?>
              <a href="/ojt1/admin/profile.php" class="btn btn-sm btn-outline-light" style="font-size:0.75rem;">Profile</a>
              <a href="/ojt1/admin/settings.php" class="btn btn-sm btn-outline-light" style="font-size:0.75rem;">Settings</a>
            <?php elseif ($portalType === 'coordinator'): ?>
              <a href="/ojt1/coodinator/profile.php" class="btn btn-sm btn-outline-light" style="font-size:0.75rem;">Profile</a>
              <a href="/ojt1/coodinator/settings.php" class="btn btn-sm btn-outline-light" style="font-size:0.75rem;">Settings</a>
            <?php elseif ($portalType === 'student'): ?>
              <a href="/ojt1/students/profile.php" class="btn btn-sm btn-outline-light" style="font-size:0.75rem;">Profile</a>
              <a href="/ojt1/students/settings.php" class="btn btn-sm btn-outline-light" style="font-size:0.75rem;">Settings</a>
            <?php else: ?>
              <a href="/ojt1/admin/profile.php" class="btn btn-sm btn-outline-light" style="font-size:0.75rem;">Profile</a>
              <a href="/ojt1/admin/settings.php" class="btn btn-sm btn-outline-light" style="font-size:0.75rem;">Settings</a>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <script>
        document.addEventListener('DOMContentLoaded', function() {
          var usernameToggle = document.getElementById('sidebarUserName');
          var quickLinks = document.getElementById('sidebarQuickLinks');
          if (usernameToggle && quickLinks) {
            // start hidden on initial page load
            quickLinks.classList.remove('d-none');
            quickLinks.classList.remove('visible');

            usernameToggle.addEventListener('click', function(e) {
              e.preventDefault();
              quickLinks.classList.toggle('visible');
            });

            // hide on outside click
            document.addEventListener('click', function(e) {
              if (!usernameToggle.contains(e.target) && !quickLinks.contains(e.target)) {
                quickLinks.classList.remove('visible');
              }
            });
          }
        });
      </script>

      <!-- Sidebar Menu -->
      <?php
      function active(string $path) {
        return strpos($_SERVER['PHP_SELF'], $path) !== false ? 'active' : '';
      }
      ?>

      <?php
      $today = date('Y-m-d');
      $dailyHours = 0;
      $studentId = !empty($_SESSION['student_id']) ? strtoupper($_SESSION['student_id']) : '';

      if ($studentId && !empty($_SESSION['attendance_entries'][$studentId]) && is_array($_SESSION['attendance_entries'][$studentId])) {
        foreach ($_SESSION['attendance_entries'][$studentId] as $a) {
          if (empty($a['out']) || empty($a['hours'])) {
            continue;
          }
          $entryDay = date('Y-m-d', $a['out']);
          if ($entryDay === $today) {
            $dailyHours += floatval($a['hours']);
          }
        }
      }

      if (!empty($_SESSION['clock_in'])) {
        $elapsed = (time() - $_SESSION['clock_in']) / 3600;
        $dailyHours += round($elapsed, 2);
      }
      ?>
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

          <?php if ($portalType === 'student') : ?>
            <li class="nav-item">
              <a href="/ojt1/students/dashboard.php" data-content="/ojt1/students/dashboard.php" class="nav-link <?php echo active('/students/dashboard.php'); ?>" title="Student dashboard overview">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard (Overview)</p>
              </a>
            </li>
            <li class="nav-header">Management</li>
            <li class="nav-item">
              <a href="/ojt1/students/logbook.php" data-content="/ojt1/students/logbook.php" class="nav-link <?php echo active('/students/logbook.php'); ?>" title="View and manage your OJT logbook">
                <i class="nav-icon fas fa-book"></i>
                <p>
                  My OJT Logbook
                  <span class="right badge badge-warning"><?= round($dailyHours, 2) ?>h today</span>
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="/ojt1/students/attendance.php" data-content="/ojt1/students/attendance.php" class="nav-link <?php echo active('/students/attendance.php'); ?>" title="View duty progress and remaining hours">
                <i class="nav-icon fas fa-chart-line"></i>
                <p>Progress</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="/ojt1/students/documentary.php" data-content="/ojt1/students/documentary.php" class="nav-link <?php echo active('/students/documentary.php'); ?>" title="Upload daily documentary/pictures">
                <i class="nav-icon fas fa-camera-retro"></i>
                <p>Daily Documentary</p>
              </a>
            </li>

          <?php elseif ($portalType === 'coordinator') : ?>
            <li class="nav-item">
              <a href="/ojt1/coodinator/coordinator.php" data-content="/ojt1/coodinator/coordinator.php" class="nav-link <?php echo active('/coodinator/coordinator.php'); ?>" title="Coordinator dashboard overview">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard (Overview)</p>
              </a>
            </li>
            <li class="nav-header">Management</li>
            <li class="nav-item">
              <a href="/ojt1/coodinator/students.php" data-content="/ojt1/coodinator/students.php" class="nav-link <?php echo active('/coodinator/students.php'); ?>" title="View and manage student tracking">
                <i class="nav-icon fas fa-users"></i>
                <p>Student Tracker</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="/ojt1/coodinator/office_deploy.php" data-content="/ojt1/coodinator/office_deploy.php" class="nav-link <?php echo active('/coodinator/office_deploy.php'); ?>" title="Search office locations and deploy students">
                <i class="nav-icon fas fa-map-marker-alt"></i>
                <p>Office Deployment</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="/ojt1/coodinator/reports.php" data-content="/ojt1/coodinator/reports.php" class="nav-link <?php echo active('/coodinator/reports.php'); ?>" title="View progress and reports">
                <i class="nav-icon fas fa-chart-bar"></i>
                <p>Reports</p>
              </a>
            </li>
          <?php elseif ($portalType === 'admin') : ?>
            <li class="nav-item">
              <a href="/ojt1/admin/admin.php" data-content="/ojt1/admin/admin.php" class="nav-link <?php echo active('/admin/admin.php'); ?>" title="Admin dashboard overview">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard (Overview)</p>
              </a>
            </li>
            <li class="nav-header">Management</li>
            <li class="nav-item">
              <a href="/ojt1/admin/coordinator_access.php" data-content="/ojt1/admin/coordinator_access.php" class="nav-link <?php echo active('/admin/coordinator_access.php'); ?>" title="Coordinator account + code generator">
                <i class="nav-icon fas fa-user-shield"></i>
                <p>Coordinator Access</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="/ojt1/admin/department_hours.php" data-content="/ojt1/admin/department_hours.php" class="nav-link <?php echo active('/admin/department_hours.php'); ?>" title="Set required OJT hours per department">
                <i class="nav-icon fas fa-clock"></i>
                <p>Department Hours</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="/ojt1/admin/faculty.php" data-content="/ojt1/admin/faculty.php" class="nav-link <?php echo active('/admin/faculty.php'); ?>" title="View and manage coordinator accounts">
                <i class="nav-icon fas fa-chalkboard-teacher"></i>
                <p>Coordinators</p>
              </a>
            </li>
          <?php else : ?>
            <li class="nav-item">
              <a href="/ojt1/students/dashboard.php" class="nav-link">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Student OJT Dashboard</p>
              </a>
            </li>
          <?php endif; ?>

          <li class="nav-header">Account</li>
          <li class="nav-item">
            <a href="/ojt1/logout.php" class="nav-link">
              <i class="nav-icon fas fa-sign-out-alt"></i>
              <p>Logout</p>
            </a>
          </li>

        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>