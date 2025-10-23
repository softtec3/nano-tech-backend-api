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

    if (!isset($product_id) && empty($_GET["product_id"])) {
        throw new Exception("?product_id= must needed");
    }
    $lang = $_GET["lang"] ?? "en";
    $product_name_field = $lang === 'bn' ? 'product_name_bn' : 'product_name_en';
    $product_description_field = $lang === 'bn' ? 'product_description_bn' : 'product_description_en';
    $product_id = (int) $_GET["product_id"] ?? 0;

    $stmt = $conn->prepare("SELECT id, $product_name_field AS product_name, regular_price, discount,current_price, product_model, raw_product_category,product_category,product_category_id,raw_product_sub_category,product_sub_category,product_sub_category_id,raw_warehouse,warehouse_name,warehouse_id,warehouse_address,raw_warehouse_section,warehouse_section_name,warehouse_section_id,raw_warehouse_sub_section,warehouse_sub_section_name,warehouse_sub_section_id,product_quantity,product_warranty,product_main_img,product_img_one,product_img_two,product_img_three,product_img_four, $product_description_field AS product_description,marker,created_at FROM products WHERE id=?");
    if (!$stmt) {
        throw new Exception("SQL failed in products find operation "  . $conn->error);
    }
    $stmt->bind_param("i", $product_id);
    if (!$stmt->execute()) {
        throw new Exception("Execution error: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $product = $row;
        }
        $response["success"] = true;
        $response["message"] = "Product found";
        $response["data"] = $product;
    } else {
        $response["success"] = false;
        $response["message"] = "Product not found with this id " . $product_id;
    }







    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
