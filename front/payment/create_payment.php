<?php
require_once("../db_connect.php");
$config = include('bkash_config.php');

$response = [
    "success" => false,
    "message" => "",
    "data" => []
];

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Invalid request method. Must be POST request.");
    }

    // --- Decode JSON body ---
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        throw new Exception("Invalid JSON input.");
    }

    $amount   = $data["amount"] ?? null;
    $order_id = $data["order_id"] ?? null;

    if (empty($amount) || empty($order_id)) {
        throw new Exception("amount and order_id are required.");
    }

    // --- Load token ---
    $tokenData = json_decode(file_get_contents('token.json'), true);
    $id_token  = $tokenData['id_token'] ?? '';

    if (empty($id_token)) {
        throw new Exception("No valid token. Please refresh and try again.");
    }

    // --- Prepare API data ---
    $merchant_invoice = 'INV-' . time();
    $url = $config['base_url'] . '/tokenized-checkout/payment/create';
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'authorization:' . $id_token,
        'x-app-key:' . $config['app_key']
    ];

    $payload = json_encode([
        'payerReference' => $order_id,
        'callbackURL' => 'https://api.nano-techbd.com/front/payment/callback.php',
        'amount' => $amount,
        'currency' => 'BDT',
        'intent' => 'sale',
        'merchantInvoiceNumber' => $merchant_invoice
    ]);

    // --- cURL request ---
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        throw new Exception("cURL Error: " . $curl_error);
    }

    $bkash_response = json_decode($result, true);

    if (empty($bkash_response)) {
        throw new Exception("Invalid bKash response or empty body.");
    }

    // --- Return success ---
    $response["success"] = true;
    $response["message"] = "Payment created successfully.";
    $response["data"] = $bkash_response;
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
} finally {
    echo json_encode($response);
}
