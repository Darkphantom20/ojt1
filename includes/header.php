<?php
date_default_timezone_set('Asia/Manila');
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

$rootPath = '/ojt1/';

if (!function_exists('getDepartmentThemeClass')) {
  function getDepartmentThemeClass($department = null) {
    $department = strtolower(trim($department ?? $_SESSION['student_department'] ?? $_SESSION['coordinator_department'] ?? ''));
    if ($department === '') {
      return 'default';
    }

    if (strpos($department, 'college of business') !== false
        || strpos($department, 'business administration') !== false
        || strpos($department, 'agribusiness') !== false
        || strpos($department, 'bsab') !== false
        || strpos($department, 'hospitality management') !== false
        || strpos($department, 'bshm') !== false
        || strpos($department, 'financial management') !== false
        || strpos($department, 'agriculture business') !== false
        || strpos($department, 'college of business & management') !== false) {
      return 'yellow';
    }

    if (strpos($department, 'college of computing') !== false
        || strpos($department, 'computer science') !== false
        || strpos($department, 'information systems') !== false
        || strpos($department, 'bscs') !== false
        || strpos($department, 'bsis') !== false) {
      return 'purple';
    }

    if (strpos($department, 'college of criminology') !== false
        || strpos($department, 'criminology') !== false
        || strpos($department, 'criminal justice') !== false
        || strpos($department, 'bscrim') !== false) {
      return 'red';
    }

    if (strpos($department, 'college of arts') !== false
        || strpos($department, 'baels') !== false
        || strpos($department, 'english language studies') !== false
        || strpos($department, 'arts') !== false) {
      return 'red';
    }

    if (strpos($department, 'college of education') !== false
        || strpos($department, 'beed') !== false
        || strpos($department, 'bped') !== false
        || strpos($department, 'bsed') !== false
        || strpos($department, 'elementary education') !== false
        || strpos($department, 'secondary education') !== false
        || strpos($department, 'physical education') !== false
        || strpos($department, 'education') !== false) {
      return 'blue';
    }

    if (strpos($department, 'college of agriculture') !== false
        || strpos($department, 'bsa') !== false
        || strpos($department, 'animal science') !== false
        || strpos($department, 'crop science') !== false
        || strpos($department, 'plant pathology') !== false
        || strpos($department, 'soil science') !== false
        || strpos($department, 'bsf') !== false
        || strpos($department, 'forestry') !== false
        || strpos($department, 'agriculture') !== false) {
      return 'green';
    }

    return 'default';
  }
}

$csp = "default-src 'self'; "
    . "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://maps.googleapis.com https://www.google.com https://cdnjs.cloudflare.com https://code.jquery.com; "
    . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://code.ionicframework.com; "
    . "font-src 'self' https://fonts.gstatic.com; "
    . "img-src 'self' data: https://images.unsplash.com https://maps.gstatic.com https://*.googleusercontent.com https://*.googleapis.com https://*.googlesyndication.com https://*.ggpht.com https://*.google.com; "
    . "connect-src 'self' https://maps.googleapis.com https://nominatim.openstreetmap.org https://*.googleapis.com; "
    . "frame-ancestors 'self'; "
    . "object-src 'none'; "
    . "base-uri 'self'; "
    . "form-action 'self';";

header('Content-Security-Policy: ' . $csp);
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: no-referrer-when-downgrade');

$portalTitle = 'Dashboard';
if (strpos($_SERVER['PHP_SELF'], '/students/') !== false) {
  $portalTitle = 'Student Portal';
} elseif (strpos($_SERVER['PHP_SELF'], '/coodinator/') !== false || strpos($_SERVER['PHP_SELF'], '/coordinator/') !== false) {
  $portalTitle = 'Coordinator Portal';
} elseif (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
  $portalTitle = 'Admin Portal';
}

$pageTitle = isset($pageTitle) ? $pageTitle : $portalTitle;

// Wallpaper image selection logic
$wallpaperPath = $rootPath . 'assets/img/bsis-thesis-wallpaper.png';
$wallpaperLocalFile = __DIR__ . '/../assets/img/bsis-thesis-wallpaper.png';
if (!file_exists($wallpaperLocalFile)) {
  $userWallpapers = glob(__DIR__ . '/../assets/img/users/*.png');
  if (!empty($userWallpapers)) {
    $wallpaperPath = $rootPath . 'assets/img/users/' . basename($userWallpapers[0]);
  } else {
    // fallback remote wallpaper if no local file exists
    $wallpaperPath = 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80';
  }
}

