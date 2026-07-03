<!DOCTYPE html>
<html lang="en">
<?php
session_start();

if (empty($_SESSION['coordinator_logged_in']) || $_SESSION['coordinator_logged_in'] !== true) {
    header('Location: /ojt1/index.php?coordinator_login_failed=1');
    exit;
}

$pageTitle = 'Coordinator Profile';
include __DIR__ . '/../includes/header.php';

$profileMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName = trim($_POST['user_name'] ?? '');
    $newEmail = trim($_POST['user_email'] ?? '');

    if ($newName !== '') {
        $_SESSION['coordinator_name'] = $newName;
        $_SESSION['user_name'] = $newName;
    }
    if ($newEmail !== '') {
        $_SESSION['coordinator_email'] = $newEmail;
        $_SESSION['user_email'] = $newEmail;
    }

    if (isset($_FILES['user_avatar']) && $_FILES['user_avatar']['error'] === UPLOAD_ERR_OK) {
        $forbiddenExtensions = ['php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'js', 'sh', 'bat', 'cmd', 'com', 'jar', 'vbs', 'jsp', 'asp', 'aspx'];
        $allowedMimeTypes = ['image/png' => 'png', 'image/jpeg' => 'jpg'];
        $fileName = basename($_FILES['user_avatar']['name']);
        $fileInfo = getimagesize($_FILES['user_avatar']['tmp_name']);
        $type = $fileInfo['mime'] ?? mime_content_type($_FILES['user_avatar']['tmp_name']);
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $fileSegments = array_filter(array_map('strtolower', explode('.', $fileName)), 'strlen');
        $hasForbidden = false;
        foreach ($fileSegments as $segment) {
            if (in_array($segment, $forbiddenExtensions, true)) {
                $hasForbidden = true;
                break;
            }
        }

        if ($fileInfo !== false && isset($allowedMimeTypes[$type]) && !$hasForbidden) {
            $uploadDir = __DIR__ . '/../assets/img/users/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $safeBase = preg_replace('/[^a-z0-9_]/i', '_', strtolower($newName ?: 'guest'));
            $fileName = 'coordinator_' . $safeBase . '_' . time() . '.' . $allowedMimeTypes[$type];
            $target = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['user_avatar']['tmp_name'], $target)) {
                chmod($target, 0644);
                $_SESSION['coordinator_avatar'] = 'assets/img/users/' . $fileName;
                $_SESSION['user_avatar'] = 'assets/img/users/' . $fileName;
            }
        }
    }

    // Keep in DB if available.
    if (!empty($_SESSION['coordinator_id'])) {
        require_once __DIR__ . '/../dbconnection.php';

        $profileSql = 'UPDATE coordinator_accounts SET full_name = ?, email = ?';
        $params = [$_SESSION['coordinator_name'], $_SESSION['coordinator_email']];

        if (!empty($_SESSION['coordinator_avatar'])) {
            $profileSql .= ', avatar = ?';
            $params[] = $_SESSION['coordinator_avatar'];
        }

        $profileSql .= ' WHERE id = ?';
        $params[] = $_SESSION['coordinator_id'];

        $stmt = $conn->prepare($profileSql);
        if ($stmt) {
            $stmt->execute($params);
        }
    }

    $profileMessage = '<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Profile updated successfully.</div>';
}

include __DIR__ . '/../dbconnection.php';

// Load coordinator data from database if available
$coordinatorData = null;
if (!empty($_SESSION['coordinator_id'])) {
    try {
        $stmt = $conn->prepare("SELECT full_name, email, department, avatar, created_at FROM coordinator_accounts WHERE id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $_SESSION['coordinator_id']);
            $stmt->execute();

            if (method_exists($stmt, 'get_result')) {
                $result = $stmt->get_result();
                $coordinatorData = $result ? $result->fetch_assoc() : null;
            } else {
                $stmt->bind_result($fullName, $email, $department, $avatar, $createdAt);
                if ($stmt->fetch()) {
                    $coordinatorData = [
                        'full_name' => $fullName,
                        'email' => $email,
                        'department' => $department,
                        'avatar' => $avatar,
                        'created_at' => $createdAt,
                    ];
                }
            }
        }
    } catch (Exception $e) {
        error_log('Error loading coordinator data: ' . $e->getMessage());
    }
}

