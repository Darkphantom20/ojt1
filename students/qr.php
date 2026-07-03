<?php
session_start();

if (empty($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header('Location: /ojt1/index.php');
    exit;
}

$studentId = $_SESSION['student_id'] ?? '';
$studentName = $_SESSION['student_name'] ?? 'Student';

if (!$studentId) {
    header('Location: /ojt1/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student QR Code</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow-sm mx-auto" style="max-width: 420px; margin-top: 20px;">
        <div class="card-body text-center">
            <h4 class="card-title">Student QR Code</h4>
            <p>Welcome, <?= htmlspecialchars($studentName) ?>.</p>
            <p class="text-muted">This QR code is generated from your student ID and will be used for check-in/out.</p>

            <div id="qrcode" class="mb-4" style="display:inline-block;"></div>
            <p><strong><?= htmlspecialchars($studentId) ?></strong></p>
            <p class="small text-muted">Scan this QR code at the attendance screen to auto time in/out.</p>

            <button id="downloadBtn" class="btn btn-outline-secondary mb-2">Download QR</button>
            <a href="/ojt1/students/dashboard.php" class="btn btn-success d-block">Go to Dashboard</a>
        </div>
    </div>
</div>

<script>
    window.studentQrConfig = {
        studentId: <?= json_encode((string) $studentId) ?>
    };
</script>
<script src="../assets/js/student-qr.js"></script>
</body>
</html>
