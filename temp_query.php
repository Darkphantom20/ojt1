<?php
$db = new mysqli("localhost","root","","ojthub");
if ($db->connect_error) { echo "DB ERROR: ".$db->connect_error; exit(1); }
$coordinatorId = 1;
$res = $db->query("SELECT * FROM coordinator_department_assignments WHERE coordinator_id = $coordinatorId LIMIT 10");
if (!$res) { echo "DEPT QUERY ERROR: ".$db->error; exit(1); }
echo "coordinator_department_assignments:\n";
while ($row = $res->fetch_assoc()) { echo json_encode($row)."\n"; }
$res2 = $db->query("SELECT student_id, name, department FROM students LIMIT 10");
if (!$res2) { echo "STUD QUERY ERROR: ".$db->error; exit(1); }
echo "students:\n";
while ($row = $res2->fetch_assoc()) { echo json_encode($row)."\n"; }
$db->close();
