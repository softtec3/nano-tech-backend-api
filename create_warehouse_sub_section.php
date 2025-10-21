<?php
require_once("./db_connect.php");
require_once("./auth_admin_only.php");
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
    $sql = "CREATE TABLE IF NOT EXISTS warehouse_subsections(
	id int AUTO_INCREMENT PRIMARY KEY,
    warehouse_id int NOT NULL,
    section_id int NOT NULL,
    sub_section_name varchar(100) DEFAULT NULL,
    created_at timestamp  DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (section_id) REFERENCES warehouse_sections(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses (id) ON DELETE CASCADE ON UPDATE CASCADE
)";

    if (!$conn->query($sql)) {
        throw new Exception("Error creating table " . $conn->error);
    }

    $sub_section_data = json_decode(file_get_contents("php://input"), true);

    // save data to database
    $warehouse_id = (int) $sub_section_data["warehouse_id"] ?? 0;
    $section_id = (int) $sub_section_data["section_id"] ?? 0;
    $sub_section_name = $sub_section_data["sub_section_name"] ?? "";

    if ($warehouse_id == 0 || $section_id == 0 || empty($sub_section_name)) {
        throw new Exception("All fields are required");
    }

    $stmt = $conn->prepare("INSERT INTO warehouse_subsections (warehouse_id, section_id, sub_section_name) VALUES (?,?,?)");
    if (!$stmt) {
        throw new Exception("SQL failed: " . $conn->error);
    }
    $stmt->bind_param("iis", $warehouse_id, $section_id, $sub_section_name);

    if (!$stmt->execute()) {
        throw new Exception("Failed to insert warehouse_subsections table " . $stmt->error);
    }



    $response["success"] = true;
    $response["message"] = "Sub section successfully created";
    $response["data"] = ["insert_id" => $stmt->insert_id];

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
