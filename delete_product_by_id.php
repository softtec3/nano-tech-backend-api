<?php
require_once("./db_connect.php");
$response = [
    "success" => false,
    "message" => "",
    "data" => []
];


try {
    if ($_SERVER["REQUEST_METHOD"] != "GET") {
        throw new Exception("Invalid request method. Must be GET request");
    }
    $delete_id = $_GET["delete_id"] ?? 0;

    if (empty($delete_id)) {
        throw new Exception("?delete_id= is needed");
    }
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    if (!$stmt) {
        throw new Exception("SQL failed " . $conn->error);
    }
    $stmt->bind_param("s", $delete_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete: " . $stmt->error);
    }
    if ($stmt->affected_rows > 0) {
        $response["success"] = true;
        $response["message"] = "Successfully deleted";
    } else {
        $response["success"] = true;
        $response["message"] = "No data found with id $delete_id";
    }




    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
