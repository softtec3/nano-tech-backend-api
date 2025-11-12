<?php
$config = include('bkash_config.php');
$data = json_decode(file_get_contents("php://input"), true);

$id_token = $data['id_token'];
$paymentId = $data['paymentId'];

$url = $config['base_url'] . '/tokenized-checkout/payment/execute';
$headers = [
    'Content-Type: application/json',
    'Accept: application/json',
    'authorization:' . $id_token,
    'x-app-key:' . $config['app_key']
];

$payload = json_encode([
    'paymentId' => $paymentId
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
