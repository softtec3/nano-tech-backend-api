<?php
// --- Database connection ---
$host = "localhost";
$username = "softtec3_nano_admin";
$password = "7o@5Jf3OT.wRv_VB";
$db_name = "softtec3_nano_tech";

$conn = new mysqli($host, $username, $password, $db_name);

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

$conn->set_charset('utf8mb4');
