<?php
// db connection
require_once("./db_connect.php");

$response = [
    "success" => false,
    "message" => "",
    "data" => []
];
try {
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Invalid request method. Must be POST request");
    }
    // user data from frontend
    $user_data = json_decode(file_get_contents("php://input"), true);

    // save user to database
    $name = $user_data["name"] ?? "";
    $role = $user_data["role"] ?? "";
    $designation = $user_data["designation"] ?? "";
    $employeeId = $user_data["employeeId"] ?? "";
    $email = $user_data["email"] ?? "";
    $password = password_hash($user_data["password"], PASSWORD_DEFAULT);

    // checking all fields are available or not
    if (empty($name) || empty($role) || empty($designation) || empty($employeeId) || empty($email) || empty($user_data["password"])) {
        throw new Exception("All fields are required");
    }

    $stmt = $conn->prepare("INSERT INTO users(name, designation, employee_id, email, password, role) VALUES (?,?,?,?,?,?)");
    if (!$stmt) {
        throw new Exception("SQL prepare failed " . $conn->error);
    }
    $stmt->bind_param("ssssss", $name, $designation, $employeeId, $email, $password, $role);
    if (!$stmt->execute()) {
        throw new Exception("Database insert failed " . $stmt->error);
    }
    $response["success"] = true;
    $response["message"] = "Successfully created a new user";
    $response["data"] = ["insert_id" => $stmt->insert_id];

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