?>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta http-equiv="Content-Security-Policy" content="<?= htmlspecialchars($csp, ENT_QUOTES) ?>">
  <title><?= htmlspecialchars($pageTitle) ?> | OJT System</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?= $rootPath ?>plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="<?= $rootPath ?>plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="<?= $rootPath ?>plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="<?= $rootPath ?>plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?= $rootPath ?>dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="<?= $rootPath ?>plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="<?= $rootPath ?>plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="<?= $rootPath ?>plugins/summernote/summernote-bs4.min.css">
  <!-- Responsive stylesheet for all devices -->
  <link rel="stylesheet" href="<?= $rootPath ?>assets/css/responsive.css">
  <!-- Enhanced status boxes stylesheet -->
  <link rel="stylesheet" href="<?= $rootPath ?>assets/css/enhanced-status-boxes.css">
  <!-- Student page shared stylesheet -->
  <link rel="stylesheet" href="<?= $rootPath ?>assets/css/students.css">
  <!-- Dashboard wallpaper background -->
  <style>
    .content-wrapper {
      background-image: url('<?= htmlspecialchars($wallpaperPath, ENT_QUOTES) ?>');
      background-size: cover !important;
      background-position: center center !important;
      background-repeat: no-repeat !important;
      background-attachment: fixed !important;
      min-height: 100vh;
    }

    /* Optional: make the content panels translucent so wallpaper is visible */
    .content, .card {
      background: rgba(255, 255, 255, 0.85) !important;
    }

    /* Realistic sidebar theme for OJT monitoring system */
    .main-sidebar {
      background: linear-gradient(155deg, #0c4a8b 0%, #1089d5 55%, #1a9bff 100%) !important;
      border-right: 2px solid rgba(255, 255, 255, 0.2);
      box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.25);
    }

    .main-sidebar .brand-link,
    .main-sidebar .user-panel {
      background: rgba(255, 255, 255, 0.08); 
    }

    .main-sidebar .nav-header {
      color: #d7f3ff;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      border-bottom: 1px solid rgba(255, 255, 255, 0.18);
      margin-bottom: 0.35rem;
    }

    .main-sidebar .nav-sidebar .nav-link {
      color: #e8f6ff;
      border-radius: 0.35rem;
      margin-bottom: 0.2rem;
    }

    .main-sidebar .nav-sidebar .nav-link:hover {
      background: rgba(255, 255, 255, 0.17);
      color: #ffffff;
    }

    .main-sidebar .nav-sidebar .nav-item > .nav-link.active {
      background: linear-gradient(90deg, rgba(0, 145, 255, 0.65), rgba(0, 122, 230, 0.95));
      color: #ffffff;
      font-weight: 700;
      box-shadow: 0 0 12px rgba(0, 168, 255, 0.65);
    }

    .main-sidebar .nav-icon {
      color: #dcefff;
    }

    .main-sidebar .nav-link.active .nav-icon,
    .main-sidebar .nav-link:hover .nav-icon {
      color: #ffffff;
    }

    .main-sidebar .user-panel .info a {
      color: #ffffff;
      font-weight: 600;
    }

    /* Sync navbar and footer look with sidebar/wallpaper theme */
    .main-header.navbar {
      background: linear-gradient(145deg, rgba(9, 81, 146, 0.92), rgba(66, 154, 219, 0.85));
      border-bottom: 1px solid rgba(255, 255, 255, 0.25);
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.24);
    }

    .theme-purple .main-header.navbar {
      background: linear-gradient(145deg, rgba(107, 45, 156, 0.92), rgba(160, 85, 186, 0.85));
    }
    .theme-yellow .main-header.navbar {
      background: linear-gradient(145deg, rgba(215, 165, 15, 0.92), rgba(245, 190, 35, 0.85));
    }
    .theme-red .main-header.navbar {
      background: linear-gradient(145deg, rgba(168, 35, 35, 0.92), rgba(235, 77, 77, 0.85));
    }
    .theme-orange .main-header.navbar {
      background: linear-gradient(145deg, rgba(227, 111, 5, 0.92), rgba(249, 147, 48, 0.85));
    }
    .theme-blue .main-header.navbar {
      background: linear-gradient(145deg, rgba(20, 61, 138, 0.92), rgba(45, 95, 218, 0.85));
    }
    .theme-green .main-header.navbar {
      background: linear-gradient(145deg, rgba(18, 109, 62, 0.92), rgba(45, 173, 103, 0.85));
    }

    .main-header.navbar .nav-link,
    .main-header.navbar .brand-link {
      color: #e3f6ff;
    }

    .main-header.navbar .nav-link:hover,
    .main-header.navbar .nav-link.active {
      color: #ffffff;
      background: rgba(255, 255, 255, 0.15);
      border-radius: 0.35rem;
    }

    .main-footer {
      background: linear-gradient(40deg, rgba(17, 54, 103, 0.92), rgba(22, 95, 160, 0.86));
      color: #f0faff;
      border-top: 1px solid rgba(255, 255, 255, 0.18);
      box-shadow: inset 0 8px 15px rgba(0, 0, 0, 0.18);
    }
    .theme-purple .main-footer {
      background: linear-gradient(40deg, rgba(92,28,114,0.92), rgba(147,80,181,0.86));
      color: #f8ecff;
    }
    .theme-yellow .main-footer {
      background: linear-gradient(40deg, rgba(193,150,23,0.92), rgba(247,191,41,0.86));
      color: #25230c;
    }
    .theme-red .main-footer {
      background: linear-gradient(40deg, rgba(157,26,26,0.92), rgba(233,78,78,0.86));
      color: #fff2f2;
    }
    .theme-orange .main-footer {
      background: linear-gradient(40deg, rgba(217,105,17,0.92), rgba(255,158,76,0.86));
      color: #fff6eb;
    }
    .theme-blue .main-footer {
      background: linear-gradient(40deg, rgba(21, 60, 131,0.92), rgba(44, 96, 212,0.86));
      color: #f0f7ff;
    }
    .theme-green .main-footer {
      background: linear-gradient(40deg, rgba(20,100,59,0.92), rgba(36,163,94,0.86));
      color: #effff2;
    }

    .main-footer a {
      color: #def0ff;
    }

    .main-footer a:hover {
      color: #ffffff;
    }

    /* Realistic status cards (dashboard summary panels) */
    .small-box {
      border-radius: 0.8rem !important;
      overflow: hidden;
      box-shadow: 0 12px 24px rgba(0,0,0,0.16);
      border: 1px solid rgba(255, 255, 255, 0.24);
      background: linear-gradient(130deg, rgba(14, 65, 121, 0.95), rgba(11, 128, 215, 0.9));
      color: #edf4ff;
      transition: transform .22s ease, box-shadow .22s ease;
    }

    .small-box:hover {
      transform: translateY(-4px);
      box-shadow: 0 18px 28px rgba(0,0,0,0.22);
    }

    .small-box .inner h3,
    .small-box .inner p {
      color: #fff;
      text-shadow: 0 2px 6px rgba(0,0,0,0.35);
    }

    .small-box .icon {
      color: rgba(255, 255, 255, 0.75);
      text-shadow: 0 1px 2px rgba(0,0,0,0.3);
      font-size: 3.3rem !important;
    }

    .small-box .icon i {
      margin-top: 2px;
    }

    .small-box .small-box-footer {
      color: rgba(255,255,255,0.85) !important;
      background: rgba(0,0,0,0.16);
      text-shadow: none;
      border-top: 1px solid rgba(255,255,255,0.24);
    }

    .small-box .small-box-footer:hover {
      color: #ffffff !important;
      background: rgba(0,0,0,0.22);
    }

    /* Global content header style across all portals */
    .content-header {
      padding: 1rem 1.25rem;
      margin-bottom: 0.95rem;
      background: rgba(255,255,255,0.78);
      border-radius: 0.5rem;
      box-shadow: 0 6px 16px rgba(0,0,0,0.09);
      border: 1px solid rgba(222, 230, 238, 0.65);
    }

    /* Modern breadcrumb style for this system */
    .content-header .breadcrumb {
      background: rgba(255, 255, 255, 0.35);
      border: 1px solid rgba(0,0,0,0.08);
      border-radius: 999px;
      padding: 0.25rem 0.75rem;
      box-shadow: inset 0 0 0 1px rgba(255,255,255,0.75), 0 1px 6px rgba(0,0,0,0.08);
      backdrop-filter: blur(10px);
      display: inline-flex;
      align-items: center;
      font-size: 0.86rem;
      font-weight: 600;
      color: #2b4756;
    }

    .content-header .breadcrumb .breadcrumb-item + .breadcrumb-item::before {
      content: '\276f';
      color: rgba(48, 63, 84, 0.7);
      margin: 0 0.45rem;
      font-size: 0.85rem;
      vertical-align: middle;
    }

    .content-header .breadcrumb .breadcrumb-item a {
      color: #1e3d75;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .content-header .breadcrumb .breadcrumb-item.active {
      color: #2a4058;
    }

    .content-header h1 {
      font-size: 1.75rem;
      font-weight: 800;
      color: #1f3f75;
      letter-spacing: 0.02em;
      margin-bottom: 0.2rem;
      display: inline-flex;
      align-items: center;
    }

    /* Theme buttons: apply department style to most buttons (retain login/logout semantics) */
    .theme-purple .btn:not(.btn-login):not(.btn-logout):not(.btn-primary) {
      background: linear-gradient(135deg, #6d33ae, #954dca);
      border-color: #8440c5;
      color: #fff;
    }
    .theme-yellow .btn:not(.btn-login):not(.btn-logout):not(.btn-primary) {
      background: linear-gradient(135deg, #e4a70f, #f6c23e);
      border-color: #d59f1a;
      color: #2b2b0c;
    }
    .theme-red .btn:not(.btn-login):not(.btn-logout):not(.btn-primary) {
      background: linear-gradient(135deg, #cc2f2f, #ee6565);
      border-color: #c44545;
      color: #fff;
    }
    .theme-orange .btn:not(.btn-login):not(.btn-logout):not(.btn-primary) {
      background: linear-gradient(135deg, #d46a0a, #ffb23f);
      border-color: #dc870c;
      color: #362f0c;
    }
    .theme-blue .btn:not(.btn-login):not(.btn-logout):not(.btn-primary) {
      background: linear-gradient(135deg, #1e45a5, #4a8fea);
      border-color: #386fc7;
      color: #fff;
    }
    .theme-green .btn:not(.btn-login):not(.btn-logout):not(.btn-primary) {
      background: linear-gradient(135deg, #1f7b4f, #3ac98a);
      border-color: #2c9b66;
      color: #fff;
    }

    .theme-purple .btn:not(.btn-success):not(.btn-danger):not(.btn-primary):hover,
    .theme-yellow .btn:not(.btn-success):not(.btn-danger):not(.btn-primary):hover,
    .theme-red .btn:not(.btn-success):not(.btn-danger):not(.btn-primary):hover,
    .theme-orange .btn:not(.btn-success):not(.btn-danger):not(.btn-primary):hover,
    .theme-blue .btn:not(.btn-success):not(.btn-danger):not(.btn-primary):hover,
    .theme-green .btn:not(.btn-success):not(.btn-danger):not(.btn-primary):hover {
      opacity: 0.92;
      transform: translateY(-1px);
    }

    .content-header h1::before {
      content: '\1F4CB';
      margin-right: 0.65rem;
      font-size: 1.35rem;
      transform: translateY(1px);
      opacity: 0.82;
    }

    .content-header h1 small {
      font-size: 0.94rem;
      font-weight: 500;
      color: #4e5f8f;
      margin-left: 0.7rem;
      opacity: 0.82;
      letter-spacing: 0.01em;
    }
    .theme-purple .content-header { background: rgba(134, 77, 193, 0.18); border-color: rgba(144, 80, 200, 0.4); }
    .theme-yellow .content-header { background: rgba(244, 190, 47, 0.17); border-color: rgba(228, 178, 13, 0.4); }
    .theme-red .content-header { background: rgba(212, 74, 74, 0.17); border-color: rgba(195, 55, 55, 0.4); }
    .theme-orange .content-header { background: rgba(245, 146, 55, 0.17); border-color: rgba(234, 118, 30, 0.4); }
    .theme-blue .content-header { background: rgba(57, 107, 210, 0.17); border-color: rgba(45, 85, 175, 0.4); }
    .theme-green .content-header { background: rgba(45, 152, 98, 0.17); border-color: rgba(32, 124, 82, 0.4); }

    /* Department-based card header colors */
    .theme-purple .card-header { background: linear-gradient(145deg, rgba(110, 63, 168, 0.95), rgba(143, 78, 207, 0.9)); color: #fff; }
    .theme-yellow .card-header { background: linear-gradient(145deg, rgba(221, 165, 23, 0.95), rgba(255, 200, 50, 0.9)); color: #2d2d1a; }
    .theme-red .card-header { background: linear-gradient(145deg, rgba(181, 35, 35, 0.95), rgba(232, 70, 70, 0.95)); color: #fff; }
    .theme-orange .card-header { background: linear-gradient(145deg, rgba(226, 100, 24, 0.95), rgba(255, 152, 56, 0.95)); color: #38300a; }
    .theme-blue .card-header { background: linear-gradient(145deg, rgba(17, 56, 133, 0.95), rgba(57, 110, 209, 0.95)); color: #fff; }
    .theme-green .card-header, .theme-green .dynamic-card-header { background: linear-gradient(145deg, rgba(20, 110, 63, 0.95), rgba(45, 170, 101, 0.95)); color: #fff; }

    .theme-purple .card.card-outline, .theme-purple .dynamic-card-header,
    .theme-yellow .card.card-outline, .theme-yellow .dynamic-card-header,
    .theme-red .card.card-outline, .theme-red .dynamic-card-header,
    .theme-orange .card.card-outline, .theme-orange .dynamic-card-header,
    .theme-blue .card.card-outline, .theme-blue .dynamic-card-header,
    .theme-green .card.card-outline, .theme-green .dynamic-card-header {
      border: 1px solid rgba(255,255,255,0.24);
      box-shadow: 0 8px 24px rgba(0,0,0,0.13);
    }

    /* compact button and card spacing improvements */
    .content .btn,
    .main-sidebar .btn,
    .modal-content .btn {
      padding: 0.35rem 0.7rem;
      font-size: 0.82rem;
      border-radius: 0.45rem;
    }

    .card {
      margin-bottom: 0.8rem;
      border-radius: 0.75rem;
      overflow: hidden;
    }

    .card-body {
      padding: 1rem;
    }

    .content-header {
      margin-bottom: 0.85rem;
      padding: 0.85rem 1rem;
    }

    .small-box > .inner h3,
    .small-box > .inner p {
      font-size: 1.4rem;
      line-height: 1.2;
    }

    .small-box > .inner .progress, .small-box > .inner .small {
      font-size: 1rem;
    }

    .small-box .icon {
      right: 0.7rem;
      font-size: 2.2rem !important;
    }

    .content-wrapper .content {
      padding: 0.7rem 0.9rem;
    }

    .form-control,
    .custom-select,
    .input-group-text {
      height: 36px;
      padding: 0.3rem 0.6rem;
      font-size: 0.88rem;
      border-radius: 0.5rem;
    }

    .theme-purple .small-box { background: linear-gradient(135deg, rgba(132,63,176,0.95), rgba(168,79,215,0.85)); border-color: rgba(163, 95, 213, 0.5); }
    .theme-yellow .small-box { background: linear-gradient(135deg, rgba(239,179,31,0.95), rgba(249,206,49,0.85)); border-color: rgba(231, 170, 26, 0.6); }
    .theme-red .small-box { background: linear-gradient(135deg, rgba(185,34,34,0.95), rgba(237,87,87,0.85)); border-color: rgba(212, 62, 62, 0.6); }
    .theme-orange .small-box { background: linear-gradient(135deg, rgba(233,102,24,0.95), rgba(255,154,65,0.85)); border-color: rgba(236, 114, 34, 0.6); }
    .theme-blue .small-box { background: linear-gradient(135deg, rgba(24,70,161,0.95), rgba(81,145,225,0.85)); border-color: rgba(63, 112, 198, 0.6); }
    .theme-green .small-box { background: linear-gradient(135deg, rgba(23,147,86,0.95), rgba(86,206,139,0.85)); border-color: rgba(45, 170, 98, 0.6); }

    .theme-purple .content { background: rgba(241, 226, 255, 0.45) !important; }
    .theme-yellow .content { background: rgba(254, 245, 209, 0.45) !important; }
    .theme-red .content { background: rgba(255, 220, 220, 0.45) !important; }
    .theme-orange .content { background: rgba(255, 235, 206, 0.45) !important; }
    .theme-blue .content { background: rgba(217, 235, 255, 0.45) !important; }
    .theme-green .content { background: rgba(221, 252, 235, 0.45) !important; }

    .content-header p {
      color: #5a6c86;
      margin-bottom: 0;
    }

    .main-sidebar .nav-sidebar .nav-item:not(:last-child) {
      margin-bottom: .45rem;
    }

    .main-sidebar .nav-sidebar .nav-link {
      transition: all .25s ease;
    }

    .main-sidebar .nav-sidebar .nav-link i {
      width: 22px;
      text-align: center;
    }

    .main-sidebar .nav-sidebar .nav-link.active,
    .main-sidebar .nav-sidebar .nav-link:hover {
      transform: translateX(2px);
    }

    .main-footer {
      padding: 1rem 1.25rem;
      font-size: .88rem;
      text-align: center;
    }

    .main-footer .float-right {
      color: rgba(255,255,255,0.85);
      letter-spacing: .01em;
    }

    /* Admin sidebar details and content panel style */
    .content-wrapper .content {
      background: rgba(255,255,255,0.90);
      border-radius: .65rem;
      box-shadow: 0 14px 28px rgba(0,0,0,0.11);
      margin: 0.15rem 0;
      padding: 1rem;
    }

    /* Student portal: consistent scrolling on content pages */
    <?php if (strpos($_SERVER['PHP_SELF'], '/students/') !== false): ?>
    .content-wrapper {
      max-height: none !important;
      overflow-y: auto !important;
      overflow-x: hidden !important;
    }

    .content-wrapper .content {
      max-height: none !important;
      overflow: visible !important;
    }
    <?php endif; ?>

    /* ============================================
       RESPONSIVE DESIGN - TABLET (768px and up)
       ============================================ */
    @media (max-width: 991.98px) {
      /* Sidebar adjustments for tablets */
      .content-header h1 {
        font-size: 1.5rem;
      }

      .content-header .breadcrumb {
        font-size: 0.75rem;
        padding: 0.2rem 0.5rem;
      }

      .small-box .icon {
        font-size: 1.8rem !important;
      }

      .small-box > .inner h3 {
        font-size: 1.2rem;
      }

      /* Better column distribution on tablets */
      .row .col-md-6 {
        margin-bottom: 1rem;
      }
    }

    /* ============================================
       RESPONSIVE DESIGN - MOBILE (576px and down)
       ============================================ */
    @media (max-width: 575.98px) {
      /* Reduce all padding and margins for mobile */
      body {
        font-size: 13px;
      }

      .main-header.navbar {
        height: auto;
        min-height: 50px;
      }

      .main-header .navbar-nav {
        flex-wrap: wrap;
      }

      /* Content wrapper padding */
      .content-wrapper {
        margin-left: 0 !important;
        padding-top: 0.5rem;
      }

      .content {
        padding: 0.5rem !important;
      }

      /* Page header optimization */
      .content-header {
        padding: 0.75rem 0.85rem;
        margin-bottom: 0.75rem;
      }

      .content-header h1 {
        font-size: 1.25rem;
        margin-bottom: 0.5rem;
      }

      .content-header h1::before {
        font-size: 1rem;
        margin-right: 0.35rem;
      }

      .content-header .row {
        flex-direction: column;
      }

      .content-header .col-sm-6 {
        margin-bottom: 0.5rem;
      }

      .breadcrumb {
        font-size: 0.7rem !important;
        padding: 0.15rem 0.4rem !important;
      }

      /* Card adjustments */
      .card {
        margin-bottom: 0.6rem;
        border-radius: 0.5rem;
      }

      .card-body {
        padding: 0.75rem;
      }

      .card-header {
        padding: 0.6rem 0.75rem;
      }

      /* Small box cards */
      .small-box {
        margin-bottom: 0.75rem;
        border-radius: 0.6rem;
      }

      .small-box > .inner {
        padding: 0.75rem;
      }

      .small-box > .inner h3 {
        font-size: 1.1rem;
        margin: 0 0 0.25rem 0;
      }

      .small-box > .inner p {
        font-size: 0.85rem;
        margin: 0;
      }

      .small-box .icon {
        position: absolute;
        right: 0.5rem;
        top: 0.5rem;
        font-size: 1.5rem !important;
        opacity: 0.8;
      }

      .small-box-footer {
        padding: 0.5rem !important;
        font-size: 0.75rem !important;
      }

      /* Buttons */
      .btn {
        padding: 0.3rem 0.5rem !important;
        font-size: 0.75rem !important;
        border-radius: 0.35rem !important;
        white-space: normal;
      }

      .btn-block {
        width: 100%;
      }

      /* Forms */
      .form-group {
        margin-bottom: 0.75rem;
      }

      .form-control,
      .custom-select,
      .input-group-text {
        height: 32px;
        padding: 0.25rem 0.5rem;
        font-size: 0.85rem;
        border-radius: 0.35rem;
      }

      .input-group {
        margin-bottom: 0.5rem;
      }

      /* Tables - responsive design */
      .table {
        font-size: 0.85rem;
        margin-bottom: 0;
      }

      .table th,
      .table td {
        padding: 0.5rem 0.35rem;
        white-space: nowrap;
      }

      .table-responsive {
        font-size: 0.85rem;
      }

      .table-responsive > .table {
        margin-bottom: 0;
      }

      /* Hide non-essential columns on mobile */
      .table .col-actions {
        min-width: 70px;
      }

      /* Modal adjustments */
      .modal-content {
        border-radius: 0.5rem;
      }

      .modal-header {
        padding: 0.75rem;
      }

      .modal-body {
        padding: 0.75rem;
      }

      .modal-footer {
        padding: 0.6rem;
      }

      .modal-title {
        font-size: 1rem;
      }

      /* Alert boxes */
      .alert {
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
        margin-bottom: 0.75rem;
      }

      /* Sidebar - collapse on mobile */
      .sidebar-mini.sidebar-closed .content-wrapper {
        margin-left: 0 !important;
      }

      /* Footer */
      .main-footer {
        padding: 0.75rem !important;
        font-size: 0.8rem !important;
      }

      /* Row spacing */
      .row {
        margin-left: -0.4rem;
        margin-right: -0.4rem;
      }

      .row > [class*="col-"] {
        padding-left: 0.4rem;
        padding-right: 0.4rem;
        margin-bottom: 0.75rem;
      }

      /* Ensure full width on single column layouts */
      .col-lg-3,
      .col-lg-4,
      .col-lg-6,
      .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
      }

      /* Text adjustments */
      h1 { font-size: 1.3rem !important; }
      h2 { font-size: 1.1rem !important; }
      h3 { font-size: 1rem !important; }
      h4 { font-size: 0.95rem !important; }
      h5 { font-size: 0.9rem !important; }
      h6 { font-size: 0.85rem !important; }

      /* List adjustments */
      ul, ol {
        padding-left: 1.2rem;
        margin-bottom: 0.75rem;
      }

      li {
        margin-bottom: 0.25rem;
      }

      /* Badge adjustments */
      .badge {
        padding: 0.3rem 0.5rem;
        font-size: 0.7rem;
      }

      /* Icon adjustments */
      .nav-icon {
        font-size: 1rem;
      }
    }

    /* ============================================
       RESPONSIVE DESIGN - EXTRA SMALL (< 376px)
       ============================================ */
    @media (max-width: 375px) {
      body {
        font-size: 12px;
      }

      .content-header h1 {
        font-size: 1.1rem;
      }

      .btn {
        padding: 0.25rem 0.4rem !important;
        font-size: 0.7rem !important;
      }

      .form-control,
      .custom-select {
        height: 30px;
        font-size: 0.8rem;
      }

      .small-box > .inner h3 {
        font-size: 1rem;
      }

      .table th,
      .table td {
        padding: 0.35rem 0.25rem;
        font-size: 0.75rem;
      }
    }

    /* ============================================
       LANDSCAPE MODE OPTIMIZATIONS
       ============================================ */
    @media (max-height: 500px) and (orientation: landscape) {
      .main-header.navbar {
        min-height: 40px;
      }

      .content-wrapper {
        padding-top: 0.25rem;
      }

      .small-box {
        margin-bottom: 0.5rem;
      }

      .card {
        margin-bottom: 0.5rem;
      }
    }

    /* ============================================
       TABLE RESPONSIVENESS FIX
       ============================================ */
    .table-responsive {
      display: block;
      width: 100%;
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }

    .table-responsive > .table {
      margin-bottom: 0;
    }

    @media (max-width: 767.98px) {
      .table-responsive {
        width: 100%;
      }

      .table thead {
        font-size: 0.85rem;
      }

      .table tbody {
        font-size: 0.8rem;
      }
    }

    /* ============================================
       NAVIGATION BAR RESPONSIVE FIX
       ============================================ */
    .main-header .nav-link {
      padding: 0.5rem 0.75rem;
    }

    @media (max-width: 767.98px) {
      .main-header .nav-link {
        padding: 0.35rem 0.5rem;
        font-size: 0.85rem;
      }

      .navbar-nav {
        flex-direction: row;
        gap: 0.25rem;
      }
    }

    /* ============================================
       IMPROVE TOUCH TARGETS ON MOBILE
       ============================================ */
    @media (max-width: 575.98px) {
      button,
      a.btn,
      .btn {
        min-height: 44px;
        min-width: 44px;
        padding: 0.5rem 0.75rem !important;
      }

      input[type="checkbox"],
      input[type="radio"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
      }

      label {
        cursor: pointer;
      }

      /* Ensure clickable elements are touch-friendly */
      .nav-link,
      .list-group-item,
      .dropdown-item {
        min-height: 40px;
        display: flex;
        align-items: center;
      }
    }

    /* ============================================
       PRINT OPTIMIZATION
       ============================================ */
    @media print {
      .main-header,
      .main-sidebar,
      .main-footer,
      .sidebar-mini.sidebar-closed-toggled .sidebar-mini-toggler {
        display: none !important;
      }

      .content-wrapper {
        margin-left: 0 !important;
        padding: 1rem;
      }

      body {
        background-color: white !important;
      }

      .card {
        box-shadow: none;
        border: 1px solid #ddd;
      }
    }
  </style>

  <?php
  if (!function_exists('getDepartmentsForCollege')) {
    function getDepartmentsForCollege(string $college): array {
      $college = strtolower(trim($college));
      switch ($college) {
        case 'education':
        case 'college of education':
          return [
            'Bachelor of Elementary Education (BEEd)',
            'Bachelor of Physical Education (BPEd)',
            'Bachelor of Secondary Education (BSEd) - Major in English',
            'Bachelor of Secondary Education (BSEd) - Major in Filipino',
            'Bachelor of Secondary Education (BSEd) - Major in Mathematics',
            'Bachelor of Secondary Education (BSEd) - Major in Social Studies',
          ];
        case 'college of arts':
        case 'arts':
          return ['Bachelor of Arts in English Language Studies (BAELS)'];
        case 'college of agriculture & forestry':
        case 'college of agriculture':
        case 'agriculture & forestry':
        case 'agriculture':
          return [
            'Bachelor of Science in Agriculture (BSA) - Major in Animal Science',
            'Bachelor of Science in Agriculture (BSA) - Major in Crop Science',
            'Bachelor of Science in Agriculture (BSA) - Major in Plant Pathology',
            'Bachelor of Science in Agriculture (BSA) - Major in Soil Science',
            'Bachelor of Science in Forestry (BSF)',
          ];
        case 'college of business & management':
        case 'college of business':
        case 'business & management':
        case 'business':
          return [
            'Bachelor of Science in Agribusiness (BSAB)',
            'Bachelor of Science in Business Administration (BSBA) - Major in Financial Management',
            'Bachelor of Science in Hospitality Management (BSHM)',
          ];
        case 'college of computing studies':
        case 'college of computing':
        case 'computing':
          return ['Bachelor of Science in Computer Science (BSCS)', 'Bachelor of Science in Information Systems (BSIS)'];
        case 'college of criminology':
        case 'criminology':
          return ['Bachelor of Science in Criminology (BSCrim)'];
        default:
          if (strpos($college, 'computing') !== false) {
            return ['Bachelor of Science in Computer Science (BSCS)', 'Bachelor of Science in Information Systems (BSIS)'];
          }
          if (strpos($college, 'business') !== false) {
            return [
              'Bachelor of Science in Agribusiness (BSAB)',
              'Bachelor of Science in Business Administration (BSBA) - Major in Financial Management',
              'Bachelor of Science in Hospitality Management (BSHM)',
            ];
          }
          if (strpos($college, 'criminology') !== false) {
            return ['Bachelor of Science in Criminology (BSCrim)'];
          }
          if (strpos($college, 'arts') !== false) {
            return ['Bachelor of Arts in English Language Studies (BAELS)'];
          }
          if (strpos($college, 'education') !== false) {
            return [
              'Bachelor of Elementary Education (BEEd)',
              'Bachelor of Physical Education (BPEd)',
              'Bachelor of Secondary Education (BSEd) - Major in English',
              'Bachelor of Secondary Education (BSEd) - Major in Filipino',
              'Bachelor of Secondary Education (BSEd) - Major in Mathematics',
              'Bachelor of Secondary Education (BSEd) - Major in Social Studies',
            ];
          }
          if (strpos($college, 'agriculture') !== false || strpos($college, 'forestry') !== false) {
            return [
              'Bachelor of Science in Agriculture (BSA) - Major in Animal Science',
              'Bachelor of Science in Agriculture (BSA) - Major in Crop Science',
              'Bachelor of Science in Agriculture (BSA) - Major in Plant Pathology',
              'Bachelor of Science in Agriculture (BSA) - Major in Soil Science',
              'Bachelor of Science in Forestry (BSF)',
            ];
          }
          return [];
      }
    }
  }

  if (!function_exists('isDepartmentInCollege')) {
    function isDepartmentInCollege(string $department, string $college): bool {
      $department = strtolower(trim($department));
      $allowedDepartments = array_map('strtolower', getDepartmentsForCollege($college));
      return in_array($department, $allowedDepartments, true);
    }
  }

  if (!function_exists('getDepartmentStatusBoxClass')) {
    function getDepartmentStatusBoxClass(string $department) {
      $department = strtolower(trim($department));
      if (strpos($department, 'college of computing') !== false || strpos($department, 'information systems') !== false || strpos($department, 'computer science') !== false) {
        return 'bg-dept-info-systems';
      }
      if (strpos($department, 'college of business') !== false || strpos($department, 'business administration') !== false || strpos($department, 'human resource') !== false || strpos($department, 'agriculture business') !== false) {
        return 'bg-dept-business';
      }
      if (strpos($department, 'college of criminology') !== false || strpos($department, 'criminal justice') !== false || strpos($department, 'criminology') !== false) {
        return 'bg-dept-criminal-justice';
      }
      if (strpos($department, 'forestry') !== false) {
        return 'bg-dept-forestry';
      }
      if (strpos($department, 'college of agriculture') !== false || strpos($department, 'biosystem engineering') !== false || strpos($department, 'agriculture') !== false) {
        return 'bg-dept-biosystem';
      }
      if (strpos($department, 'college of arts') !== false) {
        return 'bg-dept-criminal-justice';
      }
      if (strpos($department, 'education') !== false) {
        return 'bg-dept-education';
      }
      return 'bg-dept-default';
    }
  }
  ?>
</head>