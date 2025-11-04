<?php
require_once("./db_connect.php");
require_once("./auth_user.php");
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
        $uploadDir = "../uploads/users/";
        $fileName = basename($_FILES[$name]["name"]);
        $saved_file_name = "uploads/users/" . $fileName;
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
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Invalid request method. Must be POST request");
    }
    if (!isset($_GET["user_id"]) || $_GET["user_id"] == "") {
        throw new Exception("?user_id= must needed");
    }

    $user_id = $_GET["user_id"] ?? 0;
    $address = $_POST["address"] ?? "";
    $address_label = $_POST["address_label"] ?? "";
    $area = $_POST["area"] ?? "";
    $full_name = $_POST["full_name"] ?? "";
    $landmark = $_POST["landmark"] ?? "";
    $mobile_number = $_POST["mobile_number"] ?? "";
    $image = upload_file_get_name("image") ?? null;


    if (empty($image) || $image == null) {
        $stmt = $conn->prepare("UPDATE users_information SET full_name=?, mobile_number=?, address_label=?, area=?, address=?, landmark=? WHERE user_id=?");
        if (!$stmt) {
            throw  new Exception("SQL failed: " . $conn->error);
        }
        $stmt->bind_param("ssssssi", $full_name, $mobile_number, $address_label, $area, $address, $landmark, $user_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update: " . $stmt->error);
        }
        if ($stmt->affected_rows > 0) {
            $response["success"] = true;
            $response["message"] = "Profile Successfully Updated";
        } else {
            $response["success"] = true;
            $response["message"] = "Not updated anything";
        }
    } else {
        $stmt = $conn->prepare("UPDATE users_information SET full_name=?, mobile_number=?, address_label=?, area=?, address=?, landmark=?, image=? WHERE user_id=?");
        if (!$stmt) {
            throw  new Exception("SQL failed: " . $conn->error);
        }
        $stmt->bind_param("sssssssi", $full_name, $mobile_number, $address_label, $area, $address, $landmark, $image, $user_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update: " . $stmt->error);
        }
        if ($stmt->affected_rows > 0) {
            $response["success"] = true;
            $response["message"] = "Profile Successfully Updated";
        } else {
            $response["success"] = true;
            $response["message"] = "Not updated anything";
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
