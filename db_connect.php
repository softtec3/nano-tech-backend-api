<?php
$allowed_origins = [
    "http://localhost:5173",               // local development (Vite)
    "https://admin.nano-techbd.com",       // production frontend
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
    header("Access-Control-Allow-Credentials: true");
}

header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=utf-8");

$host = "localhost";
$username = "softtec3_nano_admin";
$password = "7o@5Jf3OT.wRv_VB";
$db_name = "softtec3_nano_tech";

$conn = new mysqli($host, $username, $password, $db_name);

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}
$conn->set_charset('utf8mb4');
