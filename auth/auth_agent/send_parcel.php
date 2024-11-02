<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include Composer's autoload
require __DIR__ . '/../vendor/autoload.php';

use Twilio\Rest\Client;

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "auth";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// Set headers for JSON input and output
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Get data from JSON POST request
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$requiredFields = [
    'sender_phone',
    'receiver_phone',
    'receiver_station',
    'quantity',
    'product_description',
    'item_value',
    'weight',
    'amount',
    'delivery_mode',
    'payment_location' // New field to determine where payment is made
];

foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Field '$field' is required."]);
        exit;
    }
}

// Extract parcel data
$sender_phone = $conn->real_escape_string($data['sender_phone']);
$receiver_phone = $conn->real_escape_string($data['receiver_phone']);
$receiver_station = $conn->real_escape_string($data['receiver_station']);
$quantity = (int)$data['quantity'];
$product_description = $conn->real_escape_string($data['product_description']);
$item_value = (float)$data['item_value'];
$weight = (float)$data['weight'];
$amount = (float)$data['amount'];
$delivery_mode = $conn->real_escape_string($data['delivery_mode']);
$payment_location = $conn->real_escape_string($data['payment_location']);

// Generate a unique parcel ticket
$parcel_ticket = uniqid('PT-', true);

// Encrypt parcel data
$encryption_key = 'your_secret_key'; 
$iv_length = openssl_cipher_iv_length('AES-256-CBC');
$iv = openssl_random_pseudo_bytes($iv_length);

$encrypted_data = openssl_encrypt(
    json_encode([
        'sender_phone' => $sender_phone,
        'receiver_phone' => $receiver_phone,
        'receiver_station' => $receiver_station,
        'quantity' => $quantity,
        'product_description' => $product_description,
        'item_value' => $item_value,
        'weight' => $weight,
        'amount' => $amount,
        'delivery_mode' => $delivery_mode
    ]),
    'AES-256-CBC',
    hash('sha256', $encryption_key, true),
    0,
    $iv
);

// Store encrypted data and IV
$encrypted_data = base64_encode($encrypted_data . '::' . base64_encode($iv));

// Set default payment status
$payment_status = 'pending';

// Check if payment is to be made at the sending booth
if ($payment_location === 'sending') {
    // Include Airtel Money payment script
    include 'airtelmoney.php';

    // Call the payment processing function with amount and sender phone
    $paymentResponse = processAirtelPayment($amount, $sender_phone);

    // Debugging: log the payment response
    error_log(print_r($paymentResponse, true));

    if (isset($paymentResponse['success']) && $paymentResponse['success'] === true) {
        $payment_status = 'paid';
    } else {
        echo json_encode([
            "status" => "error", 
            "message" => "Payment failed.", 
            "response" => $paymentResponse
        ]);
        exit;
    }
}

// Insert the parcel data into the database
$insertQuery = "INSERT INTO parcels (parcel_ticket, encrypted_data, sender_phone, receiver_phone, receiver_station, quantity, product_description, item_value, weight, amount, delivery_mode, payment_status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($insertQuery);
$stmt->bind_param("sssssisdidds", $parcel_ticket, $encrypted_data, $sender_phone, $receiver_phone, $receiver_station, $quantity, $product_description, $item_value, $weight, $amount, $delivery_mode, $payment_status);

if ($stmt->execute()) {
    // Send SMS notifications to sender and receiver
    sendSMS($sender_phone, "Your parcel has been sent successfully. Parcel Ticket: $parcel_ticket.");
    sendSMS($receiver_phone, "You have received a parcel. Parcel Ticket: $parcel_ticket.");

    echo json_encode([
        "status" => "success",
        "message" => "Parcel sent successfully.",
        "parcel_ticket" => $parcel_ticket
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to send parcel."]);
}

$stmt->close();
$conn->close();
?>
