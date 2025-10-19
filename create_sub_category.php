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
    $sub_category_data = json_decode(file_get_contents("php://input"), true);

    // saving data to database
    $main_category_id = (int) $sub_category_data["mainCategory"];
    $subcategory_en = $sub_category_data["subcategory_en"] ?? "";
    $subcategory_bn = $sub_category_data["subcategory_bn"] ?? "";

    if (empty($main_category_id) || empty($subcategory_en) || empty($subcategory_bn)) {
        throw new Exception("All fields are required");
    }
    $stmt = $conn->prepare("INSERT INTO sub_categories (category_id, sub_category_en, sub_category_bn) VALUES (?,?,?)");
    if (!$stmt) {
        throw new Exception("SQL failed " . $conn->error);
    }
    $stmt->bind_param("iss", $main_category_id, $subcategory_en, $subcategory_bn);
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert into subcategories" . $stmt->error);
    }
    $response["success"] = true;
    $response["message"] = "Sub category created successfully";
    $response["data"] = ["insert_id" => $stmt->insert_id];

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
