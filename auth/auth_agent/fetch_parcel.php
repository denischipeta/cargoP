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

// Set headers for JSON output
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// SQL query to fetch parcel data including additional details
$sql = "SELECT 
            id AS parcelID, 
            created_at AS timestamp, 
            sender_name AS sender, 
            sender_phone AS senderPhone, 
            receiver_name AS receiver, 
            receiver_phone AS receiverPhone, 
            delivery_address AS deliveryAddress, 
            payment_method AS paymentMode, 
            price AS amount, 
            delivery_mode AS status 
        FROM parcels";

$result = $conn->query($sql);

$parcels = [];

if ($result->num_rows > 0) {
    // Fetch data into an array
    while ($row = $result->fetch_assoc()) {
        $parcels[] = $row;
    }
    // Return data as JSON
    echo json_encode(["status" => "success", "parcels" => $parcels]);
} else {
    echo json_encode(["status" => "error", "message" => "No parcels found."]);
}

// Close the connection
$conn->close();
?>
