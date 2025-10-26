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

    if (!isset($_GET["product_id"]) || !isset($_GET["sales_point_id"]) || $_GET["product_id"] == "" || $_GET["sales_point_id"] == "") {
        throw new Exception("?product_id=&sales_point_id= must needed");
    }

    $product_id = (int) $_GET["product_id"] ?? 0;
    $sales_point_id = (int) $_GET["sales_point_id"] ?? 0;

    $stmt = $conn->prepare("SELECT assign_id, status FROM sales_points_products WHERE product_id=? AND sales_point_id=?");

    if (!$stmt) {
        throw new Exception("SQL failed: " . $conn->error);
    }

    $stmt->bind_param("ii", $product_id, $sales_point_id);

    if (!$stmt->execute()) {
        throw new Exception("Fetching failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row;
        }
        $response["success"] = true;
        $response["message"] = "Fetching successful";
        $response["data"] = $ids;
    } else {
        $response["message"] = "0 data found";
    }





    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
