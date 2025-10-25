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
        $uploadDir = "./uploads/banners/";
        $fileName = basename($_FILES[$name]["name"]);
        $saved_file_name = "uploads/banners/" . $fileName;
        $targetPath = $uploadDir . $fileName;

        // Move the uploaded file
        if (move_uploaded_file($_FILES[$name]["tmp_name"], $targetPath)) {
            return $saved_file_name;
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

    $sql = "CREATE TABLE IF NOT EXISTS banners(
	id int AUTO_INCREMENT PRIMARY KEY,
    banner_image TEXT DEFAULT NULL,
    banner_link TEXT DEFAULT NULL,
    banner_section ENUM('main','rectangle_two','grid','rectangle_three') DEFAULT 'main',
    status ENUM('active','inactive') DEFAULT 'active',
    created_at timestamp DEFAULT CURRENT_TIMESTAMP
    )";
    if (!$conn->query($sql)) {
        throw new Exception("Failed to create banner table");
    }

    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Invalid request method. Must be POST request");
    }
    $banner_image = upload_file_get_name("banner_image");
    $banner_section = $_POST["banner_section"];

    if ($banner_image == null || empty($banner_section)) {
        throw new Exception("All fields are required");
    }
    $stmt = $conn->prepare("INSERT INTO banners(banner_image, banner_section) VALUES (?,?)");
    if (!$stmt) {
        throw new Exception("SQL failed banners " . $conn->error);
    }
    $stmt->bind_param("ss", $banner_image, $banner_section);

    if (!$stmt->execute()) {
        throw new Exception("Failed to insert on banner " . $stmt->error);
    }
    if ($stmt->insert_id) {
        $response["success"] = true;
        $response["message"] = "Successfully added banner";
        $response["data"] = ["insert_id" => $stmt->insert_id];
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
