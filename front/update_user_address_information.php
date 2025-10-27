<?php
require_once("./db_connect.php");
require_once("./auth_user.php");
$response = [
    "success" => false,
    "message" => "",
    "data" => []
];


try {
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Invalid request method. Must be POST request");
    }
    if (!isset($_GET["user_name"]) || $_GET["user_name"] == "") {
        throw new Exception("?user_name= is needed");
    }
    $user_name = trim($_GET["user_name"]);

    // user data
    $data = json_decode(file_get_contents("php://input"), true);

    $address = $data["address"] ?? "";
    $address_label = $data["address_label"] ?? "home";
    $area = $data["area"] ?? "";
    $full_name = $data["full_name"] ?? "";
    $landmark = $data["landmark"] ?? "";
    $mobile_number = $data["mobile_number"] ?? "";

    if (empty($address) || empty($address_label) || empty($area) || empty($full_name) || empty($landmark) || empty($mobile_number)) {
        $response["message"] = "all fields are required";
    }

    $stmt = $conn->prepare("UPDATE users_information SET full_name=?, mobile_number=?, address_label=?, area=?, address=?, landmark=? WHERE user_name=?");
    if (!$stmt) {
        throw new Exception("SQL failed: " . $conn->error);
    }

    $stmt->bind_param("sssssss", $full_name, $mobile_number, $address_label, $area, $address, $landmark, $user_name);
    if (!$stmt->execute()) {
        throw new Exception("Update failed: " . $stmt->error);
    }
    if ($stmt->affected_rows > 0) {
        $response["success"] = true;
        $response["message"] = "Successfully updated";
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
