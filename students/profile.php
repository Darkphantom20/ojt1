<!DOCTYPE html>
<html lang="en">
<?php
session_start();
require_once __DIR__ . '/../dbconnection.php';

if (empty($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header('Location: /ojt1/index.php');
    exit;
}

$pageTitle = 'Student Profile';
include __DIR__ . '/../includes/header.php';

$profileMessage = '';

function getStudentStorePath(): string
{
    return __DIR__ . '/students_users.json';
}

function loadStudentData(): array
{
    $file = getStudentStorePath();
    if (!file_exists($file)) {
        return [];
    }
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function saveStudentData(array $data): bool
{
    $file = getStudentStorePath();
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT)) !== false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newName = trim($_POST['user_name'] ?? '');
    $newEmail = trim($_POST['user_email'] ?? '');

    $students = loadStudentData();
    $studentId = $_SESSION['student_id'] ?? null;

    if ($newName !== '') {
        $_SESSION['student_name'] = $newName;
        $_SESSION['user_name'] = $newName;
        if ($studentId && isset($students[$studentId])) {
            $students[$studentId]['name'] = $newName;
        }
        
        $stmt = $conn->prepare("UPDATE students SET name = ? WHERE student_id = ?");
        $stmt->bind_param('ss', $newName, $studentId);
        $stmt->execute();
    }
    if ($newEmail !== '') {
        $_SESSION['student_email'] = $newEmail;
        $_SESSION['user_email'] = $newEmail;
        if ($studentId && isset($students[$studentId])) {
            $students[$studentId]['email'] = $newEmail;
        }
        
        $stmt = $conn->prepare("UPDATE students SET email = ? WHERE student_id = ?");
        $stmt->bind_param('ss', $newEmail, $studentId);
        $stmt->execute();
    }

    if (isset($_FILES['user_avatar'])) {
        $error = $_FILES['user_avatar']['error'];
        if ($error !== UPLOAD_ERR_OK) {
            $profileMessage = '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Upload error: ' . $error . '</div>';
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
                $profileMessage = '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Invalid file type. Only JPG and PNG images are allowed.</div>';
            } else {
                $uploadDir = __DIR__ . '/../assets/img/users/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $safeBase = preg_replace('/[^a-z0-9_]/i', '_', strtolower($newName ?: 'guest'));
                $fileName = 'student_' . $safeBase . '_' . time() . '.' . $allowedMimeTypes[$type];
                $target = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['user_avatar']['tmp_name'], $target)) {
                    chmod($target, 0644);
                    $_SESSION['student_avatar'] = 'assets/img/users/' . $fileName;
                    $_SESSION['user_avatar'] = 'assets/img/users/' . $fileName;
                    if ($studentId && isset($students[$studentId])) {
                        $students[$studentId]['avatar'] = 'assets/img/users/' . $fileName;
                    }
                    
                    $stmt = $conn->prepare("UPDATE students SET avatar = ? WHERE student_id = ?");
                    $avatarPath = 'assets/img/users/' . $fileName;
                    $stmt->bind_param('ss', $avatarPath, $studentId);
                    $stmt->execute();
                } else {
                    $profileMessage = '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Failed to save the uploaded file.</div>';
                }
            }
        }
    }

    if (!empty($studentId) && !empty($students[$studentId])) {
        saveStudentData($students);
    }

    $profileMessage = '<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Profile updated successfully.</div>';
}

$userName = $_SESSION['student_name'] ?? $_SESSION['user_name'] ?? 'Student Guest';
$userEmail = $_SESSION['student_email'] ?? $_SESSION['user_email'] ?? 'student@example.com';
$userDepartment = $_SESSION['student_department'] ?? 'Not set';
$userRequiredOjtHours = isset($_SESSION['student_required_ojt_hours']) ? intval($_SESSION['student_required_ojt_hours']) : 0;
$userAvatar = $_SESSION['student_avatar'] ?? $_SESSION['user_avatar'] ?? '';
$lastLogin = $_SESSION['student_last_login'] ?? $_SESSION['last_login'] ?? date('Y-m-d H:i:s');
?>
<body class="hold-transition sidebar-mini layout-fixed student-profile-page">
<div class="wrapper">

  <div class="preloader student-profile-preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake profile-avatar" src="../assets/img/users/OIP.webp" alt="Preloader" height="150" width="150">
  </div>

  <?php include __DIR__ . '/../includes/navbar.php'; ?>
  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="content-wrapper student-profile-wrapper">
    <section class="content student-profile-section d-flex align-items-center">
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
                      <img src="/ojt1/<?= htmlspecialchars($userAvatar) ?>" class="img-circle elevation-2 profile-avatar" alt="Profile Image">
                    <?php else: ?>
                      <div class="img-circle elevation-2 profile-avatar-placeholder d-flex justify-content-center align-items-center">
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
                    <label>Department / Course</label>
                    <input class="form-control" readonly value="<?= htmlspecialchars($userDepartment) ?>">
                  </div>

                  <div class="form-group">
                    <label>Required OJT Hours</label>
                    <input class="form-control" readonly value="<?= htmlspecialchars($userRequiredOjtHours . ' hrs') ?>">
                  </div>

                  <div class="form-group">
                    <label>Role</label>
                    <input class="form-control" readonly value="Student">
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
</div>
</body>
</html>