// Use database data if available, otherwise fall back to session
$userName = $coordinatorData['full_name'] ?? $_SESSION['coordinator_name'] ?? $_SESSION['user_name'] ?? 'Coordinator Guest';
$userEmail = $coordinatorData['email'] ?? $_SESSION['coordinator_email'] ?? $_SESSION['user_email'] ?? 'coordinator@example.com';
$userDepartment = $coordinatorData['department'] ?? $_SESSION['coordinator_department'] ?? 'Unassigned';
$userAvatar = $coordinatorData['avatar'] ?? $_SESSION['coordinator_avatar'] ?? $_SESSION['user_avatar'] ?? '';
$lastLogin = $_SESSION['coordinator_last_login'] ?? $_SESSION['last_login'] ?? date('Y-m-d H:i:s');

$coordinatorTheme = getDepartmentThemeClass($userDepartment);
?>
<body class="hold-transition sidebar-mini layout-fixed theme-<?= htmlspecialchars($coordinatorTheme) ?>">
<div class="wrapper">

  <div class="preloader flex-column justify-content-center align-items-center" style="background: linear-gradient(to bottom, blue, yellow);">
    <img class="animation__shake" src="../assets/img/users/OIP.webp" alt="Preloader" height="150" width="150" style="border-radius: 50%;">
  </div>

  <?php include __DIR__ . '/../includes/navbar.php'; ?>
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="content-wrapper" style="background: #f5f9ff;">
    <section class="content d-flex align-items-center" style="min-height: calc(100vh - 140px);">
      <div class="container-fluid">
        <div class="row justify-content-center">
          <div class="col-md-6">
            <div class="card card-primary shadow-sm rounded-lg">
              <div class="card-header"><h3 class="card-title"><i class="fas fa-user"></i> My Profile</h3></div>
              <div class="card-body">
                <?= $profileMessage ?>
                <form action="" method="POST" enctype="multipart/form-data">
                  <div class="form-group text-center">
                    <?php if (!empty($userAvatar) && file_exists(__DIR__ . '/../' . ltrim($userAvatar, '/'))): ?>
                      <img id="profilePreview" src="/ojt1/<?= htmlspecialchars($userAvatar) ?>" class="img-circle elevation-2" alt="Profile Image" style="width: 100px; height: 100px; object-fit: cover; display: block; margin: 0 auto;">
                      <div id="profilePlaceholder" style="display: none;"></div>
                    <?php else: ?>
                      <img id="profilePreview" src="#" class="img-circle elevation-2" alt="Profile Image" style="width: 100px; height: 100px; object-fit: cover; display: none; margin: 0 auto;">
                      <div id="profilePlaceholder" class="img-circle elevation-2 d-flex justify-content-center align-items-center" style="width: 100px; height: 100px; background: #6c757d; color: #fff; font-size: 36px; margin: auto;">
                        <?= htmlspecialchars(substr($userName, 0, 1)) ?>
                      </div>
                    <?php endif; ?>
                    <div class="mt-2 d-flex justify-content-center">
                      <label class="btn btn-sm btn-outline-secondary mx-auto" for="userAvatar">Change photo</label>
                      <input id="userAvatar" name="user_avatar" type="file" accept="image/png, image/jpeg" class="d-none">
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="userName">Name</label>
                    <input id="userName" name="user_name" class="form-control" required value="<?= htmlspecialchars($userName) ?>">
                  </div>

                  <div class="form-group">
                    <label for="userEmail">Email</label>
                    <input id="userEmail" name="user_email" type="email" class="form-control" required value="<?= htmlspecialchars($userEmail) ?>">
                  </div>

                  <div class="form-group">
                    <label>Department</label>
                    <input class="form-control" readonly value="<?= htmlspecialchars($userDepartment) ?>">
                  </div>

                  <div class="form-group">
                    <label>Role</label>
                    <input class="form-control" readonly value="Coordinator">
                  </div>

                  <div class="form-group">
                    <label>Last login</label>
                    <input class="form-control" readonly value="<?= htmlspecialchars($lastLogin) ?>">
                  </div>

                  <button type="submit" class="btn btn-primary btn-block">Update Profile</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
  <?php include __DIR__ . '/../includes/script.php'; ?>
  <script src="../assets/js/coodinator/profile.js"></script>
</div>
</body>
</html>