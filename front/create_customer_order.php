<?php
require_once("./db_connect.php");
require_once("./auth_user.php");
$response = [
    "success" => false,
    "message" => "",
    "data" => []
];


try {
    // customer_orders table
    $sql = "CREATE TABLE IF NOT EXISTS customer_orders(
	id int AUTO_INCREMENT PRIMARY KEY,
    user_id int NOT NULL,
    user_name varchar(200) DEFAULT NULL,
    full_name varchar(200) DEFAULT NULL,
    total_amount int NOT NULL,
    payment_method varchar(100) DEFAULT NULL,
    pickup_type ENUM('home','sales_point') DEFAULT 'home',
    order_status ENUM('pending','confirmed','shipped','delivered', 'rejected') DEFAULT 'pending',
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if (!$conn->query($sql)) {
        throw new Exception("Error creating customer_orders table: " . $conn->error);
    }
    // customer_order_items table
    $sql2 = "CREATE TABLE IF NOT EXISTS customer_order_items(
	id int AUTO_INCREMENT PRIMARY KEY,
    order_id int NOT NULL,
    product_id int NOT NULL,
    quantity int NOT NULL,
    price int NOT NULL,
    delivery_charge int NOT NULL,
    FOREIGN KEY (order_id) REFERENCES customer_orders(id) ON DELETE CASCADE ON UPDATE CASCADE
    )";
    if (!$conn->query($sql2)) {
        throw new Exception("Error creating customer_orders table: " . $conn->error);
    }

    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Invalid request method. Must be POST request");
    }
    $data = json_decode(file_get_contents("php://input"), true);

    // customer_orders table data
    $user_id = (int) $data["user_id"] ?? NULL;
    $user_name = $data["user_name"] ?? NULL;
    $full_name = $data["full_name"] ?? "";
    $payment_method = $data["payment_method"] ?? "";
    $cart = $data["cart"] ?? [];
    $total_amount = 0;

    if (empty($user_id) || empty($user_name) || empty($full_name) || empty($payment_method) || empty($cart)) {
        throw new Exception("user_id, user_name, full_name, payment_method, cart is needed");
    }

    if (count($cart) > 0) {
        foreach ($cart as $c) {
            $total_amount += $c["price"] * $c["quantity"] + $c["delivery_charge"];
        }
    } else {
        $response["message"] = "Your product cart is empty";
    }

    $stmt = $conn->prepare("INSERT INTO customer_orders(user_id, user_name, full_name,total_amount,payment_method) VALUES (?,?,?,?,?)");
    if (!$stmt) {
        throw new Exception("SQL failed: " . $conn->error);
    }
    $stmt->bind_param("issis", $user_id, $user_name, $full_name, $total_amount, $payment_method);

    if (!$stmt->execute()) {
        throw new Exception("Error to inert on customer_orders table: " . $stmt->error);
    }
    $order_id = $stmt->insert_id;

    foreach ($cart as $item) {
        $product_id = $item["product_id"];
        $quantity = $item["quantity"];
        $price = $item["price"];
        $delivery_charge = $item["delivery_charge"];
        $stmt2 = $conn->prepare("INSERT INTO customer_order_items (order_id, product_id, quantity, price, delivery_charge) VALUES(?,?,?,?,?)");
        if (!$stmt2) {
            throw new Exception("SQL failed customer_order_items " . $conn->error);
        }
        $stmt2->bind_param("iiiis", $order_id, $product_id, $quantity, $price, $delivery_charge);
        if (!$stmt2->execute()) {
            throw new Exception("Error to insert to customer_order_items" . $stmt2->error);
        }
    }
    $response["success"] = true;
    $response["message"] = "Order successfully placed";

    $stmt->close();
    $stmt2->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
