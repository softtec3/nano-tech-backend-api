<?php
require_once("./db_connect.php");
$response = [
    "success" => false,
    "message" => "",
    "data" => []
];

try {

    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Invalid request method. Must be POST request");
    }
    // create warehouse table
    $sql = "CREATE TABLE IF NOT EXISTS warehouses(
	id int AUTO_INCREMENT PRIMARY KEY,
    warehouse_name varchar(100) DEFAULT NULL,
    warehouse_location varchar(255) DEFAULT NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP
)";

    if (!$conn->query($sql)) {
        throw new Exception("Error creating table" . $conn->error);
    }

    $warehouse_data = json_decode(file_get_contents("php://input"), true);

    // save data to database
    $warehouse_name = $warehouse_data["warehouse_name"] ?? "";
    $warehouse_location = $warehouse_data["warehouse_location"] ?? "";

    if (empty($warehouse_name) && empty($warehouse_location)) {
        throw new Exception("All fields are required");
    }

    $stmt = $conn->prepare("INSERT INTO warehouses (warehouse_name, warehouse_location) VALUES (?,?)");
    if (!$stmt) {
        throw new Exception("SQL failed " . $conn->error);
    }
    $stmt->bind_param("ss", $warehouse_name, $warehouse_location);

    if (!$stmt->execute()) {
        throw new Exception("Error inserting on table warehouses " . $stmt->error);
    }

    $response["success"] = true;
    $response["message"] = "Warehouse successfully created";
    $response["data"] = ["insert_id" => $stmt->insert_id];

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
