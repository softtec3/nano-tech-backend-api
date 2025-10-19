<?php
require_once("./db_connect.php");

session_start();
session_unset();
session_destroy();

$response = [
    "success" => true,
    "message" => "Successfully logout",
    "data" => []
];
echo json_encode($response);
