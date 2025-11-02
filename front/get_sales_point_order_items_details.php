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

    if (!isset($_GET["sales_point_id"]) || $_GET["sales_point_id"] == "" ||  !isset($_GET["order_id"]) || $_GET["order_id"] == "") {
        throw new Exception("?sales_point_id=&order_id= must needed");
    }

    $sales_point_id = (int) $_GET["sales_point_id"] ?? 0;
    $order_id = (int) $_GET["order_id"] ?? 0;

    $stmt = $conn->prepare("SELECT * FROM sales_point_order_items WHERE sales_point_id=? AND order_id=? ORDER BY id DESC");
    if (!$stmt) {
        throw new Exception("SQL failed: " . $conn->error);
    }
    $stmt->bind_param("ii", $sales_point_id, $order_id);

    if (!$stmt->execute()) {
        throw new Exception("Fetching failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $items_details[] = $row;
        }
        $response["success"] = true;
        $response["message"] = "Fetching successful";
        $response["data"] = $items_details;
    }
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
