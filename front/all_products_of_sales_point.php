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

    if (!isset($_GET["sales_point_id"])) {
        throw new Exception("?sales_point_id= must needed");
    }
    $sales_point_id = (int) $_GET["sales_point_id"] ?? 0;

    $products = [];

    $stmt = $conn->prepare("SELECT product_id FROM sales_points_products_summary WHERE sales_point_id=?");
    if (!$stmt) {
        throw new Exception("SQL failed " . $conn->error);
    }
    $stmt->bind_param("i", $sales_point_id);
    if (!$stmt->execute()) {
        throw new Exception("Fetching failed: " . $stmt->error);
    }
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $pro[] = $row["product_id"];
        }
        $products = $pro ?? [];
        $response["data"] = $products;
    } else {
        $response["message"] = "0 product found with this sales point id " . $sales_point_id;
    }








    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
