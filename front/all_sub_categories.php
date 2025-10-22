<?php
require_once("./db_connect.php");
$response = [
    "success" => false,
    "message" => "",
    "data" => []
];

try {
    if ($_SERVER["REQUEST_METHOD"] != "GET") {
        throw new Exception("Must be a get request");
    }
    $lang = $_GET["lang"] ?? "en";
    $sub_cat_field = $lang == "bn" ? "sub_category_bn" : "sub_category_en";
    // get all sub categories
    $stmt = $conn->prepare("SELECT id, $sub_cat_field AS sub_category_name,category_id FROM sub_categories");
    if (!$stmt) {
        throw new Exception("SQL failed " . $conn->error);
    }
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert into subcategories" . $stmt->error);
    }
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $sub_categories[] = $row;
        }
        $response["success"] = true;
        $response["data"] = $sub_categories;
    } else {
        $response["success"] = false;
        $response["message"] = "No sub category found with this id " . $category_id;
    }
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
