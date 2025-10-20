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
    $sql = "CREATE TABLE IF NOT EXISTS warehouse_sections(
	id int AUTO_INCREMENT PRIMARY KEY,
    warehouse_id int NOT NULL,
    section_name varchar(100) DEFAULT NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE ON UPDATE CASCADE
)";

    if (!$conn->query($sql)) {
        throw new Exception("Error creating table" . $conn->error);
    }

    $section_data = json_decode(file_get_contents("php://input"), true);

    // save data to database
    $warehouse_id = (int) $section_data["warehouse_id"] ?? 0;
    $section_name = $section_data["section_name"] ?? "";

    if ($warehouse_id == 0 || empty($section_name)) {
        throw new Exception("All fields are required");
    }
    $stmt = $conn->prepare("INSERT INTO warehouse_sections(warehouse_id, section_name) VALUES (?,?)");
    if (!$stmt) {
        throw new Exception("SQL failed" . $conn->error);
    }
    $stmt->bind_param("is", $warehouse_id, $section_name);
    if (!$stmt->execute()) {
        throw new Exception("Insert error on warehouse_sections table " . $stmt->error);
    }

    $response["success"] = true;
    $response["message"] = "Section created successfully";
    $response["data"] = ["insert_id" =>  $stmt->insert_id];


    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
