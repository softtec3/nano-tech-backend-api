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
    $user_name = $credential["user_name"] ?? "";
    $password = $credential["password"] ?? "";
    if (empty($user_name) || empty($password)) {
        throw new Exception("All fields are required");
    }

    $stmt = $conn->prepare("SELECT id, user_name, password, role FROM general_users WHERE user_name=?");
    if (!$stmt) {
        throw new Exception("SQL failed: " . $conn->error);
    }
    $stmt->bind_param("s", $user_name);
    if (!$stmt->execute()) {
        throw new Exception("Execution failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $user = $row;
        }
        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["user_name"];
            $_SESSION["role"] = $user["role"];

            $response["success"] = true;
            $response["message"] = "Login successful";
            $response["data"] = ["user_id" => $user["id"], "user_name" => $user["user_name"], "role" => $user["role"]];
        } else {
            $response["success"] = true;
            $response["message"] = "Wrong password";
        }
    } else {
        $response["success"] = true;
        $response["message"] = "User not found";
    }






    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
