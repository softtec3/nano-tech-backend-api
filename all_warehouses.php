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
    $stmt = $conn->prepare("SELECT id, warehouse_name, warehouse_location FROM warehouses");
    if (!$stmt) {
        throw new Exception("SQL failed" . $conn->error);
    }
    if (!$stmt->execute()) {
        throw new Exception("Warehouses fetching failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $warehouses[] = $row;
        }
        $response["success"] = true;
        $response["message"] = "Fetching successful";
        $response["data"] = $warehouses;
    } else {
        $response["success"] = false;
        $response["message"] = "0 warehouses found. Please create";
    }
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
