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

    if (!isset($_GET["product_id"]) || $_GET["product_id"] == "") {
        throw new Exception("?product_id= must needed");
    }
    $product_id = (int) $_GET["product_id"] ?? 0;
    $status = "created";
    $stmt = $conn->prepare("SELECT * FROM products_barcodes WHERE product_id=? AND status=?");
    if (!$stmt) {
        throw new Exception("SQL failed: " . $conn->error);
    }
    $stmt->bind_param("is", $product_id, $status);
    if (!$stmt->execute()) {
        throw new Exception("Failed to fetch "  . $stmt->error);
    }
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $barcodes[] = $row;
        }
        $response["success"] = true;
        $response["message"] = "Fetching successful";
        $response["data"] = $barcodes;
    } else {
        $response["message"] = "0 barcode found";
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
