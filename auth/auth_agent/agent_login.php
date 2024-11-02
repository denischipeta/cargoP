<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "auth";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Set the Content-Type header to application/json
header('Content-Type: application/json');

// Check connection
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the raw POST data from Flutter
    $rawData = file_get_contents('php://input');

    // Check if the request body is empty
    if (empty($rawData)) {
        echo json_encode(["status" => "error", "message" => "No input data provided."]);
        exit;
    }

    // Decode JSON data
    $input = json_decode($rawData, true);

    // Check if JSON decoding was successful
    if ($input === null) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON data."]);
        exit;
    }

    // Extract booth number and password from input
    $boothNumber = isset($input['booth_number']) ? trim($input['booth_number']) : '';
    $password = isset($input['password']) ? trim($input['password']) : '';

    // Check if both fields are filled in
    if (empty($boothNumber) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Please fill in all fields."]);
        exit;
    }

    // Prepare and execute the query to check if the agent exists
    $query = "SELECT * FROM agents WHERE business_reg_number = ?"; // Change to business_reg_number
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $boothNumber); // binding the booth number to business registration number
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if an agent with the booth number exists
        if ($result->num_rows == 1) {
            $agent = $result->fetch_assoc();

            // Verify the password
            if (password_verify($password, $agent['password'])) {
                // Successful login
                echo json_encode([
                    "status" => "success",
                    "message" => "Login successful.",
                    "agent_name" => $agent['agent_name'],
                    "booth_number" => $agent['business_reg_number'], // assuming booth_number should be business_reg_number
                    "agent_status" => $agent['status']
                ]);
            } else {
                // Invalid password
                echo json_encode(["status" => "error", "message" => "Invalid password."]);
            }
        } else {
            // No agent found with the booth number
            echo json_encode(["status" => "error", "message" => "Agent not found."]);
        }

        $stmt->close();
    } else {
        // Database query error
        echo json_encode(["status" => "error", "message" => "Database query error."]);
    }
}

$conn->close();
?>
