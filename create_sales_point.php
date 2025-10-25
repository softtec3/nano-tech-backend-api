<?php
require_once("./db_connect.php");
require_once("./auth_admin_only.php");
$response = [
    "success" => false,
    "message" => "",
    "data" => []
];


try {

    $sql = "CREATE TABLE IF NOT EXISTS sales_points(
	id int AUTO_INCREMENT PRIMARY KEY,
    name varchar(255) DEFAULT NULL,
    location TEXT DEFAULT NULL,
    owner_name varchar(100) DEFAULT NULL,
    phone_number varchar(20) DEFAULT NULL,
    owner_nid varchar(20) UNIQUE DEFAULT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at timestamp DEFAULT CURRENT_TIMESTAMP
    )";

    if (!$conn->query($sql)) {
        throw new Exception("Error creating sales_points table: " . $conn->error);
    }

    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Invalid request method. Must be POST request");
    }

    $data = json_decode(file_get_contents("php://input"), true);
    $name = trim($data["name"]) ?? "";
    $location = trim($data["location"]) ?? "";
    $owner_nid = trim($data["owner_nid"]) ?? "";
    $owner_name = trim($data["owner_name"]) ?? "";
    $phone_number = trim($data["phone_number"]) ?? "";

    if (empty($name) || empty($location) || empty($owner_nid) || empty($owner_name)  || empty($phone_number)) {
        throw new Exception("All fields are required");
    }

    $stmt = $conn->prepare("INSERT INTO sales_points (name, location, owner_name, phone_number, owner_nid) VALUES (?,?,?,?,?)");
    if (!$stmt) {
        throw new Exception("Exception failed " . $conn->error);
    }
    $stmt->bind_param("sssss", $name, $location, $owner_name, $phone_number, $owner_nid);

    if (!$stmt->execute()) {
        throw new Exception("Error inserting on sales_points table " . $stmt->error);
    }

    if ($stmt->insert_id) {
        $response["success"] = true;
        $response["message"] = "Sales point created successfully";
        $response["data"] = ["insert_id" => $stmt->insert_id];
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
