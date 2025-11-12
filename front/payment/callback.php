<?php
require_once("../php/config.php"); // your PDO connection ($conn)
$config = include('bkash_config.php');

// enable error logging (optional)
ini_set('log_errors', 0); // 1 for development
ini_set('error_log', __DIR__ . '/bkash_errors.log');

$status = $_GET['status'] ?? 'unknown';
$paymentId = $_GET['paymentID'] ?? '';

function redirectHome($delay = 3)
{
    echo "<script>
        setTimeout(() => {
            window.location.href = '../home.php';
        }, " . ($delay * 1000) . ");
    </script>";
    exit;
}

function showMessage($title, $type = 'info')
{
    $icons = [
        'success' => '✅',
        'error' => '❌',
        'warning' => '⚠️',
        'cancel' => '🚫',
        'info' => 'ℹ️'
    ];
    $icon = $icons[$type] ?? 'ℹ️';
    echo "<h2>{$icon} {$title}</h2>";
}

try {
    if ($status === 'success' && $paymentId !== '') {

        // 1️⃣ Load token
        $tokenData = json_decode(file_get_contents('token.json'), true);
        $id_token = $tokenData['id_token'] ?? null;
        if (!$id_token) {
            showMessage("Authorization token missing.", 'error');
            redirectHome();
        }

        // 2️⃣ Execute payment
        $url = $config['base_url'] . '/tokenized-checkout/payment/execute';
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'authorization:' . $id_token,
            'x-app-key:' . $config['app_key']
        ];
        $payload = json_encode(['paymentId' => $paymentId]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $execute_response = json_decode(curl_exec($ch), true);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            error_log("cURL Error: " . $curl_error);
            showMessage("Network issue contacting bKash API.", 'error');
            redirectHome();
        }

        // 3️⃣ Validate response
        if (!isset($execute_response['trxId'])) {
            error_log("Payment execution failed: " . json_encode($execute_response));
            showMessage("Payment execution failed. Please try again.", 'error');
            redirectHome();
        }

        // 4️⃣ Extract details
        $trxId = $execute_response['trxId'];
        $amount = $execute_response['amount'] ?? '0';
        $payerReference = $execute_response['payerReference'] ?? null;
        $invoice_number = $execute_response['merchantInvoiceNumber'] ?? null;

        showMessage("Payment Successful!", 'success');
        echo "<p>Transaction ID: " . htmlspecialchars($trxId) . "</p>";
        echo "<p>Amount: " . htmlspecialchars($amount) . " BDT</p>";
        echo "<p>User ID: " . htmlspecialchars($payerReference) . "</p>";

        // 5️⃣ Fetch transaction record
        $find_transaction = $conn->prepare("SELECT * FROM transactions WHERE merchant_invoice = ?");
        $find_transaction->execute([$invoice_number]);
        $found_transaction = $find_transaction->fetch(PDO::FETCH_ASSOC);

        if (!$found_transaction) {
            error_log("Transaction not found for invoice: $invoice_number");
            showMessage("Transaction not found.", 'error');
            redirectHome();
        }

        // 6️⃣ Update transaction status
        $update_status = $conn->prepare("UPDATE transactions SET status='success', txn_id=? WHERE merchant_invoice=?");
        $update_status->execute([$trxId, $invoice_number]);

        // 7️⃣ Apply business logic
        $payment_for = $found_transaction["payment_for"] ?? '';
        $pay_user_id = $payerReference;

        // 👉 Biodata payment
        if ($payment_for === "biodata") {
            $update_biodata = $conn->prepare("UPDATE biodatas SET status='active' WHERE user_id=?");
            if (!$update_biodata->execute([$pay_user_id])) {
                error_log("Failed to activate biodata for user_id $pay_user_id");
            }
        }

        // 👉 Package payment
        if ($payment_for !== "biodata" && !empty($payment_for)) {
            $connect_amount = 0;
            switch ($payment_for) {
                case 'starter':
                    $connect_amount = 1;
                    break;
                case 'mini':
                    $connect_amount = 3;
                    break;
                case 'basic':
                    $connect_amount = 3;
                    break;
                case 'pro':
                    $connect_amount = 15;
                    break;
                case 'premium':
                    $connect_amount = 1200;
                    break;
            }

            if ($connect_amount > 0) {
                $update_connect = $conn->prepare("UPDATE users SET connects = connects + ? WHERE user_id=?");
                if (!$update_connect->execute([$connect_amount, $pay_user_id])) {
                    error_log("Failed to update connects for user $pay_user_id");
                }
            }
        }

        // 👉 Single biodata interest payment
        if (!empty($found_transaction["interested_id"])) {
            $interested_id = $found_transaction["interested_id"];
            $add_interested = $conn->prepare("INSERT INTO interested (user_id, biodata_id) VALUES (?, ?)");
            if (!$add_interested->execute([$pay_user_id, $interested_id])) {
                error_log("Failed to add interested record for user $pay_user_id, biodata $interested_id");
            }
        }

        redirectHome();
    } elseif ($status === 'failure') {
        showMessage("Payment Failed.", 'error');
        redirectHome();
    } elseif ($status === 'cancel') {
        showMessage("Payment Cancelled.", 'cancel');
        redirectHome();
    } else {
        showMessage("Unknown Payment Status", 'warning');
        redirectHome();
    }
} catch (Exception $e) {
    error_log("Exception in callback.php: " . $e->getMessage());
    showMessage("Unexpected error occurred.", 'error');
    redirectHome();
}
