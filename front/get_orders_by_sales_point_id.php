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

    if (!isset($_GET["sales_point_id"]) || $_GET["sales_point_id"] == "") {
        throw new Exception("?sales_point_id= must needed");
    }

    $sales_point_id = (int) $_GET["sales_point_id"] ?? 0;

    $stmt = $conn->prepare("SELECT * FROM sales_point_orders WHERE sales_point_id=? ORDER BY id DESC");
    if (!$stmt) {
        throw new Exception("SQL failed: " . $conn->error);
    }
    $stmt->bind_param("i", $sales_point_id);

    if (!$stmt->execute()) {
        throw new Exception("Fetching failed: "  . $stmt->error);
    }
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        $response["success"] = true;
        $response["message"] = "Fetching successful";
        $response["data"] = $orders;
    } else {
        $response["message"] = "0 order found";
    }



    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
