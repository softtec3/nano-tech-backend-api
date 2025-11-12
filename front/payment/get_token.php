<?php
$config = include('bkash_config.php');
$tokenFile = 'token.json';

//  Helper to save token
function saveToken($data, $tokenFile)
{
    file_put_contents($tokenFile, json_encode($data, JSON_PRETTY_PRINT));
}

//  Load existing token if available
if (file_exists($tokenFile)) {
    $tokenData = json_decode(file_get_contents($tokenFile), true);
} else {
    $tokenData = [];
}

$now = time();
$needNewToken = true;

if (!empty($tokenData['id_token']) && $tokenData['expires_at'] > $now) {
    // still valid
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
        curl_close($ch);

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

if ($needNewToken) {
    // Call Grant Token API
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
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (!empty($response['id_token'])) {
        $tokenData = [
            'id_token' => $response['id_token'],
            'expires_at' => $now + 3600,
        ];
        saveToken($tokenData, $tokenFile);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unable to generate token', 'details' => $response]);
        exit;
    }
}

// 🔹 Return valid token
header('Content-Type: application/json');
echo json_encode($tokenData);
