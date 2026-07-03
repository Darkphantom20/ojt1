<?php
include 'dbconnection.php';
$res = $conn->query('DESCRIBE students');
if (!$res) {
    echo 'query failed: ' . $conn->error . "\n";
    exit;
}
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . ' ' . $row['Type'] . ' ' . $row['Null'] . ' ' . $row['Key'] . ' ' . ($row['Default'] === NULL ? 'NULL' : $row['Default']) . "\n";
}
