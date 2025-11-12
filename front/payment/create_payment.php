<?php
require_once("../php/config.php");
$config = include('bkash_config.php');

// get latest token
$tokenData = json_decode(file_get_contents('token.json'), true);
$id_token = $tokenData['id_token'] ?? '';

if (!$id_token) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'No valid token']);
    exit;
}

$url = $config['base_url'] . '/tokenized-checkout/payment/create';
$headers = [
    'Content-Type: application/json',
    'Accept: application/json',
    'authorization:' . $id_token,
    'x-app-key:' . $config['app_key']
];
$data = json_decode(file_get_contents("php://input"), true);
// user data
$merchant_invoice = 'INV-' . time();
$amount = $data["amount"];
$user_id = $data["user_id"];
$payment_for = $data["payment_for"];
$interested_id = $data["interested_id"] ?? null;

// save to database

$sql = "INSERT INTO transactions(user_id, amount, payment_for, merchant_invoice, interested_id) VALUES ('$user_id', '$amount', '$payment_for', '$merchant_invoice', '$interested_id')";

$inserted_transactions = $conn->query($sql);

$payload = json_encode([
    'payerReference' => $user_id, //need to change with customer user_id
    'callbackURL' => 'https://ghotok.soft-techtechnology.com/payment/callback.php', //need to change with real domain
    'amount' => $amount, //need to change with real amount
    'currency' => 'BDT',
    'intent' => 'sale',
    'merchantInvoiceNumber' => $merchant_invoice
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

header('Content-Type: application/json');
echo $response;
