<?php
require __DIR__ . '/vendor/autoload.php'; // Include Twilio's PHP SDK

use Twilio\Rest\Client;

error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "auth";

// Twilio credentials
$twilioSid = 'ACbb01b9a43c173e96ba7f840d79daf7d7';         // Replace with your Twilio SID
$twilioToken = '0dfadf7785c4dc7d085395615bd982b3';     // Replace with your Twilio Token
$twilioPhone = '+12679301646';     // Replace with your Twilio Phone Number

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Set the Content-Type header to application/json
header('Content-Type: application/json');

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed."
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the raw POST data
    $rawData = file_get_contents('php://input');
    $input = json_decode($rawData, true);

    if ($input === null) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON data."]);
        exit;
    }

    // Extract input values
    $phone = isset($input['phone']) ? trim($input['phone']) : '';
    $status = isset($input['status']) ? trim($input['status']) : '';

    // Check if all required fields are filled in
    if (empty($phone) || empty($status)) {
        echo json_encode(["status" => "error", "message" => "Please fill in all fields."]);
        exit;
    }

    // Check the current status of the agent
    $checkQuery = "SELECT status FROM agents WHERE phone = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $agent = $result->fetch_assoc();

        // If the status is already the same as the input status, return a message
        if ($agent['status'] === $status) {
            echo json_encode([
                "status" => "success",
                "message" => "Agent is already in the '$status' state."
            ]);
            exit;
        }

        // Prepare the SQL query to update the agent's status
        $query = "UPDATE agents SET status = ? WHERE phone = ?";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("ss", $status, $phone);

            if ($stmt->execute()) {
                // Send SMS notification
                $client = new Client($twilioSid, $twilioToken);
                $messageBody = '';

                if ($status === 'active') {
                    $messageBody = "Dear agent, your account has been activated. You can now access your dashboard.";
                } elseif ($status === 'blocked') {
                    $messageBody = "Dear agent, your account has been blocked. Please contact support for further assistance.";
                }

                try {
                    // Send the SMS
                    $client->messages->create(
                        $phone, // Agent's phone number
                        [
                            'from' => $twilioPhone,
                            'body' => $messageBody
                        ]
                    );
                    echo json_encode([
                        "status" => "success",
                        "message" => "Agent status has been changed to '$status', and SMS notification sent."
                    ]);
                } catch (Exception $e) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Agent status changed to '$status', but failed to send SMS: " . $e->getMessage()
                    ]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to update agent status: " . $stmt->error]);
            }

            $stmt->close();
        } else {
            echo json_encode(["status" => "error", "message" => "Database query error."]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "No agent found with the provided phone number."
        ]);
    }
}

$conn->close();
?>
