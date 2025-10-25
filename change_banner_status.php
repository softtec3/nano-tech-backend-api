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

    if (!isset($_GET["status"]) || $_GET["status"] == "" || !isset($_GET["banner_id"]) || $_GET["banner_id"] == "") {
        throw new Exception("?status=&banner_id= needed");
    }

    $banner_id = (int) $_GET["banner_id"] ?? 0;
    $banner_status = $_GET["status"] ??  "active";

    $stmt = $conn->prepare("UPDATE banners SET status=? WHERE id=?");
    if (!$stmt) {
        throw new Exception("SQL failed: "  . $conn->error);
    }
    $stmt->bind_param("si", $banner_status, $banner_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to update status " . $stmt->error);
    }
    if ($stmt->affected_rows > 0) {
        $response["success"] = true;
        $response["message"] = "Status successfully updated";
    } else {
        $response["success"] = false;
        $response["message"] =  "No update";
    }


    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
