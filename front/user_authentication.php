<?php
session_start();
require_once("./db_connect.php");
$response = [
    "success" => false,
    "message" => "",
    "data" => []
];

try {
    if (!empty($_SESSION["user_name"]) && !empty($_SESSION["role"])) {
        $response["success"] = true;
        $response["message"] = "User is authenticated";
        $response["data"] = [
            "user_name" => $_SESSION["user_name"],
            "role" => $_SESSION["role"],
        ];
    }
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
