<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "auth";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// Set headers for JSON input and output
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get data from JSON POST request
    $input = json_decode(file_get_contents('php://input'), true);

    if ($input === null) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON data."]);
        exit;
    }

    // Extract parcel ticket from input
    $parcel_ticket = isset($input['parcel_ticket']) ? trim($input['parcel_ticket']) : '';

    // Check if parcel ticket is filled
    if (empty($parcel_ticket)) {
        echo json_encode(["status" => "error", "message" => "Parcel ticket number is required."]);
        exit;
    }

    // Prepare and execute the query to check the parcel status
    $query = "SELECT status, payment_status FROM parcels WHERE parcel_ticket = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $parcel_ticket);
        $stmt->execute();
        $stmt->store_result(); // Store the result to check if the parcel exists

        if ($stmt->num_rows == 1) {
            // Fetch the result
            $stmt->bind_result($status, $payment_status);
            $stmt->fetch();

            // Parcel found, return its status and payment status
            echo json_encode([
                'status' => 'available',
                'parcel_status' => $status,
                'payment_status' => $payment_status,
                'message' => 'Parcel found successfully.'
            ]);
        } else {
            // Parcel not found
            echo json_encode([
                'status' => 'not_available',
                'payment_status' => null,
                'message' => 'Parcel ticket number not found.'
            ]);
        }

        $stmt->close();
    } else {
        // Database query error
        echo json_encode(["status" => "error", "message" => "Database query error."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method. Only POST is allowed."]);
}

$conn->close();
?>
