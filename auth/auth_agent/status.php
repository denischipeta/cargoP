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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the raw POST data from Flutter
    $input = json_decode(file_get_contents('php://input'), true);

    if ($input === null) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON data."]);
        exit;
    }

    // Extract booth number and password from input
    $boothNumber = isset($input['booth_number']) ? trim($input['booth_number']) : '';
    $password = isset($input['password']) ? trim($input['password']) : '';

    if (empty($boothNumber) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Please fill in all fields."]);
        exit;
    }

    // Check if agent exists
    $query = "SELECT * FROM agents WHERE booth_number = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $boothNumber);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if agent with the booth number exists
        if ($result->num_rows == 1) {
            $agent = $result->fetch_assoc();

            // Verify the password
            if (password_verify($password, $agent['password'])) {
                // Successful login, return agent status
                echo json_encode([
                    "status" => "success",
                    "agent_status" => $agent['status']  // Send back the agent status
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

    $conn->close();
}
?>
