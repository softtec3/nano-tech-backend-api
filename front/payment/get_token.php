<?php
require_once("../db_connect.php");
$config = include('bkash_config.php');
$tokenFile = 'token.json';

header('Content-Type: application/json');

$response = [
    "success" => false,
    "message" => "",
    "data" => []
];

try {
    // --- helper function ---
    function saveToken($data, $tokenFile)
    {
        file_put_contents($tokenFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    // --- load existing token ---
    if (file_exists($tokenFile)) {
        $tokenData = json_decode(file_get_contents($tokenFile), true);
    } else {
        $tokenData = [];
    }

    $now = time();
    $needNewToken = true;

    // --- check if token still valid ---
    if (!empty($tokenData['id_token']) && $tokenData['expires_at'] > $now) {
        $remaining = $tokenData['expires_at'] - $now;
        if ($remaining > 300) { // more than 5 min left
            $needNewToken = false;
        } else {
            // try refresh before expiry
            $url = $config['base_url'] . '/tokenized-checkout/auth/refresh-token';
            $headers = [
                'Content-Type: application/json',
                'Accept: application/json',
                'authorization:' . $tokenData['id_token'],
                'x-app-key:' . $config['app_key']
            ];
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $refreshResponse = json_decode(curl_exec($ch), true);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                throw new Exception("cURL error while refreshing token: $curlError");
            }

            if (!empty($refreshResponse['id_token'])) {
                $tokenData = [
                    'id_token' => $refreshResponse['id_token'],
                    'expires_at' => $now + 3600,
                ];
                saveToken($tokenData, $tokenFile);
                $needNewToken = false;
            }
        }
    }

    // --- generate new token if needed ---
    if ($needNewToken) {
        $url = $config['base_url'] . '/tokenized-checkout/auth/grant-token';
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'username:' . $config['username'],
            'password:' . $config['password']
        ];
        $body = json_encode([
            'app_key' => $config['app_key'],
            'app_secret' => $config['app_secret']
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = json_decode(curl_exec($ch), true);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new Exception("cURL error while granting token: $curlError");
        }

        if (empty($responseData['id_token'])) {
            throw new Exception("Unable to generate token: " . json_encode($responseData));
        }

        $tokenData = [
            'id_token' => $responseData['id_token'],
            'expires_at' => $now + 3600,
        ];
        saveToken($tokenData, $tokenFile);
    }

    // --- success response ---
    $response["success"] = true;
    $response["message"] = "Token ready";
    $response["data"] = $tokenData;
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
