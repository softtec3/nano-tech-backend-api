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
    if ($status === "delivered") {
        $payment_status = "paid";
        $stmt2 = $conn->prepare("UPDATE customer_orders SET payment_status=? WHERE id=?");
        if (!$stmt2) {
            throw new Exception("SQL failed customer_orders: " . $conn->error);
        }
        $stmt2->bind_param("si", $payment_status, $order_id);
        if (!$stmt2->execute()) {
            throw new Exception("Failed to update customer_orders: " . $stmt2->error);
        }
        $stmt2->close();
    }
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
