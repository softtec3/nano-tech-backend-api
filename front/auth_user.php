<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_name"])) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "Access denied. Must be logged in.",
    ]);
    exit();
}
