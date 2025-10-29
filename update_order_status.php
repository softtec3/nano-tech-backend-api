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

    if (!isset($_GET["order_id"]) || !isset($_GET["status"])) {
        throw new Exception("?order_id=?&status= is needed");
    }

    $order_id = (int) $_GET["order_id"] ?? 0;
    $status = $_GET["status"] ?? "";
    if (empty($status) && $status != "pending" && $status != "confirmed" && $status != "shipped" && $status != "delivered" && $status != "rejected") {
        throw new Exception("Invalid status");
    }

    $stmt = $conn->prepare("UPDATE customer_orders SET order_status=? WHERE id=?");

    if (!$stmt) {
        throw new Exception("SQL failed: " . $conn->error);
    }

    $stmt->bind_param("si", $status, $order_id);
    if (!$stmt->execute()) {
        throw new Exception("Update failed: " . $stmt->error);
    }

    if ($stmt->affected_rows > 0) {
        $response["success"] = true;
        $response["message"] = "Status successfully updated to " . $status;
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
