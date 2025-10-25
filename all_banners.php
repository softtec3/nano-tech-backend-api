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

    $stmt = $conn->prepare("SELECT * FROM banners ORDER BY id DESC");
    if (!$stmt) {
        throw new Exception("SQL failed: " . $conn->error);
    }
    if (!$stmt->execute()) {
        throw new Exception("Failed to fetch: " . $stmt->error);
    }
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $banners[] = $row;
        }
        $response["success"] = true;
        $response["message"] = "Fetch successful";
        $response["data"] = $banners;
    } else {
        $response["success"] = false;
        $response["message"] = "0 banner found";
    }









    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
