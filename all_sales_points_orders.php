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

    $stmt = $conn->prepare("SELECT * FROM sales_point_order_items ORDER BY id DESC");
    if (!$stmt) {
        throw new Exception("SQL failed: " . $conn->error);
    }

    if (!$stmt->execute()) {
        throw new Exception("Fetching failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        foreach ($orders as $idx => $order) {
            $sales_point_id = $order["sales_point_id"] ?? 0;
            $product_id = $order["product_id"] ?? 0;
            // get sales point name
            $stmt2 = $conn->prepare("SELECT name FROM sales_points WHERE id=?");
            if (!$stmt2) {
                throw new Exception("SQL failed sales_points: " . $conn->error);
            }
            $stmt2->bind_param("i", $sales_point_id);
            if (!$stmt2->execute()) {
                throw new Exception("Fetching failed: " . $stmt2->error);
            }
            $result2 = $stmt2->get_result();
            $sales_point_name = $result2->fetch_assoc()["name"];
            $orders[$idx]["sales_point_name"] = $sales_point_name;

            // get product name
            $stmt3 = $conn->prepare("SELECT product_name_en FROM products WHERE id=?");
            if (!$stmt3) {
                throw new Exception("SQL failed products: " . $conn->error);
            }
            $stmt3->bind_param("i", $product_id);
            if (!$stmt3->execute()) {
                throw new Exception("Fetching failed products: " . $stmt3->error);
            }
            $result3 = $stmt3->get_result();
            $product_name = $result3->fetch_assoc()["product_name_en"];
            $orders[$idx]["product_name"] = $product_name;
        }




        $response["success"] = true;
        $response["message"] = "Fetching successful";
        $response["data"] = $orders;
    } else {
        throw new Exception("0 order found");
    }







    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
