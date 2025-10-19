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
        $uploadDir = "./uploads/categories/";
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
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Invalid request method. Must be POST request");
    }

    // Collect form data (from FormData)
    $category_en = $_POST["category_en"] ?? "";
    $category_bn = $_POST["category_bn"] ?? "";

    if (empty($category_en) || empty($category_bn)) {
        throw new Exception("All fields are required");
    }

    // Handle file upload
    $category_image = upload_file_get_name("category_image");

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO categories (category_image, category_en, category_bn) VALUES (?, ?, ?)");
    if (!$stmt) {
        throw new Exception("SQL prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sss", $category_image, $category_en, $category_bn);

    if (!$stmt->execute()) {
        throw new Exception("Error inserting category: " . $stmt->error);
    }

    $response["success"] = true;
    $response["message"] = "Category created successfully";
    $response["data"] = ["insert_id" => $stmt->insert_id];

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
