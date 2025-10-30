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

            if ($user["role"] === "sales-representative") {
                $stmt2 = $conn->prepare("SELECT id FROM sales_points WHERE user_id=?");
                if (!$stmt2) {
                    throw new Exception("SQL failed sales_points: " . $conn->error);
                }
                $stmt2->bind_param("i", $user["id"]);
                if (!$stmt2->execute()) {
                    throw new Exception("Fetching failed sales_point " . $stmt2->error);
                }
                $result2 = $stmt2->get_result();
                if ($result2 && $result2->num_rows > 0) {
                    while ($row2 = $result2->fetch_assoc()) {
                        $sales_id = $row2["id"];
                    }
                }
                $_SESSION["sales_point_id"] = $sales_id;
                $stmt2->close();
            }

            $response["success"] = true;
            $response["message"] = "Login successful";
            if ($sales_id ?? NULL) {
                $response["data"] = ["user_id" => $user["id"], "user_name" => $user["user_name"], "role" => $user["role"], "sales_point_id" => $sales_id];
            } else {
                $response["data"] = ["user_id" => $user["id"], "user_name" => $user["user_name"], "role" => $user["role"]];
            }
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
