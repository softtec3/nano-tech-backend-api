<?php
require_once("./db_connect.php");
require_once("./auth_sales_point.php");
$response = [
    "success" => false,
    "message" => "",
    "data" => []
];


try {
    if ($_SERVER["REQUEST_METHOD"] != "GET") {
        throw new Exception("Invalid request method. Must be GET request");
    }

    if (!isset($_GET["sales_point_id"]) || $_GET["sales_point_id"] == "") {
        throw new Exception("?sales_point_id= must needed");
    }

    $sales_point_id = (int) $_GET["sales_point_id"] ?? 0;

    $stmt = $conn->prepare("SELECT * FROM sales_points_products_summary WHERE sales_point_id=? ORDER BY id DESC");

    if (!$stmt) {
        throw new Exception("SQL failed: " . $conn->error);
    }
    $stmt->bind_param("i", $sales_point_id);

    if (!$stmt->execute()) {
        throw new Exception("Fetching failed");
    }

    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        // get image
        foreach ($products as $idx => $product) {
            $id = $product["product_id"];
            $stmt2 = $conn->prepare("SELECT product_main_img FROM products WHERE id=?");
            if (!$stmt2) {
                throw new Exception("SQL failed products: " . $conn->error);
            }
            $stmt2->bind_param("i", $id);
            if (!$stmt2->execute()) {
                throw new Exception("failed to fetch" . $stmt2->error);
            }
            $result2 = $stmt2->get_result();
            if ($result2 && $result2->num_rows > 0) {
                $product_image = $result2->fetch_assoc()["product_main_img"];
            }
            $products[$idx]["product_image"] = $product_image;
        }


        $response["success"] = true;
        $response["message"]  = "Fetching successful";
        $response["data"] = $products;
    } else {
        $response["message"] = "0 products found";
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
