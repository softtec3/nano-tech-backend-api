<?php
session_start();
require_once("./db_connect.php");
$response = [
    "success" => false,
    "message" => "",
    "data" => []
];

try {
    if (!empty($_SESSION["logged_user_email"]) && !empty($_SESSION["logged_user_role"])) {
        $response["success"] = true;
        $response["message"] = "User is authenticated";
        $response["data"] = [
            "logged_user_email" => $_SESSION["logged_user_email"],
            "logged_user_role" => $_SESSION["logged_user_role"],
        ];
    }
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
