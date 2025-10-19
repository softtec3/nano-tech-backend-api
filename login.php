<?php
session_start();
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
    $credential = json_decode(file_get_contents("php://input"), true);

    // checking with database
    $email = trim($credential["email"]) ?? "";
    $password = trim($credential["password"]) ?? "";

    if (empty($email) || empty($password)) {
        throw new Exception("All fields are required");
    }
    // find user
    $stmt = $conn->prepare("SELECT email, password, role FROM users WHERE email=?");
    if (!$stmt) {
        throw new Exception("SQL prepare failed" . $conn->error);
    }
    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception("Execution error: " . $stmt->error);
    }
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $target_user = $result->fetch_assoc();
        if (password_verify($password, $target_user["password"])) {
            $_SESSION["logged_user_email"] = $target_user["email"];
            $_SESSION["logged_user_role"] = $target_user["role"];

            $response["success"] = true;
            $response["message"] = "password matched";
            $response["data"] = ["logged_user_email" => $target_user["email"], "logged_user_role" => $target_user["role"]];
        } else {
            throw new Exception("Password not matched");
        }
    } else {
        throw new Exception("User not found");
    }
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
