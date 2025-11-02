<?php
require_once("./db_connect.php");
require_once("./auth_sales_point.php");
$response = [
    "success" => false,
    "message" => "",
    "data" => []
];


try {
    if ($_SERVER["REQUEST_METHOD"] != "GET") {
        throw new Exception("Invalid request method. Must be GET request");
    }

    if (!isset($_GET["order_id"]) || $_GET["order_id"] == "" || !isset($_GET["sales_point_id"]) || $_GET["sales_point_id"] == "") {
        throw new Exception("?order_id=&sales_point_id= must needed");
    }

    $sales_point_id = (int) $_GET["sales_point_id"] ?? 0;
    $order_id = (int) $_GET["order_id"] ?? 0;

    if ($_SESSION["sales_point_id"] != $sales_point_id) {
        http_response_code(403);
        throw new Exception("403 Forbidden. Access denied");
    }

    $stmt  = $conn->prepare("SELECT * FROM sales_point_order_customers WHERE order_id=?");
    if (!$stmt) {
        throw new Exception("SQL failed " . $conn->error);
    }

    $stmt->bind_param("i", $order_id);
    if (!$stmt->execute()) {
        throw new Exception("Fetching failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $details = $row;
        }
        $response["success"] = true;
        $response["message"] = "Fetching successful";
        $response["data"] = $details ?? [];
    } else {
        $response["message"] = "Details not found with this id " . $order_id;
    }



    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
