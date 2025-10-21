<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["logged_user_role"]) || $_SESSION["logged_user_role"] !== "admin") {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "Access denied. Admin privileges required."
    ]);
    exit();
}
