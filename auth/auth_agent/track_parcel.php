<?php
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

// Set headers for JSON input and output
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Get data from JSON POST request
$data = json_decode(file_get_contents('php://input'), true);

// Extract data (tracking number in this case)
$parcel_ticket = $data['tracking_number'] ?? '';

// Validate that the tracking number was provided
if (empty($parcel_ticket)) {
    echo json_encode([
        "status" => "error",
        "message" => "Tracking number is required."
    ]);
    exit();
}

// Prepare the SQL statement to fetch parcel details based on tracking number
$stmt = $conn->prepare("SELECT * FROM parcels WHERE parcel_ticket = ?");
$stmt->bind_param("s", $parcel_ticket);
$stmt->execute();
$result = $stmt->get_result();

// Check if the parcel exists
if ($result->num_rows > 0) {
    $parcel = $result->fetch_assoc();
    echo json_encode([
        "status" => "success",
        "parcel" => $parcel
    ]);
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
