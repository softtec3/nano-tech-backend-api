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

    if (!isset($_GET["sales_point_id"]) || !isset($_GET["product_id"])) {
        throw new Exception("?sales_point_id=&product_id= must needed");
    }
    $sales_point_id = (int) $_GET["sales_point_id"] ?? 0;
    $product_id = (int) $_GET["product_id"] ?? 0;
    $status = "assigned";

    $stmt = $conn->prepare("SELECT assign_id FROM sales_points_products WHERE sales_point_id=? AND product_id=? AND status=?");


    if (!$stmt) {
        throw new Exception("SQL failed: " . $conn->error);
    }
    $stmt->bind_param("iis", $sales_point_id, $product_id, $status);
    if (!$stmt->execute()) {
        throw new Exception("Fetching failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row["assign_id"];
        }
        $response["success"] = true;
        $response["message"] = "Fetching successful";
        $response["data"] = $ids ?? [];
    } else {
        $response["message"] = "0 id found";
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
