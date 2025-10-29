<?php
require_once("./db_connect.php");
require_once("./auth_user.php");
$response = [
    "success" => false,
    "message" => "",
    "data" => []
];


try {
    if ($_SERVER["REQUEST_METHOD"] != "GET") {
        throw new Exception("Invalid request method. Must be GET request");
    }

    if (!isset($_GET["user_id"])) {
        throw new Exception("?user_id= is needed");
    }

    $user_id = (int) $_GET["user_id"] ?? 0;

    $stmt = $conn->prepare("SELECT * FROM customer_orders WHERE user_id=? ORDER by id DESC");
    if (!$stmt) {
        throw new Exception("SQL failed: " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Fetching failed:  " . $stmt->error);
    }
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row =  $result->fetch_assoc()) {
            $user_orders[] = $row;
        }
        $response["success"] = true;
        $response["message"] = "Fetching successful";
        $response["data"] = $user_orders;
    } else {
        $response["message"] = "No order found with is user id " . $user_id;
    }






    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
