<!DOCTYPE html>
<html lang="en">
<?php
session_start();

if (empty($_SESSION['coordinator_logged_in']) || $_SESSION['coordinator_logged_in'] !== true) {
    header('Location: /ojt1/index.php?coordinator_login_failed=1');
    exit;
}

$pageTitle = 'Coordinator Settings';
include __DIR__ . '/../includes/header.php';

$errors = [];
$success = '';
$current = '';
$new = '';
$confirm = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (trim($current) === '') {
        $errors[] = 'Current password is required.';
    }
    if (trim($new) === '') {
        $errors[] = 'New password is required.';
    }
    if ($new !== $confirm) {
        $errors[] = 'New password and confirmation must match.';
    }
    if ($new !== '' && strlen($new) < 8) {
        $errors[] = 'New password must be at least 8 characters long.';
    }

    if (empty($errors)) {
        require_once __DIR__ . '/../dbconnection.php';
        if (!empty($_SESSION['coordinator_id'])) {
            $stmt = $conn->prepare('SELECT password_hash FROM coordinator_accounts WHERE id = ? LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('i', $_SESSION['coordinator_id']);
                $stmt->execute();

                $passwordHash = null;
                if (method_exists($stmt, 'get_result')) {
                    $result = $stmt->get_result();
                    $row = $result ? $result->fetch_assoc() : null;
                    $passwordHash = $row['password_hash'] ?? null;
                } else {
                    $stmt->bind_result($passwordHash);
                    $stmt->fetch();
                }

                if (!$passwordHash || !password_verify($current, $passwordHash)) {
                    $errors[] = 'Current password is incorrect.';
                } else {
                    $newHash = password_hash($new, PASSWORD_DEFAULT);
                    $update = $conn->prepare('UPDATE coordinator_accounts SET password_hash = ? WHERE id = ?');
                    if ($update) {
                        $update->bind_param('si', $newHash, $_SESSION['coordinator_id']);
                        $update->execute();
                        $success = 'Password updated successfully.';
                    } else {
                        $errors[] = 'Unable to save new password.';
                    }
                }
            } else {
                $errors[] = 'Unable to read current password record.';
            }
        } else {
            $errors[] = 'Coordinator user context missing.';
        }
    }

    if ($success) {
        $current = $new = $confirm = '';
    }
}

include __DIR__ . '/../dbconnection.php';

// Load coordinator data from database if available
$coordinatorData = null;
if (!empty($_SESSION['coordinator_id'])) {
    try {
        $stmt = $conn->prepare("SELECT full_name, email FROM coordinator_accounts WHERE id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $_SESSION['coordinator_id']);
            $stmt->execute();
            if (method_exists($stmt, 'get_result')) {
                $result = $stmt->get_result();
                $coordinatorData = $result ? $result->fetch_assoc() : null;
            } else {
                $stmt->bind_result($fullName, $email);
                if ($stmt->fetch()) {
                    $coordinatorData = ['full_name' => $fullName, 'email' => $email];
                }
            }
        }
    } catch (Exception $e) {
        error_log('Error loading coordinator data: ' . $e->getMessage());
    }
}

// Use database data if available, otherwise fall back to session
$displayName = $coordinatorData['full_name'] ?? $_SESSION['coordinator_name'] ?? $_SESSION['user_name'] ?? 'Coordinator Guest';
$displayEmail = $coordinatorData['email'] ?? $_SESSION['coordinator_email'] ?? $_SESSION['user_email'] ?? 'coordinator@example.com';

$coordinatorDepartment = trim($_SESSION['coordinator_department'] ?? '');
$coordinatorTheme = getDepartmentThemeClass($coordinatorDepartment);
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
            <div class="card card-secondary shadow-sm rounded-lg">
              <div class="card-header"><h3 class="card-title"><i class="fas fa-cogs"></i> Settings</h3></div>
              <div class="card-body">
                <p>Manage your account security and preferences.</p>

                <?php if (!empty($success)): ?>
                  <div class="alert alert-success" role="alert"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                  <div class="alert alert-danger" role="alert"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>

                <form action="#" method="POST" autocomplete="off">
                  <div class="form-group">
                    <label>Display Name</label>
                    <input class="form-control" readonly value="<?= htmlspecialchars($displayName) ?>">
                  </div>
                  <div class="form-group">
                    <label>Email</label>
                    <input class="form-control" readonly value="<?= htmlspecialchars($displayEmail) ?>">
                  </div>
                  <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <input id="currentPassword" type="password" name="current_password" class="form-control" value="<?= htmlspecialchars($current) ?>" required>
                  </div>
                  <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input id="newPassword" type="password" name="new_password" class="form-control" value="<?= htmlspecialchars($new) ?>" required>
                  </div>
                  <div class="form-group">
                    <label for="confirmPassword">Confirm New Password</label>
                    <input id="confirmPassword" type="password" name="confirm_password" class="form-control" value="<?= htmlspecialchars($confirm) ?>" required>
                  </div>
                  <button type="submit" class="btn btn-primary">Update Password</button>
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
</div>
</body>
</html>