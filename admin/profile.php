<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Admin Profile';
include __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../dbconnection.php';


$profileMessage = '';
$avatarUploadMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName = trim($_POST['user_name'] ?? '');
    $newEmail = trim($_POST['user_email'] ?? '');

    
    if (!empty($_SESSION['admin_id'])) {
        $updateFields = [];
        $updateValues = [];

        if ($newName !== '') {
            $updateFields[] = 'name = ?';
            $updateValues[] = $newName;
            $_SESSION['admin_name'] = $newName;
            $_SESSION['user_name'] = $newName;
        }

        if ($newEmail !== '') {
            $updateFields[] = 'email = ?';
            $updateValues[] = $newEmail;
            $_SESSION['admin_email'] = $newEmail;
            $_SESSION['user_email'] = $newEmail;
        }

        if (!empty($updateFields)) {
            $updateValues[] = $_SESSION['admin_id'];
            $stmt = $conn->prepare("UPDATE admin_users SET " . implode(', ', $updateFields) . " WHERE id = ?");
            if ($stmt) {
                $types = str_repeat('s', count($updateValues));
                $stmt->bind_param($types, ...$updateValues);
                if (!$stmt->execute()) {
                    $avatarUploadMessage = '<div class="alert alert-danger">Failed to update profile text data.</div>';
                }
                $stmt->close();
            }
        }
    }

    
    if (isset($_FILES['user_avatar']) && $_FILES['user_avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['user_avatar']['error'] !== UPLOAD_ERR_OK) {
            $avatarUploadMessage = '<div class="alert alert-danger">Avatar upload failed. Error code: ' . $_FILES['user_avatar']['error'] . '</div>';
        } else {
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

            if ($fileInfo === false || !isset($allowedMimeTypes[$type]) || $hasForbidden) {
                $avatarUploadMessage = '<div class="alert alert-danger">Invalid avatar file. Only JPG and PNG images are allowed.</div>';
            } else {
                $uploadDir = __DIR__ . '/../assets/img/users/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $safeBase = preg_replace('/[^a-z0-9_]/i', '_', strtolower($newName ?: ($_SESSION['admin_name'] ?? 'guest')));
                $fileName = 'user_' . $safeBase . '_' . time() . '.' . $allowedMimeTypes[$type];
                $target = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['user_avatar']['tmp_name'], $target)) {
                    chmod($target, 0644);
                    $avatarPath = 'assets/img/users/' . $fileName;
                    $_SESSION['admin_avatar'] = $avatarPath;
                    $_SESSION['user_avatar'] = $avatarPath;

                    if (!empty($_SESSION['admin_id'])) {
                        $stmt = $conn->prepare("UPDATE admin_users SET avatar = ? WHERE id = ?");
                        if ($stmt) {
                            $stmt->bind_param('si', $avatarPath, $_SESSION['admin_id']);
                            if (!$stmt->execute()) {
                                $avatarUploadMessage = '<div class="alert alert-danger">Failed to save avatar path in database.</div>';
                            }
                            $stmt->close();
                        }
                    }

                    if ($avatarUploadMessage === '') {
                        $avatarUploadMessage = '<div class="alert alert-success">Avatar uploaded successfully.</div>';
                    }
                } else {
                    $avatarUploadMessage = '<div class="alert alert-danger">Failed to move uploaded avatar file on server.</div>';
                }
            }
        }
    }

    if ($avatarUploadMessage === '') {
        $profileMessage = '<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Profile updated successfully.</div>';
    } else {
        $profileMessage = $avatarUploadMessage;
    }
}


$userName = $_SESSION['admin_name'] ?? $_SESSION['user_name'] ?? 'Admin Guest';
$userEmail = $_SESSION['admin_email'] ?? $_SESSION['user_email'] ?? 'admin@example.com';
$userAvatar = $_SESSION['admin_avatar'] ?? $_SESSION['user_avatar'] ?? '';
$lastLogin = $_SESSION['admin_last_login'] ?? $_SESSION['last_login'] ?? date('Y-m-d H:i:s');


$avatarUrl = '';
if ($userAvatar !== '') {
    $avatarUrl = '/ojt1/' . ltrim($userAvatar, '/') . '?v=' . time();
}


$_SESSION['admin_name'] = $userName;
$_SESSION['admin_email'] = $userEmail;
if (!empty($userAvatar)) {
  $_SESSION['admin_avatar'] = $userAvatar;
}
$_SESSION['admin_last_login'] = $lastLogin;
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
            <div class="card card-primary shadow-sm rounded-lg">
              <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user"></i> My Profile</h3>
              </div>
              <div class="card-body">
                <?= $profileMessage ?>
                <form action="" method="POST" enctype="multipart/form-data">
                  <div class="form-group text-center">
                    <?php if (!empty($userAvatar) && file_exists(__DIR__ . '/../' . ltrim($userAvatar, '/'))): ?>
                      <img id="profilePreview" src="<?= htmlspecialchars($avatarUrl) ?>" class="img-circle elevation-2" alt="Profile Image" style="width: 100px; height: 100px; object-fit: cover; display: block; margin: 0 auto;">
                      <div id="profilePreviewPlaceholder" style="display: none;"></div>
                    <?php else: ?>
                      <img id="profilePreview" src="#" class="img-circle elevation-2" alt="Profile Preview" style="width: 100px; height: 100px; object-fit: cover; display: none; margin: 0 auto;">
                      <div id="profilePreviewPlaceholder" class="img-circle elevation-2 d-flex justify-content-center align-items-center" style="width: 100px; height: 100px; background: #6c757d; color: #fff; font-size: 36px; margin: auto;">
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
                    <label>Role</label>
                    <input class="form-control" readonly value="Admin">
                  </div>

                  <div class="form-group">
                    <label>Last login</label>
                    <input class="form-control" readonly value="<?= htmlspecialchars($lastLogin) ?>">
                  </div>

                  <button type="submit" class="btn btn-primary btn-block">Update Profile</button>
                </form>

                <hr.>
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
    document.addEventListener('DOMContentLoaded', function() {
      const userAvatarInput = document.getElementById('userAvatar');
      const previewImg = document.getElementById('profilePreview');
      const placeholder = document.getElementById('profilePreviewPlaceholder');

      if (!userAvatarInput) return;

      userAvatarInput.addEventListener('change', function(event) {
        const file = event.target.files && event.target.files[0];
        if (!file) {
          return;
        }

        if (!file.type.startsWith('image/')) {
          alert('Please choose a valid image file.');
          return;
        }

        const objectUrl = URL.createObjectURL(file);

        if (previewImg) {
          previewImg.src = objectUrl;
          previewImg.style.display = 'inline-block';
        }

        if (placeholder) {
          placeholder.style.display = 'none';
        }

        
        previewImg.onload = function() {
          URL.revokeObjectURL(objectUrl);
        };
      });
    });
  </script>
</div>
</body>
</html>