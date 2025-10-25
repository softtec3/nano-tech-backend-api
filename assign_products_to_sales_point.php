<?php
require_once("./db_connect.php");
require_once("./auth_admin_only.php");
$response = [
    "success" => false,
    "message" => "",
    "data" => []
];


try {
    $sql1 = "CREATE TABLE IF NOT EXISTS sales_points_products(
	id int AUTO_INCREMENT PRIMARY KEY,
    product_id int NOT NULL,
    product_name TEXT DEFAULT NULL,
    assign_id varchar(100) DEFAULT NULL,
    status ENUM('assigned', 'sold') DEFAULT 'assigned',
    sales_point_id int NOT NULL,
    sales_point_name varchar(255) NOT NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    sold_time timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (sales_point_id) REFERENCES sales_points(id) ON UPDATE CASCADE ON DELETE CASCADE
)";

    if (!$conn->query($sql1)) {
        throw new Exception("Error creating sales_points_products table " . $conn->error);
    }
    $sql2 = "CREATE TABLE IF NOT EXISTS sales_points_products_summary(
	id int AUTO_INCREMENT PRIMARY KEY,
    product_id int NOT NULL,
    product_name TEXT DEFAULT NULL,
    assign_products_quantity int DEFAULT 0,
    sales_point_id int NOT NULL,
    sales_point_name varchar(255) NOT NULL,
    assign_date timestamp DEFAULT CURRENT_TIMESTAMP
)";

    if (!$conn->query($sql2)) {
        throw new Exception("Error creating sales_points_products_summary table " . $conn->error);
    }


    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Invalid request method. Must be POST request");
    }

    $data = json_decode(file_get_contents("php://input"), true);

    $product_id = (int) $data["product_id"] ?? 0;
    $product_name = $data["product_name"] ?? "";
    $sales_point_id = (int) $data["sales_point_id"] ?? 0;
    $sales_point_name = $data["sales_point_name"] ?? "";
    $assign_product_quantity = (int) $data["assign_product_quantity"] ?? 0;
    $selectedIds = $data["selectedIds"];

    $stmt = $conn->prepare("INSERT INTO sales_points_products_summary (product_id,product_name,assign_products_quantity,sales_point_id,sales_point_name) VALUES (?,?,?,?,?)");
    if (!$stmt) {
        throw new Exception("SQL failed on sales_points_products_summary " . $conn->error);
    }
    $stmt->bind_param("isiis", $product_id, $product_name, $assign_product_quantity, $sales_point_id, $sales_point_name);
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert on sales_points_products_summary " . $stmt->error);
    }

    if (count($selectedIds) > 0) {
        foreach ($selectedIds as $id) {
            $status = "assign";
            $stmt2 = $conn->prepare("INSERT INTO sales_points_products(product_id, product_name, assign_id,	sales_point_id,sales_point_name) VALUES(?,?,?,?,?)");
            if (!$stmt2) {
                throw new Exception("SQL failed on sales_points_products " . $conn->error);
            }
            $stmt2->bind_param("issis", $product_id, $product_name, $id, $sales_point_id, $sales_point_name);
            if (!$stmt2->execute()) {
                throw new Exception("Failed to insert on sales_points_products " . $stmt2->error);
            }
            $stmt3 = $conn->prepare("UPDATE products_barcodes SET status=? WHERE barcode=?");
            if (!$stmt3) {
                throw new Exception("SQL failed on products_barcodes " . $conn->error);
            }
            $stmt3->bind_param("ss", $status, $id);
            if (!$stmt3->execute()) {
                throw new Exception("Failed to Update products_barcodes " . $stmt3->error);
            }
        }
    }
    $response["success"] = true;
    $response["message"] = "Assigned successfully";

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
