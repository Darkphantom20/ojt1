<?php
session_start();
$_SESSION["coordinator_logged_in"] = true;
$_SESSION["coordinator_id"] = 1;
$_SERVER["REQUEST_METHOD"] = "POST";
$_POST = [
    "student_ids" => json_encode(["TC-25-B-00566"]),
    "location_name" => "Test Office",
    "location_address" => "123 Test",
    "lat" => "12.345678",
    "lng" => "98.765432"
];
ob_start();
require __DIR__ . "/coodinator/save_office_assignment.php";
$output = ob_get_clean();
echo $output;

