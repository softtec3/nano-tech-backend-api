<?php
require_once("./db_connect.php");
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
    // table creation
    $sql = "CREATE TABLE IF NOT EXISTS products(
	id int AUTO_INCREMENT PRIMARY KEY,
    product_name_en varchar(255) DEFAULT NULL,
    product_name_bn varchar(255) DEFAULT NULL,
    regular_price int DEFAULT 0,
    discount int DEFAULT 0,
    current_price int DEFAULT 0,
    product_model varchar(100) DEFAULT NULL,
    raw_product_category varchar(100) DEFAULT NULL,
    product_category varchar(100) DEFAULT NULL,
    product_category_id int DEFAULT NULL,
    raw_product_sub_category varchar(100) DEFAULT NULL,
    product_sub_category varchar(100) DEFAULT NULL,
    product_sub_category_id int DEFAULT NULL,
    raw_warehouse varchar(255) DEFAULT NULL,
    warehouse_name varchar(100) DEFAULT NULL,
    warehouse_id int DEFAULT NULL,
    warehouse_address TEXT DEFAULT NULL,
    raw_warehouse_section varchar(255) DEFAULT NULL,
    warehouse_section_name varchar(100) DEFAULT NULL,
    warehouse_section_id int DEFAULT NULL,
    raw_warehouse_sub_section varchar(255) DEFAULT NULL,
    warehouse_sub_section_name varchar(100) DEFAULT NULL,
    warehouse_sub_section_id int DEFAULT NULL,
    product_quantity int DEFAULT 0,
    product_warranty varchar(100) DEFAULT NULL,
    product_main_img TEXT DEFAULT NULL,
    product_img_one TEXT DEFAULT NULL,
    product_img_two TEXT DEFAULT NULL,
    product_img_three TEXT DEFAULT NULL,
    product_img_four TEXT DEFAULT NULL,
    product_description_en TEXT DEFAULT NULL,
    product_description_bn TEXT DEFAULT NULL,
    marker varchar(100) DEFAULT NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS product_specifications(
	id int AUTO_INCREMENT PRIMARY KEY,
    product_id int NOT NULL,
    specification_name_en varchar(100) DEFAULT NULL,
    specification_description_en TEXT DEFAULT NULL,
    specification_name_bn varchar(100) DEFAULT NULL,
    specification_description_bn TEXT DEFAULT NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE ON UPDATE CASCADE
);
";
    if (!$conn->multi_query($sql)) {
        throw new Exception("Error creating products or product_specification table: " . $conn->error);
    }
    while ($conn->more_results() && $conn->next_result()) {
        $result = $conn->store_result();
        if ($result) {
            $result->free();
        }
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
    $product_main_img = upload_file_get_name("product_main_img");
    $product_img_one = upload_file_get_name("product_img_one");
    $product_img_two = upload_file_get_name("product_img_two");
    $product_img_three = upload_file_get_name("product_img_three");
    $product_img_four = upload_file_get_name("product_img_four");
    $product_description_en = $_POST["product_description_en"] ?? "";
    $product_description_bn = $_POST["product_description_bn"] ?? "";


    //save data to database
    $stmt = $conn->prepare("INSERT INTO products (product_name_en, product_name_bn, regular_price,	discount, current_price, product_model, raw_product_category, product_category,	product_category_id,	raw_product_sub_category, product_sub_category,	product_sub_category_id, raw_warehouse,	warehouse_name,	warehouse_id, warehouse_address, raw_warehouse_section,	warehouse_section_name,	warehouse_section_id,	raw_warehouse_sub_section, warehouse_sub_section_name, warehouse_sub_section_id, product_quantity,	product_warranty, product_main_img,	product_img_one, product_img_two, product_img_three,	product_img_four, product_description_en, product_description_bn) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

    if (!$stmt) {
        throw new Exception("SQL products failed: " . $conn->error);
    }
    $stmt->bind_param("ssiiisssssssssssssssssissssssss", $product_name_en, $product_name_bn, $regular_price, $discount, $current_price, $product_model, $raw_product_category, $product_category, $product_category_id, $raw_product_sub_category, $product_sub_category, $product_sub_category_id, $raw_warehouse, $warehouse_name, $warehouse_id, $warehouse_address, $raw_warehouse_section, $warehouse_section_name, $warehouse_section_id, $raw_warehouse_sub_section, $warehouse_sub_section_name, $warehouse_sub_section_id, $product_quantity, $product_warranty, $product_main_img, $product_img_one, $product_img_two, $product_img_three, $product_img_four, $product_description_en, $product_description_bn);

    if (!$stmt->execute()) {
        throw new Exception("Insert to failed on products table: " . $stmt->error);
    }
    $insert_id = $stmt->insert_id;

    // specifications
    $specification_names_bn =   $_POST["sp_name_bn"];
    $specification_descriptions_bn =   $_POST["sp_desc_bn"];
    $specification_names_en =   $_POST["sp_name_en"];
    $specification_descriptions_en =   $_POST["sp_desc_en"];

    foreach ($specification_names_bn as $idx => $bn_name) {
        $specification_name_bn = $specification_names_bn[$idx];
        $specification_description_bn = $specification_descriptions_bn[$idx];
        $specification_name_en = $specification_names_en[$idx];
        $specification_description_en = $specification_descriptions_en[$idx];

        $stmt = $conn->prepare("INSERT INTO product_specifications (product_id, specification_name_en, specification_description_en, specification_name_bn, specification_description_bn) VALUES (?,?,?,?,?)");
        if (!$stmt) {
            throw new Exception("SQL specification failed: " . $conn->error);
        }
        $stmt->bind_param("issss", $insert_id, $specification_name_en, $specification_description_en, $specification_name_bn, $specification_description_bn);

        if (!$stmt->execute()) {
            throw new Exception("Insert failed into product_specifications: " . $stmt->error);
        }
    }

    $response["success"] = true;
    $response["message"] = "Product added successfully";
    $response["data"] = ["product" => $insert_id, "specification" => $stmt->insert_id];

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
