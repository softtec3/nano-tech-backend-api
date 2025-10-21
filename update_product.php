<?php
require_once("./db_connect.php");
require_once("./auth_admin_only.php");
$response = [
    "success" => false,
    "message" => "",
    "data" => []
];
// upload file and get name
function upload_file_get_name($name)
{
    // Check if the file input exists and a file was uploaded
    if (isset($_FILES[$name]) && $_FILES[$name]['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "./uploads/products/";
        $fileName = basename($_FILES[$name]["name"]);
        $targetPath = $uploadDir . $fileName;

        // Move the uploaded file
        if (move_uploaded_file($_FILES[$name]["tmp_name"], $targetPath)) {
            return $fileName;
        } else {
            // Failed to move file
            return null;
        }
    } else {
        // No file uploaded or some error occurred
        return null;
    }
}

try {

    if (!isset($_GET["product_id"]) || $_GET["product_id"] == "") {
        throw new Exception("?product_id= needed");
    }
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Invalid request method. Must be POST request");
    }
    // extract all information
    $product_name_en = $_POST["product_name_en"] ?? "";
    $product_name_bn = $_POST["product_name_bn"] ?? "";
    $regular_price = (int) $_POST["regular_price"] ?? 0;
    $discount = (int) $_POST["discount"] ?? 0;
    $current_price = (int) $_POST["current_price"] ?? 0;
    $product_model = $_POST["product_model"] ?? "";
    $raw_product_category = $_POST["product_category"];
    $product_category = explode("+", $raw_product_category)[1] ?? NULL;
    $product_category_id = explode("+", $raw_product_category)[0] ?? NULL;
    $raw_product_sub_category = $_POST["product_sub_category"];
    $product_sub_category = explode("+", $raw_product_sub_category)[1] ?? NULL;
    $product_sub_category_id = explode("+", $raw_product_sub_category)[0] ?? NULL;
    $raw_warehouse = $_POST["warehouse_name"];
    $warehouse_name = explode("+", $raw_warehouse)[1] ?? NULL;
    $warehouse_id = explode("+", $raw_warehouse)[0] ?? NULL;
    $warehouse_address = explode("+", $raw_warehouse)[2] ?? NULL;
    $raw_warehouse_section = $_POST["warehouse_section"];
    $warehouse_section_name = explode("+", $raw_warehouse_section)[1] ?? NULL;
    $warehouse_section_id = explode("+", $raw_warehouse_section)[0] ?? NULL;
    $raw_warehouse_sub_section = $_POST["warehouse_sub_section"];
    $warehouse_sub_section_name = explode("+", $raw_warehouse_sub_section)[1] ?? NULL;
    $warehouse_sub_section_id = explode("+", $raw_warehouse_sub_section)[0] ?? NULL;
    $product_quantity = (int) $_POST["product_quantity"] ?? 0;
    $product_warranty = $_POST["product_warranty"] ?? "";
    $product_main_img = upload_file_get_name("product_main_img") ?? $_POST["pro_main_img"];
    $product_img_one = upload_file_get_name("product_img_one") ?? $_POST["pro_img_one"];
    $product_img_two = upload_file_get_name("product_img_two") ?? $_POST["pro_img_two"];
    $product_img_three = upload_file_get_name("product_img_three") ?? $_POST["pro_img_three"];
    $product_img_four = upload_file_get_name("product_img_four") ?? $_POST["pro_img_four"];
    $product_description_en = $_POST["product_description_en"] ?? "";
    $product_description_bn = $_POST["product_description_bn"] ?? "";
    $id = (int) $_GET["product_id"] ?? 0;


    //save data to database
    $stmt = $conn->prepare("UPDATE products SET 
        product_name_en = ?, 
        product_name_bn = ?, 
        regular_price = ?, 
        discount = ?, 
        current_price = ?, 
        product_model = ?, 
        raw_product_category = ?, 
        product_category = ?, 
        product_category_id = ?, 
        raw_product_sub_category = ?, 
        product_sub_category = ?, 
        product_sub_category_id = ?, 
        raw_warehouse = ?, 
        warehouse_name = ?, 
        warehouse_id = ?, 
        warehouse_address = ?, 
        raw_warehouse_section = ?, 
        warehouse_section_name = ?, 
        warehouse_section_id = ?, 
        raw_warehouse_sub_section = ?, 
        warehouse_sub_section_name = ?, 
        warehouse_sub_section_id = ?, 
        product_quantity = ?, 
        product_warranty = ?, 
        product_main_img = ?, 
        product_img_one = ?, 
        product_img_two = ?, 
        product_img_three = ?, 
        product_img_four = ?, 
        product_description_en = ?, 
        product_description_bn = ?
         WHERE id = ?
");


    if (!$stmt) {
        throw new Exception("SQL products failed: " . $conn->error);
    }
    $stmt->bind_param(
        "sssssssssssssssssssssssssssssssi",
        $product_name_en,
        $product_name_bn,
        $regular_price,
        $discount,
        $current_price,
        $product_model,
        $raw_product_category,
        $product_category,
        $product_category_id,
        $raw_product_sub_category,
        $product_sub_category,
        $product_sub_category_id,
        $raw_warehouse,
        $warehouse_name,
        $warehouse_id,
        $warehouse_address,
        $raw_warehouse_section,
        $warehouse_section_name,
        $warehouse_section_id,
        $raw_warehouse_sub_section,
        $warehouse_sub_section_name,
        $warehouse_sub_section_id,
        $product_quantity,
        $product_warranty,
        $product_main_img,
        $product_img_one,
        $product_img_two,
        $product_img_three,
        $product_img_four,
        $product_description_en,
        $product_description_bn,
        $id
    );


    if (!$stmt->execute()) {
        throw new Exception("Update failed on products table: " . $stmt->error);
    }

    $response["success"] = true;
    $response["message"] = "Product updated successfully";
    // specifications
    $specification_ids =   $_POST["spec_id"];
    $specification_names_bn =   $_POST["sp_name_bn"];
    $specification_descriptions_bn =   $_POST["sp_desc_bn"];
    $specification_names_en =   $_POST["sp_name_en"];
    $specification_descriptions_en =   $_POST["sp_desc_en"];


    foreach ($specification_ids as $idx => $spec_id) {
        $specification_name_bn = $specification_names_bn[$idx];
        $specification_description_bn = $specification_descriptions_bn[$idx];
        $specification_name_en = $specification_names_en[$idx];
        $specification_description_en = $specification_descriptions_en[$idx];

        $stmt = $conn->prepare(" UPDATE product_specifications SET 
                        specification_name_en = ?, 
                        specification_description_en = ?, 
                        specification_name_bn = ?, 
                        specification_description_bn = ?
                        WHERE id = ?
                         ");

        if (!$stmt) {
            throw new Exception("SQL specification failed: " . $conn->error);
        }
        $stmt->bind_param(
            "ssssi",
            $specification_name_en,
            $specification_description_en,
            $specification_name_bn,
            $specification_description_bn,
            $spec_id
        );


        if (!$stmt->execute()) {
            throw new Exception("Update failed into product_specifications: " . $stmt->error);
        }
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
