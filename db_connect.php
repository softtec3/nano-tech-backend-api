<?php
// header("Access-Control-Allow-Origin: *");
// specific for fetch credential include
header("Access-Control-Allow-Origin: http://localhost:5173"); // your frontend URL
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = "localhost";
$username = "softtec3_nano_admin";
$password = "7o@5Jf3OT.wRv_VB";
$db_name = "softtec3_nano_tech";

$conn = new mysqli($host, $username, $password, $db_name);

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}
$conn->set_charset("utf8mb4");
