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

    if (isset($_GET["warehouse_id"]) && $_GET["warehouse_id"] != "") {

        $warehouse_id = (int) $_GET["warehouse_id"] ?? 0;

        $stmt = $conn->prepare("SELECT * FROM warehouse_sections WHERE warehouse_id=?");
        if (!$stmt) {
            throw new Exception("SQL failed: " . $conn->error);
        }
        $stmt->bind_param("i", $warehouse_id);
        if (!$stmt->execute()) {
            throw new Exception("Fetching failed" . $stmt->error);
        }
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $all_sections[] = $row;
            }
            $response["success"] = true;
            $response["message"] = "Fetching successful";
            $response["data"] = $all_sections;
        } else {
            $response["success"] = false;
            $response["message"] = "0 section found. Please create";
        }



        $stmt->close();
        $conn->close();
    } else {
        $response["success"] = false;
        $response["message"] = "?warehouse_id= must need";
    }
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
