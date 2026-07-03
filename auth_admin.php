<?php
session_start();
require_once 'dbconnection.php';
require_once 'csrf_protection.php';


$csrfToken = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($csrfToken)) {
    header('Location: index.php?admin_login_failed=1');
    exit;
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (!$username || !$password) {
    header('Location: index.php?admin_login_failed=1');
    exit;
}


$stmt = $conn->prepare("SELECT id, username, password_hash, name, email, avatar FROM admin_users WHERE username = ? LIMIT 1");
if (!$stmt) {
    header('Location: index.php?admin_login_failed=1');
    exit;
}
$stmt->bind_param('s', $username);
$stmt->execute();

$admin = null;
if (method_exists($stmt, 'get_result')) {
    $result = $stmt->get_result();
    $admin = $result ? $result->fetch_assoc() : null;
} else {
    $stmt->bind_result($id, $dbUsername, $passwordHash, $name, $email, $avatar);
    if ($stmt->fetch()) {
        $admin = [
            'id' => $id,
            'username' => $dbUsername,
            'password_hash' => $passwordHash,
            'name' => $name,
            'email' => $email,
            'avatar' => $avatar,
        ];
    }
}

$adminPasswordHash = $admin['password_hash'] ?? '';
$isPasswordValid = false;

if ($admin) {
    if (!empty($adminPasswordHash) && password_verify($password, $adminPasswordHash)) {
        $isPasswordValid = true;
    } elseif ($password === $adminPasswordHash) {
        
        $isPasswordValid = true;
    }
}

if ($admin && $isPasswordValid) {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_name'] = $admin['name'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_avatar'] = $admin['avatar'] ?? '';
    $_SESSION['user_name'] = $admin['name'];
    $_SESSION['user_email'] = $admin['email'];

    
    regenerate_csrf_token();

    header('Location: admin/admin.php');
    exit;
}

header('Location: index.php?admin_login_failed=1');
exit;
