<?php
require_once("./db_connect.php");
require_once("./auth_sales_point.php");
$response = [
    "success" => false,
    "message" => "",
    "data" => []
];

// upload file and get name
function upload_file_get_name($name)
{
    // Check if the file input exists and a file was uploaded
    if (isset($_FILES[$name]) && $_FILES[$name]['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "../uploads/salespoints/";
        $fileName = basename($_FILES[$name]["name"]);
        $saved_file_name = "uploads/salespoints/" . $fileName;
        $targetPath = $uploadDir . $fileName;

        // Move the uploaded file
        if (move_uploaded_file($_FILES[$name]["tmp_name"], $targetPath)) {
            return $saved_file_name;
        } else {
            // Failed to move file
            return null;
        }
    } else {
        // No file uploaded or some error occurred
        return null;
    }
}
try {
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Invalid request method. Must be POST request");
    }

    // sales_point_orders
    $sql = "CREATE TABLE IF NOT EXISTS sales_point_orders(
	id INT AUTO_INCREMENT PRIMARY KEY,
    sales_point_id int NOT NULL,
    total_payable_amount int DEFAULT 0,
    total_due_amount int DEFAULT 0,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(sales_point_id) REFERENCES sales_points(id) ON DELETE CASCADE ON UPDATE CASCADE
    )";
    if (!$conn->query($sql)) {
        throw new Exception("Error creating sales_point_orders table: " . $conn->error);
    }

    // sales_point_order_item
    $sql2 = "CREATE TABLE IF NOT EXISTS sales_point_order_items(
	id INT AUTO_INCREMENT PRIMARY KEY,
    order_id int NOT NULL,
    sales_point_id int NOT NULL,
    product_id int NOT NULL,
    discount_amount int DEFAULT 0,
    payable_amount int DEFAULT 0,
    payment_type ENUM('cash','installment') DEFAULT 'cash',
    price int DEFAULT 0,
    selected_id varchar(100) DEFAULT NULL,
    FOREIGN KEY(order_id) REFERENCES sales_point_orders(id) ON DELETE CASCADE ON UPDATE CASCADE
)";


    if (!$conn->query($sql2)) {
        throw new Exception("Error creating sales_point_order_items table: " . $conn->error);
    }

    // sales_point_order_customers
    $sql3 = "CREATE TABLE IF NOT EXISTS sales_point_order_customers(
	id INT AUTO_INCREMENT PRIMARY KEY,
    order_id int NOT NULL,
    customer_address varchar(255) DEFAULT NULL,
    customer_check_no varchar(255) DEFAULT NULL,
    customer_filled_check_photo TEXT DEFAULT NULL,
    customer_mobile varchar(20) DEFAULT NULL,
    customer_nid_back TEXT DEFAULT NULL,
    customer_nid_front TEXT DEFAULT NULL,
    customer_name varchar(100) DEFAULT NULL,
    customer_photo TEXT DEFAULT NULL,
    guarantor_mobile varchar(20) DEFAULT NULL,
    guarantor_nid_back TEXT DEFAULT NULL,
    guarantor_nid_front TEXT DEFAULT NULL,
    guarantor_name varchar(100) DEFAULT NULL,
    guarantor_photo TEXT DEFAULT NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(order_id) REFERENCES sales_point_orders(id) ON DELETE CASCADE ON UPDATE CASCADE
)";

    if (!$conn->query($sql3)) {
        throw new Exception("Error creating sales_point_order_customers table: " . $conn->error);
    }


    $customer_data = json_decode($_POST["customer_info"], true);
    $guarantor_data = json_decode($_POST["guarantor_info"], true);
    $carts_item = json_decode($_POST["carts_item"], true);
    $total_due_amount = (int) $_POST["totalDueAmount"] ?? 0;
    $total_payable_amount = (int) $_POST["totalPayableAmount"] ?? 0;
    if (!isset($_GET["sales_point_id"])) {
        throw new Exception("?sales_point_id= must needed");
    }
    $sales_point_id = (int) $_GET["sales_point_id"] ?? 0;

    // photos
    $customer_photo = upload_file_get_name("customer_photo");
    $customer_nid_front = upload_file_get_name("customer_nid_front");
    $customer_nid_back = upload_file_get_name("customer_nid_back");
    $customer_filled_check = upload_file_get_name("customer_filled_check");
    $guarantor_photo = upload_file_get_name("guarantor_photo");
    $guarantor_nid_front = upload_file_get_name("guarantor_nid_front");
    $guarantor_nid_back = upload_file_get_name("guarantor_nid_back");


    // customer information
    $customerAddress = $customer_data["customerAddress"] ?? null;
    $customerCheckNo = $customer_data["customerCheckNo"] ?? null;
    $customerMobile = $customer_data["customerMobile"] ?? null;
    $customerName = $customer_data["customerName"] ?? null;

    // guarantor information
    $guarantorMobile = $guarantor_data["guarantorMobile"] ?? null;
    $guarantorName = $guarantor_data["guarantorName"] ?? null;

    // save data to sales_point_orders table

    $stmt = $conn->prepare("INSERT INTO sales_point_orders(sales_point_id, total_payable_amount,total_due_amount) VALUES(?,?,?)");
    if (!$stmt) {
        throw new Exception("SQL failed: " . $conn->error);
    }
    $stmt->bind_param("iii", $sales_point_id, $total_payable_amount, $total_due_amount);
    if (!$stmt->execute()) {
        throw new Exception("Error to insert on sales_point_orders table: " . $stmt->error);
    }
    if ($stmt->insert_id) {
        $order_id = $stmt->insert_id;
        // save data to sales_point_order_customer table
        $stmt2 = $conn->prepare("INSERT INTO sales_point_order_customers(order_id,customer_address, customer_check_no, customer_filled_check_photo, customer_mobile, customer_nid_back, customer_nid_front, customer_name, customer_photo, guarantor_mobile, guarantor_nid_back, guarantor_nid_front, guarantor_name, guarantor_photo) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

        if (!$stmt2) {
            throw new Exception("SQL failed stmt2: " . $conn->error);
        }
        $stmt2->bind_param("isssssssssssss", $order_id, $customerAddress, $customerCheckNo, $customer_filled_check, $customerMobile, $customer_nid_back, $customer_nid_front, $customerName, $customer_photo, $guarantorMobile, $guarantor_nid_back, $guarantor_nid_front, $guarantorName, $guarantor_photo);
        if (!$stmt2->execute()) {
            throw new Exception("Error to insert ont sales_point_order_customers table: " . $stmt2->error);
        }
        if ($stmt2->insert_id) {
            // save data to  sales_point_order_items table
            foreach ($carts_item as $item) {
                $product_id = (int) $item["product_id"] ?? 0;
                $discount_amount = (int) $item["discountAmount"] ?? 0;
                $payable_amount = (int) $item["payableAmount"] ?? 0;
                $payment_type = $item["paymentType"] ?? "cash";
                $price = (int) $item["price"] ?? 0;
                $selected_id = $item["selectedId"] ?? null;
                // insert into sales_point_order_items table
                $stmt3 = $conn->prepare("INSERT INTO sales_point_order_items (order_id, sales_point_id, product_id,discount_amount, payable_amount, payment_type, price, selected_id) VALUES(?,?,?,?,?,?,?,?)");
                if (!$stmt3) {
                    throw new Exception("SQL failed: " . $conn->error);
                }
                $stmt3->bind_param("iiiiisis", $order_id, $sales_point_id, $product_id, $discount_amount, $payable_amount, $payment_type, $price, $selected_id);
                if (!$stmt3->execute()) {
                    throw new Exception("Error to insert on sales_point_order_items table: " . $stmt3->error . $conn->error);
                }
                // reduce products table quantity
                $stmt4 = $conn->prepare("UPDATE products SET product_quantity=product_quantity-1 WHERE id=?");
                if (!$stmt4) {
                    throw new Exception("SQL failed products: " . $conn->error);
                }
                $stmt4->bind_param("i", $product_id);
                if (!$stmt4->execute()) {
                    throw new Exception("Error to reduce quantity: " . $stmt4->error);
                }
                // update products barcode status
                $barcode_status = "sold";
                $stmt5 = $conn->prepare("UPDATE products_barcodes SET status=? WHERE product_id=? AND barcode=?");
                if (!$stmt5) {
                    throw new Exception("Sql failed products_barcodes: " . $conn->error);
                }
                $stmt5->bind_param("sis", $barcode_status, $product_id, $selected_id);
                if (!$stmt5->execute()) {
                    throw new Exception("Failed to update products_barcodes: " . $stmt5->error);
                }
                // update sales_points_products
                $sales_point_barcode_status = "sold";
                $stmt6 = $conn->prepare("UPDATE sales_points_products SET status=? WHERE product_id=? AND assign_id=?");
                if (!$stmt6) {
                    throw new Exception("Exception failed sales_points_products: " . $conn->error);
                }
                $stmt6->bind_param("sis", $sales_point_barcode_status, $product_id, $selected_id);
                if (!$stmt6->execute()) {
                    throw new Exception("Failed to update: " . $stmt6->error);
                }
                // update sales_points_products_summary
                $stmt7 = $conn->prepare("UPDATE sales_points_products_summary SET current_quantity=current_quantity-1 WHERE product_id=? AND sales_point_id=?");
                if (!$stmt7) {
                    throw new Exception("SQL failed sales_points_products_summary: " . $conn->error);
                }
                $stmt7->bind_param("ii", $product_id, $sales_point_id);
                if (!$stmt7->execute()) {
                    throw new Exception("Error to update sales_points_products_summary: " . $stmt7->error);
                }
            }
            $response["success"] = true;
            $response["message"] = "Order placed successfully";
        }
    }


    $stmt->close();
    $stmt2->close();
    $stmt3->close();
    $stmt4->close();
    $stmt5->close();
    $stmt6->close();
    $stmt7->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
