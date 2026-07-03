<?php
require_once __DIR__ . '/dbconnection.php';

$database = $conn->real_escape_string('ojthub');
$constraints = [
    [
        'table' => 'attendance_records',
        'constraint' => 'attendance_records_ibfk_1',
    ],
    [
        'table' => 'student_progress',
        'constraint' => 'student_progress_ibfk_1',
    ],
];

$checkSql = "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_TYPE = 'FOREIGN KEY' AND CONSTRAINT_NAME = ?";
$checkStmt = $conn->prepare($checkSql);
if (!$checkStmt) {
    die('Schema check failed: ' . $conn->error);
}

foreach ($constraints as $entry) {
    $table_name = $entry['table'];
    $constraint_name = $entry['constraint'];

    $checkStmt->bind_param('sss', $database, $table_name, $constraint_name);
    $checkStmt->execute();
    $checkStmt->store_result();
    if ($checkStmt->num_rows === 0) {
        echo "Foreign key constraint {$constraint_name} not found on {$table_name}. Skipping.\n";
        continue;
    }

    if (!$conn->query("ALTER TABLE `{$table_name}` DROP FOREIGN KEY `{$constraint_name}`")) {
        die('Failed to drop foreign key ' . $constraint_name . ': ' . $conn->error);
    }

    $alterSql = "ALTER TABLE `{$table_name}`
        ADD CONSTRAINT `{$constraint_name}` FOREIGN KEY (`student_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE ON UPDATE CASCADE";
    if (!$conn->query($alterSql)) {
        die('Failed to recreate foreign key ' . $constraint_name . ': ' . $conn->error);
    }

    echo "Updated foreign key {$constraint_name} on {$table_name} to ON DELETE CASCADE ON UPDATE CASCADE.\n";
}

$checkStmt->close();
