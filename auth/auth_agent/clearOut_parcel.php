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

// Get data from JSON POST request
$data = json_decode(file_get_contents('php://input'), true);

// Extract data (parcel ticket in this case)
$parcel_ticket = $data['parcel_ticket'] ?? '';

// Validate that the parcel ticket was provided
if (empty($parcel_ticket)) {
    echo json_encode([
        "status" => "error",
        "message" => "Tracking number is required."
    ]);
    exit();
}

// Prepare the SQL statement to fetch parcel details based on the parcel ticket
$stmt = $conn->prepare("SELECT * FROM parcels WHERE parcel_ticket = ?");
$stmt->bind_param("s", $parcel_ticket);
$stmt->execute();
$result = $stmt->get_result();

// Check if the parcel exists
if ($result->num_rows > 0) {
    $parcel = $result->fetch_assoc();
    
    // Check if the parcel is already cleared out
    if ($parcel['status'] === 'cleared_out') {
        echo json_encode([
            "status" => "error",
            "message" => "Parcel has already been cleared out.",
            "receiver_name" => $parcel['receiver_name'], // Assuming you have this field in your table
            "receiver_phone" => $parcel['receiver_phone'], // Assuming you have this field in your table
            "clearing_out_timestamp" => $parcel['clearing_out_timestamp'] ?? 'Not Available' // Show clearing out timestamp
        ]);
        exit();
    }

    // Update the parcel status to 'cleared_out' and set the clearing out timestamp
    $clearing_out_timestamp = date('Y-m-d H:i:s'); // Get the current timestamp
    $update_stmt = $conn->prepare("UPDATE parcels SET status = 'cleared_out', clearing_out_timestamp = ? WHERE parcel_ticket = ?");
    $update_stmt->bind_param("ss", $clearing_out_timestamp, $parcel_ticket);
    
    if ($update_stmt->execute()) {
        // Add additional details
        echo json_encode([
            "status" => "success",
            "parcel" => [
                "status" => "cleared_out", // Set status to cleared_out
                "payment_status" => $parcel['payment_status'], // Fetch current payment status
                "clearing_out_timestamp" => $clearing_out_timestamp, // Include clearing out timestamp
                "transaction_id" => $parcel['transaction_id'] ?? 'Not Available' // Fetch current transaction ID
            ],
            "message" => "Parcel cleared out successfully."
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to update parcel status."
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No parcel found with this tracking number."
    ]);
}

// Close connections
$stmt->close();
$conn->close();
?>
