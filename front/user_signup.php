<?php
require_once("./db_connect.php");
$response = [
    "success" => false,
    "message" => "",
    "data" => []
];


try {

    $sql = "CREATE TABLE IF NOT EXISTS general_users(
	id int AUTO_INCREMENT PRIMARY KEY,
    full_name varchar(255) DEFAULT NULL,
    user_name varchar(100) UNIQUE NOT NULL,
    password varchar(255) NOT NULL,
    role ENUM('user','admin','sales-representative') DEFAULT 'user',
    created_at timestamp DEFAULT CURRENT_TIMESTAMP
    )";

    if (!$conn->query($sql)) {
        throw new Exception("Error creating general_users: " . $conn->error);
    }

    $sql2 = "CREATE TABLE IF NOT EXISTS users_information (
	id int AUTO_INCREMENT PRIMARY KEY,
    user_id int NOT NULL,
    user_name varchar(100) UNIQUE NOT NULL,
    full_name varchar(255) DEFAULT NULL,
    image TEXT DEFAULT NULL,
    mobile_number varchar(20) DEFAULT NULL,
    address_label ENUM('home','office','other') DEFAULT 'home',
    area varchar(255) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    landmark TEXT DEFAULT NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES general_users(id) ON UPDATE CASCADE ON DELETE CASCADE
    )";
    if (!$conn->query($sql2)) {
        throw new Exception("Error creating users_information: " . $conn->error);
    }



    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Invalid request method. Must be POST request");
    }

    $data = json_decode(file_get_contents("php://input"), true);

    $full_name = $data["full_name"] ?? "";
    $user_name = $data["user_name"] ?? "";
    $password = $data["password"] ?? "";
    $hash_password = password_hash($password, PASSWORD_DEFAULT);

    if (empty($full_name) || empty($user_name) || empty($password)) {
        throw new Exception("All fields are required");
    }

    $stmt = $conn->prepare("INSERT INTO general_users(full_name, user_name, password) VALUES(?,?,?)");

    if (!$stmt) {
        throw new Exception("SQL failed: " . $conn->error);
    }
    $stmt->bind_param("sss", $full_name, $user_name, $hash_password);
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert " . $stmt->error);
    }
    if ($stmt->insert_id) {
        $id = $stmt->insert_id;
        $stmt2 = $conn->prepare("INSERT INTO users_information(user_id,user_name, full_name) VALUES (?,?,?)");
        if (!$stmt2) {
            throw new Exception("SQL failed users_information: " . $conn->error);
        }
        $stmt2->bind_param("iss", $id, $user_name, $full_name);

        if (!$stmt2->execute()) {
            throw new Exception("Insert failed to users_information: " . $stmt2->error);
        }

        $response["success"] = true;
        $response["message"] = "Signup successful";
        $response["data"] = ["insert_id" => $stmt->insert_id];
    }


    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
