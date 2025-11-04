<?php
require_once("./db_connect.php");
$response = [
    "success" => false,
    "message" => "",
    "data" => []
];


try {
    if ($_SERVER["REQUEST_METHOD"] != "GET") {
        throw new Exception("Invalid request method. Must be GET request");
    }

    if (!isset($_GET["sales_point_id"]) || !isset($_GET["status"]) || $_GET["sales_point_id"] == "" || $_GET["status"] == "" || !isset($_GET["user_id"]) || $_GET["user_id"] == "") {
        throw new Exception("?sales_point=&status=&user_id= is needed");
    }

    $sales_point_id = (int) $_GET["sales_point_id"] ?? 0;
    $user_id = (int) $_GET["user_id"] ?? 0;
    $status = $_GET["status"] ?? "active";
    if ($status !== "active" && $status !== "inactive") {
        $response["data"] = ["status" => $status];
        throw new Exception("Status must be active or inactive");
    }

    $stmt = $conn->prepare("UPDATE sales_points SET status=? WHERE id=?");
    if (!$stmt) {
        throw new Exception("SQL failed: " . $conn->error);
    }
    $stmt->bind_param("si", $status, $sales_point_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to update: " . $stmt->error);
    }
    // general user update
    $stmt2 = $conn->prepare("UPDATE general_users SET status=? WHERE id=?");
    if (!$stmt2) {
        throw new Exception("SQL failed: " . $conn->error);
    }
    $stmt2->bind_param("si", $status, $user_id);
    if (!$stmt2->execute()) {
        throw new Exception("Failed to update: " . $stmt2->error);
    }
    if ($stmt->affected_rows > 0) {
        $response["success"] = true;
        $response["message"] = "Status successfully updated";
    } else {
        $response["message"] = "Not updated with id: " . $sales_point_id;
    }


    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
