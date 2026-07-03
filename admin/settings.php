<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Admin Settings';
include __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../dbconnection.php';


$errors = [];
$success = '';
$oldPassword = '';
$newPassword = '';
$confirmPassword = '';

function verifyAdminPassword(string $password): bool
{
    global $conn;
    if (!empty($_SESSION['admin_id'])) {
        /** @var \PDOStatement|false $stmt */
        $stmt = $conn->prepare("SELECT password_hash FROM admin_users WHERE id = ? LIMIT 1");
        if ($stmt) {
            $stmt->execute([$_SESSION['admin_id']]);
            /** @var array|false $result */
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result && password_verify($password, $result['password_hash'])) {
                return true;
            }
        }
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (trim($oldPassword) === '') {
        $errors[] = 'Current password is required.';
    }
    if (trim($newPassword) === '') {
        $errors[] = 'New password is required.';
    }
    if ($newPassword !== $confirmPassword) {
        $errors[] = 'New password and confirmation must match.';
    }
    if ($newPassword !== '' && strlen($newPassword) < 8) {
        $errors[] = 'New password must be at least 8 characters long.';
    }

    if (empty($errors)) {
        
        $currentPasswordValid = false;

        if (!empty($_SESSION['coordinator_logged_in']) && $_SESSION['coordinator_logged_in'] === true) {
            require_once __DIR__ . '/../dbconnection.php';
            if (!empty($_SESSION['coordinator_id'])) {
                /** @var \PDOStatement|false $stmt */
                $stmt = $conn->prepare('SELECT password_hash FROM coordinator_accounts WHERE id = ? LIMIT 1');
                if ($stmt) {
                    $stmt->execute([$_SESSION['coordinator_id']]);
                    /** @var array|false $row */
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($row && password_verify($oldPassword, $row['password_hash'])) {
                        $currentPasswordValid = true;
                        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                        $update = $conn->prepare('UPDATE coordinator_accounts SET password_hash = ? WHERE id = ?');
                        if ($update) {
                            $update->execute([$newHash, $_SESSION['coordinator_id']]);
                            $success = 'Coordinator password updated successfully.';
                        } else {
                            $errors[] = 'Unable to save new coordinator password. Please try again later.';
                        }
                    }
                }
            }
        } elseif (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            if (!verifyAdminPassword($oldPassword)) {
                $currentPasswordValid = false;
            } else {
                $currentPasswordValid = true;
            }

            if ($currentPasswordValid) {
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

                
                if (!empty($_SESSION['admin_id'])) {
                    $stmt = $conn->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
                    if ($stmt->execute([$newHash, $_SESSION['admin_id']])) {
                        $success = 'Admin password updated successfully.';
                    } else {
                        $errors[] = 'Failed to save admin password.';
                    }
                } else {
                    $errors[] = 'Admin session invalid.';
                }
            }
        }

        if (!empty($_SESSION['coordinator_logged_in']) && $_SESSION['coordinator_logged_in'] === true && !isset($newHash)) {
            $errors[] = 'Current password is incorrect.';
        }
        if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && !$currentPasswordValid) {
            $errors[] = 'Current password is incorrect.';
        }

        
        if ($success) {
            $oldPassword = $newPassword = $confirmPassword = '';
        }
    }
}
?>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <div class="preloader flex-column justify-content-center align-items-center" style="background: linear-gradient(to bottom, blue, yellow);">
    <img class="animation__shake" src="../assets/img/users/OIP.webp" alt="Preloader" height="150" width="150" style="border-radius: 50%;">
  </div>

  <?php include __DIR__ . '/../includes/navbar_admin.php'; ?>
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
                  <div class="alert alert-danger" role="alert">
                    <ul class="mb-0">
                      <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                      <?php endforeach; ?>
                    </ul>
                  </div>
                <?php endif; ?>

                <form action="#" method="POST" autocomplete="off">
                  <div class="form-group">
                    <label for="profileName">Display Name</label>
                    <input id="profileName" class="form-control" value="<?= htmlspecialchars($_SESSION['admin_name'] ?? $_SESSION['user_name'] ?? 'Admin Guest') ?>" readonly>
                  </div>
                  <div class="form-group">
                    <label for="profileEmail">Email</label>
                    <input id="profileEmail" class="form-control" value="<?= htmlspecialchars($_SESSION['admin_email'] ?? $_SESSION['user_email'] ?? 'admin@example.com') ?>" readonly>
                  </div>
                  <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <input id="currentPassword" type="password" name="current_password" class="form-control" value="<?= htmlspecialchars($oldPassword) ?>" required>
                  </div>
                  <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input id="newPassword" type="password" name="new_password" class="form-control" value="<?= htmlspecialchars($newPassword) ?>" required>
                  </div>
                  <div class="form-group">
                    <label for="confirmPassword">Confirm New Password</label>
                    <input id="confirmPassword" type="password" name="confirm_password" class="form-control" value="<?= htmlspecialchars($confirmPassword) ?>" required>
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