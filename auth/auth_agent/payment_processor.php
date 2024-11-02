<?php
// payment_processor.php

// Database connection settings (replace with your actual credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "auth";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if payment method is provided
if (isset($_POST['payment_method'])) {
    $paymentMethod = $_POST['payment_method'];
    $response = array();

    switch ($paymentMethod) {
        case 'Cash':
            // Logic for cash payment processing
            $response['status'] = 'success';
            $response['message'] = 'Payment via cash is confirmed.';
            break;

        case 'Airtel Money':
            // Logic for Airtel Money payment processing
            $response['status'] = 'success';
            $response['message'] = 'Payment via Airtel Money is confirmed.';
            break;

        case 'Mpamba':
            // Logic for Mpamba payment processing
            $response['status'] = 'success';
            $response['message'] = 'Payment via Mpamba is confirmed.';
            break;

        case 'Bank':
            // Logic for bank payment processing
            $response['status'] = 'success';
            $response['message'] = 'Payment via bank is confirmed.';
            break;

        default:
            $response['status'] = 'error';
            $response['message'] = 'Invalid payment method selected.';
            break;
    }
    
    // Return JSON response
    echo json_encode($response);

} else {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Payment method not provided.'
    ));
}

$conn->close();
?>
