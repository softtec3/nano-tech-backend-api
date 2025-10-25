<?php
require_once("./db_connect.php");
require_once("./auth_admin_only.php");
$response = [
    "success" => false,
    "message" => "",
    "data" => []
];


try {

    $sql = "CREATE TABLE IF NOT EXISTS products_barcodes(
	id int AUTO_INCREMENT PRIMARY KEY,
    product_id int NOT NULL,
    barcode varchar(100) UNIQUE DEFAULT NULL,
    status ENUM('created','assign','sold') DEFAULT 'created',
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP
    )";
    if (!$conn->query($sql)) {
        throw new Exception("Error creating products_barcodes table " . $conn->error);
    }
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Invalid request method. Must be POST request");
    }

    $data = json_decode(file_get_contents("php://input"), true);

    $product_id = (int) $data["product_id"] ?? 0;
    $barcodes = $data["barcodes"] ?? [];

    if (!$data["product_id"] || empty($barcodes)) {
        throw new Exception("Product id and bar codes must needed");
    }

    foreach ($barcodes as $code) {
        $stmt = $conn->prepare("INSERT INTO products_barcodes (product_id,barcode) VALUES (?,?)");
        if (!$stmt) {
            throw new Exception("SQL failed: " . $conn->error);
        }
        $stmt->bind_param("is", $product_id, $code);
        if (!$stmt->execute()) {
            throw new Exception("Data insert failed " . $stmt->error);
        }
    }
    $response["success"] = true;
    $response["message"] = "Barcodes successfully created";

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
