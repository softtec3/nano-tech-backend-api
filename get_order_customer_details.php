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

    if (!isset($_GET["user_id"]) || !isset($_GET["order_id"])) {
        throw new Exception("?user_id=&order_id= is needed");
    }

    $user_id = (int) $_GET["user_id"] ?? 0;
    $order_id = (int) $_GET["order_id"] ?? 0;

    if (empty($user_id) || empty($order_id)) {
        throw new Exception("user id and order id is needed");
    }
    $stmt = $conn->prepare("SELECT * FROM users_information WHERE user_id=?");
    if (!$stmt) {
        throw new Exception("SQL failed: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Fetching failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result && $result->num_rows) {
        while ($row = $result->fetch_assoc()) {
            $user = $row;
        }
    }

    $stmt2 = $conn->prepare("SELECT * FROM customer_order_items WHERE order_id=? ORDER BY id DESC");
    if (!$stmt2) {
        throw new Exception("SQL failed customer_order_items" . $conn->error);
    }

    $stmt2->bind_param("i", $order_id);

    if (!$stmt2->execute()) {
        throw new Exception("Fetch failed customer_order_items" . $stmt2->error);
    }
    $result2 = $stmt2->get_result();
    if ($result2 && $result2->num_rows) {
        while ($row = $result2->fetch_assoc()) {
            $orders[] = $row;
        }
    }


    foreach ($orders as $idx => $order) {
        $product_id = $order["product_id"];
        $product_name = "";
        $stmt3 = $conn->prepare("SELECT product_name_en FROM products WHERE id=?");
        if (!$stmt3) {
            throw new Exception("SQL failed products" . $conn->error);
        }
        $stmt3->bind_param("i", $product_id);
        if (!$stmt3->execute()) {
            throw new Exception("Fetch failed customer_order_items" . $stmt3->error);
        }
        $result3 = $stmt3->get_result();
        if ($result3 && $result3->num_rows) {
            while ($row = $result3->fetch_assoc()) {
                $product_name = $row;
            }
        }
        $orders[$idx]["product_name"] = $product_name["product_name_en"];
    }


    $response["success"] = true;
    $response["message"] = "Fetching successful";
    $response["data"] = ["user" => $user, "orders" => $orders];

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
