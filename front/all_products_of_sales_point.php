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

    $stmt = $conn->prepare("SELECT product_id FROM sales_points_products_summary WHERE sales_point_id=? AND current_quantity>0");
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
        $products = array_unique($pro) ?? [];
        $final_array = "'" . implode("','", $products) . "'";
        $lang = $_GET["lang"] ?? "en";
        $product_name_field = $lang === 'bn' ? 'product_name_bn' : 'product_name_en';
        $product_description_field = $lang === 'bn' ? 'product_description_bn' : 'product_description_en';

        $stmt2 = $conn->prepare("SELECT id, $product_name_field AS product_name, regular_price, discount,current_price,delivery_charge, product_model, raw_product_category,product_category,product_category_id,raw_product_sub_category,product_sub_category,product_sub_category_id,raw_warehouse,warehouse_name,warehouse_id,warehouse_address,raw_warehouse_section,warehouse_section_name,warehouse_section_id,raw_warehouse_sub_section,warehouse_sub_section_name,warehouse_sub_section_id,product_quantity,product_warranty,product_main_img,product_img_one,product_img_two,product_img_three,product_img_four, $product_description_field AS product_description,marker,created_at FROM products WHERE id IN ($final_array) AND product_quantity>0");
        if (!$stmt2) {
            throw new Exception("SQL failed products table" . $conn->error);
        }
        if (!$stmt2->execute()) {
            throw new Exception("Execution failed products table " . $stmt2->error);
        }

        $result2 = $stmt2->get_result();
        if ($result2 && $result2->num_rows > 0) {
            while ($row = $result2->fetch_assoc()) {
                $all_products[] = $row;
            }
            $response["success"] = true;
            $response["message"] = "Fetching successful";
            $response["data"] = $all_products;
        }
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
