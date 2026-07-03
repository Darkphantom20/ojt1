<?php

$rootPath = $rootPath ?? '/ojt1/';

$notificationItems = [];
$notificationCount = 0;
$notificationPage = $rootPath . 'admin/coordinator_access.php';

if (!isset($conn)) {
    @include_once __DIR__ . '/../dbconnection.php';
}

if (isset($conn)) {
    $pendingResult = $conn->query("SELECT COUNT(*) AS cnt FROM coordinator_accounts WHERE status = 'unused'");
    if ($pendingResult) {
        $pendingRow = $pendingResult->fetch_assoc();
        $pendingCoordinatorCount = (int)($pendingRow['cnt'] ?? 0);
    } else {
        $pendingCoordinatorCount = 0;
    }

    $newRegisterResult = $conn->query("SELECT full_name, access_code, status, created_at FROM coordinator_accounts ORDER BY created_at DESC LIMIT 3");
    if ($newRegisterResult) {
        $rows = [];
        while ($row = $newRegisterResult->fetch_assoc()) {
            $rows[] = $row;
        }
        foreach ($rows as $row) {
            $notificationItems[] = [
                'icon' => 'fas fa-user-shield text-info',
                'title' => $row['full_name'] . ' (' . $row['status'] . ')',
                'description' => 'Code: ' . $row['access_code'],
                'time' => date('g:i A', strtotime($row['created_at'])) . ' today',
            ];
        }
    }

    if ($pendingCoordinatorCount > 0) {
        array_unshift($notificationItems, [
            'icon' => 'fas fa-user-check text-warning',
            'title' => $pendingCoordinatorCount . ' pending coordinator account(s)',
            'description' => 'Review the latest coordinator signups.',
            'time' => 'Now',
        ]);
    }

    $notificationCount = count($notificationItems);
    if ($notificationCount === 0) {
        $notificationItems[] = [
            'icon' => 'fas fa-check text-success',
            'title' => 'No new notifications',
            'description' => 'Everything is running smoothly.',
            'time' => '',
        ];
        $notificationCount = 1;
    }
} else {
    $notificationItems[] = [
        'icon' => 'fas fa-exclamation-circle text-secondary',
        'title' => 'Notifications currently unavailable',
        'description' => 'Database connection could not be established.',
        'time' => '',
    ];
    $notificationCount = 1;
}

$currentScript = basename($_SERVER['PHP_SELF']);
$isCoordinatorAccess = $currentScript === 'coordinator_access.php';
$isFacultyDirectory = $currentScript === 'faculty.php';
$isStudentRecords = $currentScript === 'students.php';
?>

<style>
  .navbar-admin-realistic {
    background: linear-gradient(140deg, #134e81 0%, #1e75b9 52%, #2da3ff 100%);
    box-shadow: 0 3px 18px rgba(0,0,0,0.25);
    border-bottom: 2px solid rgba(255,255,255,0.14);
    min-height: 60px;
  }
  .navbar-coordinator-realistic {
    background: linear-gradient(140deg, #6d2e91 0%, #9945cc 52%, #bf6ef5 100%);
    box-shadow: 0 3px 18px rgba(0,0,0,0.25);
    border-bottom: 2px solid rgba(255,255,255,0.14);
    min-height: 60px;
  }
  .navbar-secondary-realistic {
    background: linear-gradient(140deg, #1c5b2a 0%, #26a471 52%, #58d9a7 100%);
    box-shadow: 0 3px 18px rgba(0,0,0,0.25);
    border-bottom: 2px solid rgba(255,255,255,0.14);
    min-height: 60px;
  }
  .navbar-admin-realistic .nav-link {
    color: #ebf3ff;
    font-weight: 600;
  }
  .navbar-admin-realistic .nav-link:hover {
    color: #ffffff;
    text-shadow: 0 0 3px rgba(0,0,0,0.42);
  }
  .navbar-admin-realistic .navbar-badge {
    font-size: 0.75rem;
    min-width: 1.5em;
    height: 1.5em;
    line-height: 1.5em;
  }
  .navbar-admin-realistic .dropdown-menu {
    border-radius: 0.55rem;
    border: 1px solid rgba(22, 100, 170, 0.35);
    box-shadow: 0 10px 22px rgba(0,0,0,0.16);
  }
  .navbar-admin-realistic .dropdown-item {
    border-radius: 0.28rem;
  }
  .navbar-admin-realistic .dropdown-item:hover {
    background: rgba(28,126,189,0.12);
  }
</style>

<nav class="main-header navbar navbar-expand <?= $isCoordinatorAccess ? 'navbar-coordinator-realistic' : ($isFacultyDirectory || $isStudentRecords ? 'navbar-secondary-realistic' : 'navbar-admin-realistic') ?>">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <?php if ($isCoordinatorAccess): ?>
          <a href="<?= $rootPath ?>admin/coordinator_access.php" class="nav-link"><i class="fas fa-user-cog mr-1"></i> Coordinator Access</a>
        <?php elseif ($isFacultyDirectory): ?>
          <a href="<?= $rootPath ?>admin/faculty.php" class="nav-link"><i class="fas fa-chalkboard-teacher mr-1"></i> Faculty Directory</a>
        <?php else: ?>
          <a href="<?= $rootPath ?>admin/admin.php" class="nav-link"><i class="fas fa-tachometer-alt mr-1"></i> Admin Dashboard</a>
        <?php endif; ?>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      

      <li class="nav-item">
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
    </ul>
  </nav>