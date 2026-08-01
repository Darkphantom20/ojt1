<?php
$conn = new mysqli("localhost", "root", "", "ojthub");
if ($conn->connect_error) { echo "ERR: " . $conn->connect_error; exit(1); }
$res = $conn->query("SHOW CREATE TABLE coordinator_office_assignments");
if (!$res) { echo "ERR: " . $conn->error; exit(1); }
$row = $res->fetch_assoc();
echo $row["Create Table"];

