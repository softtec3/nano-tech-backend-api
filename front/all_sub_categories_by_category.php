<?php
require_once("./db_connect.php");
$response = [
    "success" => false,
    "message" => "",
    "data" => []
];

try {
    if (isset($_GET["category_id"]) && $_GET["category_id"] != "") {
        $category_id = (int) $_GET["category_id"];
        $lang = $_GET["lang"] ?? "en";
        $sub_cat_field = $lang == "bn" ? "sub_category_bn" : "sub_category_en";
        // get all sub categories
        $stmt = $conn->prepare("SELECT id, $sub_cat_field AS sub_category_name FROM sub_categories WHERE category_id=?");
        if (!$stmt) {
            throw new Exception("SQL failed " . $conn->error);
        }
        $stmt->bind_param("i", $category_id);
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
    } else {
        $response["message"] = "?category_id= is needed";
    }
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
