<?php
require_once("./db_connect.php");
require_once("./auth_admin_only.php");
$response = [
    "success" => false,
    "message" => "",
    "data" => []
];


try {
    if ($_SERVER["REQUEST_METHOD"] != "GET") {
        throw new Exception("Invalid request method. Must be GET request");
    }

    $stmt = $conn->prepare("SELECT id, full_name, user_name, role FROM general_users ORDER BY id DESC");
    if (!$stmt) {
        throw new Exception("SQL failed " . $conn->error);
    }

    if (!$stmt->execute()) {
        throw new Exception("Fetching failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $response["success"] = true;
        $response["message"] = "Fetching successful";
        $response["data"] = $users;
    } {
        $response["message"] = "0 user found";
    }


    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
