<?php
include 'dbconnection.php';


echo "=== Checking Ninak (TC-23-A-00490) ===\n\n";


$stmt = $conn->prepare("SELECT * FROM student_progress WHERE student_id = 'TC-23-A-00490'");
$stmt->execute();
$result = $stmt->get_result();
echo "student_progress table:\n";
while ($row = $result->fetch_assoc()) {
    print_r($row);
}


$stmt2 = $conn->prepare("SELECT * FROM attendance_records WHERE student_id = 'TC-23-A-00490'");
$stmt2->execute();
$result2 = $stmt2->get_result();
echo "\nattendance_records table:\n";
while ($row = $result2->fetch_assoc()) {
    print_r($row);
}


$stmt3 = $conn->prepare("SELECT * FROM students WHERE student_id = 'TC-23-A-00490'");
$stmt3->execute();
$result3 = $stmt3->get_result();
echo "\nstudents table:\n";
while ($row = $result3->fetch_assoc()) {
    print_r($row);
}


$stmt4 = $conn->prepare("SELECT * FROM coordinator_student_assignments WHERE student_id = 'TC-23-A-00490'");
$stmt4->execute();
$result4 = $stmt4->get_result();
echo "\ncoordinator_student_assignments table:\n";
while ($row = $result4->fetch_assoc()) {
    print_r($row);
}


echo "\ncoordinator_department_assignments:\n";
$stmt5 = $conn->query("SELECT * FROM coordinator_department_assignments");
while ($row = $stmt5->fetch_assoc()) {
    print_r($row);
}