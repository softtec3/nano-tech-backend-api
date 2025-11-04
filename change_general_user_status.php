<?php
require_once("./db_connect.php");
require_once("./auth_admin_only.php");
$response = [
    "success" => false,
    "message" => "",
    "data" => []
];


try {
    if ($_SERVER["REQUEST_METHOD"] != "GET") {
        throw new Exception("Invalid request method. Must be GET request");
    }
    if (!isset($_GET["user_id"]) || empty($_GET["user_id"]) || !isset($_GET["status"]) || empty($_GET["status"])) {
        throw new Exception("?user_id=&status= must needed");
    }
    $user_id = (int) $_GET["user_id"] ?? 0;
    $status = $_GET["status"];
    if ($status !== "active" && $status !== "inactive") {
        throw new Exception("Status must be active or inactive");
    }
    $stmt = $conn->prepare("UPDATE general_users SET status=? WHERE id=?");
    if (!$stmt) {
        throw new Exception("SQL failed" . $conn->error);
    }
    $stmt->bind_param("si", $status, $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to update: " . $stmt->error);
    }
    if ($stmt->affected_rows > 0) {
        $response["success"] = true;
        $response["message"] = "Successfully change status to " . $status;
    } else {
        $response["success"] = true;
        $response["message"]  = "No update";
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
