<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

/**
 * Process Airtel Money payment through CTechPay API.
 *
 * @param float $amount The amount to be paid.
 * @param string $phone The phone number for the payment.
 * @return array An array containing success or error message.
 */
function processAirtelPayment($amount, $phone) {
    // CTechPay API endpoint
    $url = 'https://api-sandbox.ctechpay.com/student/mobile/';
    
    // Prepare data for the payment request
    $data = [
        'airtel' => 1,
        'token' => '006618202a3cee6603c207e549e99bcb', // Your API token
        'registration' => 'BLIS2520', // Your registration code
        'amount' => $amount,
        'phone' => $phone
    ];

    // Initialize cURL session
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for sandbox environment

    // Execute the request
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check for cURL error
    if ($error) {
        return ["error" => "Request failed: $error"];
    }

    // Check for HTTP response code
    if ($httpCode !== 200) {
        return ["error" => "HTTP request failed with code: $httpCode", "response" => $response];
    }

    // Decode the JSON response
    $result = json_decode($response, true);

    // Check for a successful payment response
    if (isset($result['status']['message']) && $result['status']['message'] === 'Success.' && $result['status']['code'] === '200') {
        return ["success" => true];
    } else {
        return [
            "error" => "Payment failed. Please try again.",
            "response" => $result
        ];
    }
}

// Process incoming POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    if (empty($_POST['amount']) || empty($_POST['phone'])) {
        echo json_encode(["error" => "Amount and phone fields are required."]);
        exit;
    }

    // Call the payment processing function
    $amount = $_POST['amount'];
    $phone = $_POST['phone'];
    $paymentResponse = processAirtelPayment($amount, $phone);
    
    // Return the payment response
    echo json_encode($paymentResponse);
} else {
    echo json_encode(["error" => "Invalid request method."]);
}
?>
