<?php
$config = include('bkash_config.php');
// database connection without header info
include_once("db_config.php");
// enable error logging (optional)
ini_set('log_errors', 1); // 1 for development
ini_set('error_log', __DIR__ . '/bkash_errors.log');

$status = $_GET['status'] ?? 'unknown';
$paymentId = $_GET['paymentID'] ?? '';

function redirectHome($delay = 3)
{
    echo "<script>
        setTimeout(() => {
            window.location.href = 'http://localhost:5174/account/myOrders'; //need to change
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
// update status function
$updateStatus = function ($status, $order_id) use ($conn) {
    $stmt = $conn->prepare("UPDATE customer_orders SET payment_status=? WHERE id=?");
    if (!$stmt) {
        echo "SQL failed: " . $conn->error;
        redirectHome();
    }
    $stmt->bind_param("si", $status, $order_id);
    if (!$stmt->execute()) {
        echo "failed to update: " . $stmt->error;
    }
    $stmt->close();
    redirectHome();
};
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
        echo "<p>Order ID: " . htmlspecialchars($payerReference) . "</p>";
        // save transaction details
        $stmt2 = $conn->prepare("INSERT INTO transactions(order_id, txn_id, merchant_invoice, amount) VALUES(?,?,?,?)");
        if (!$stmt2) {
            echo "SQL failed: " . $conn->error;
        }
        $stmt2->bind_param("issi", $payerReference, $trxId, $invoice_number, $amount);
        if (!$stmt2->execute()) {
            echo "Failed to insert: " . $stmt2->error;
        }
        echo "Inserted";
        $stmt2->close();
        // Update database
        $updateStatus("paid", $payerReference);

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
