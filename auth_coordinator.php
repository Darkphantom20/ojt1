<?php
session_start();
require_once 'dbconnection.php';
require_once 'csrf_protection.php';

function sanitizeInput(string $value): string
{
    return trim(str_replace(['<', '>', '"', "'", '`'], '', $value));
}


$csrfToken = $_POST['csrf_token'] ?? '';
if (!validate_csrf_token($csrfToken)) {
    header('Location: index.php?coordinator_login_failed=1');
    exit;
}

$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$accessCode = sanitizeInput($_POST['access_code'] ?? '');

if (!$accessCode || !$password) {
    header('Location: index.php?coordinator_login_failed=1');
    exit;
}

$sql = "SELECT id, full_name, email, department, password_hash, access_code, status, avatar FROM coordinator_accounts WHERE access_code = ? LIMIT 1";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    header('Location: index.php?coordinator_login_failed=1');
    exit;
}
$stmt->bind_param('s', $accessCode);
$stmt->execute();

$account = null;
if (method_exists($stmt, 'get_result')) {
    $result = $stmt->get_result();
    $account = $result ? $result->fetch_assoc() : null;
} else {
    $stmt->bind_result($id, $fullName, $email, $passwordHash, $accessCodeDb, $status, $avatar);
    if ($stmt->fetch()) {
        $account = [
            'id' => $id,
            'full_name' => $fullName,
            'email' => $email,
            'password_hash' => $passwordHash,
            'access_code' => $accessCodeDb,
            'status' => $status,
            'avatar' => $avatar,
        ];
    }
}

if ($account && password_verify($password, $account['password_hash'])) {
    
    if ($account['status'] === 'disabled') {
        header('Location: index.php?coordinator_login_failed=1');
        exit;
    }

    $_SESSION['coordinator_logged_in'] = true;
    $_SESSION['coordinator_id'] = $account['id'];
    $_SESSION['coordinator_name'] = $account['full_name'];
    $_SESSION['coordinator_email'] = $account['email'];
    $_SESSION['coordinator_department'] = $account['department'] ?? 'Unassigned';
    $_SESSION['coordinator_avatar'] = $account['avatar'] ?? '';
    $_SESSION['user_name'] = $account['full_name'];
    $_SESSION['user_email'] = $account['email'];
    $_SESSION['user_avatar'] = $account['avatar'] ?? '';

    
    if ($account['status'] === 'unused') {
        $update = $conn->prepare("UPDATE coordinator_accounts SET status = 'active' WHERE id = ?");
        if ($update) {
            $update->execute([$account['id']]);
        }
    }

    
    regenerate_csrf_token();

    header('Location: /ojt1/coodinator/coordinator.php');
    exit;
} else {
    header('Location: index.php?coordinator_login_failed=1');
    exit;
}
